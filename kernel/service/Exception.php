<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\service;

use lying\event\ExceptionEvent;
use lying\exception\HttpException;

/**
 * Class Exception
 * @package lying\service
 */
class Exception
{
    /**
     * @var bool 是否调试模式
     */
    private $_debug;

    /**
     * 注册错误&异常处理函数
     */
    public function register()
    {
        if ($this->_debug = \Lying::config('debug', false)) {
            error_reporting(-1);
            ini_set('display_errors', 'On');
        }

        //默认的错误渲染
        \Lying::$maker->hook->on(\Lying::EVENT_FRAMEWORK_ERROR, [$this, 'renderException']);

        set_exception_handler([$this, 'exceptionHandler']);
        set_error_handler([$this, 'errorHandler']);
        register_shutdown_function([$this, 'shutdownHandler']);
    }

    /**
     * 卸载错误&异常处理函数
     */
    public function unregister()
    {
        restore_error_handler();
        restore_exception_handler();
    }

    /**
     * 清空输出缓冲区
     */
    private function clearOutput()
    {
        while (ob_get_level() !== 0) {
            @ob_end_clean() || ob_clean();
        }
    }

    /**
     * 判断该错误是否为致命错误
     * @param int $type error_get_last()返回的错误类型
     * @return bool
     */
    private function isFatalError($type)
    {
        return in_array($type, [E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING]);
    }
    
    /**
     * 异常处理函数
     * @param \Exception|\Error|\ErrorException $exception 未被捕获的异常
     */
    public function exceptionHandler($exception)
    {
        $this->unregister();
        try {
            $this->clearOutput();
            $event = new ExceptionEvent();
            $event->e = $exception;
            \Lying::$maker->hook->trigger(\Lying::EVENT_FRAMEWORK_ERROR, $event);
        } catch (\Exception $e) {
            $this->handleFallbackExceptionMessage($e, $exception);
        } catch (\Throwable $e) {
            $this->handleFallbackExceptionMessage($e, $exception);
        }
        exit(1);
    }

    /**
     * 处理在异常处理中抛出的异常
     * @param \Exception|\Throwable $exception 抛出的异常
     * @param \Exception $previousException 抛出异常的异常
     */
    private function handleFallbackExceptionMessage($exception, $previousException) {
        http_response_code(500);
        if ($this->_debug) {
            $msg = "An Error occurred while handling another error:\n";
            $msg .= (string)$exception . "\n";
            $msg .= "Previous exception:\n";
            $msg .= (string)$previousException . "\n";
            echo PHP_SAPI === 'cli' ? $msg : '<pre>' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . '</pre>';
        } else {
            echo 'An internal server error occurred.';
        }
    }
    
    /**
     * 错误处理函数
     * @param int $errno 错误的级别
     * @param string $errstr 错误的信息
     * @param string $errfile 发生错误的文件名
     * @param int $errline 错误发生的行号
     * @return bool 是否继续执行默认的错误处理
     * @throws \ErrorException 抛出一个错误异常
     */
    public function errorHandler($errno, $errstr, $errfile, $errline)
    {
        if (error_reporting() & $errno) {
            $exception = new \ErrorException($errstr, $errno, $errno, $errfile, $errline);
            //__toString方法中不能抛出异常,所以需要特殊处理
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            array_shift($trace);
            foreach ($trace as $frame) {
                if ($frame['function'] === '__toString') {
                    $this->exceptionHandler($exception);
                }
            }
            throw $exception;
        }
        return false;
    }
    
    /**
     * 脚本执行结束后调用的错误处理函数,用于处理致命错误
     */
    public function shutdownHandler()
    {
        if (($error = error_get_last()) && $this->isFatalError($error['type'])) {
            $exception = new \ErrorException($error['message'], $error['type'], $error['type'], $error['file'], $error['line']);
            $this->exceptionHandler($exception);
        }
    }

    /**
     * 默认的异常处理
     * @param ExceptionEvent $event 异常事件
     * @throws \Throwable 模板渲染出错抛出异常
     */
    public function renderException(ExceptionEvent $event)
    {
        $exception = $event->e;

        if ($exception instanceof HttpException) {
            http_response_code($exception->getCode());
        } else {
            http_response_code(500);
        }

        $view = \Lying::$maker->view;
        $view->clear();
        $view->assign('code', $exception->getCode())
            ->assign('info', $exception->getMessage())
            ->assign('line', $exception->getLine())
            ->assign('file', $exception->getFile())
            ->assign('trace', explode("\n", $exception->getTraceAsString()));
        $content = $view->renderFile(DIR_KERNEL . DS . 'view' . DS . 'exception.php');
        $view->clear();
        echo $content;
    }
}
