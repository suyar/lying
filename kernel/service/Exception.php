<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\service;

use lying\event\ExceptionEvent;

/**
 * Class Exception
 * @package lying\service
 */
class Exception
{
    /**
     * @var array HTTP返回码
     */
    private static $_httpCode = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        118 => 'Connection timed out',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        210 => 'Content Different',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        310 => 'Too many Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested range unsatisfiable',
        417 => 'Expectation failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable entity',
        423 => 'Locked',
        424 => 'Method failure',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        449 => 'Retry With',
        450 => 'Blocked by Windows Parental Controls',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway or Proxy Error',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        507 => 'Insufficient storage',
        508 => 'Loop Detected',
        509 => 'Bandwidth Limit Exceeded',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];

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
     */
    public function renderException(ExceptionEvent $event)
    {
        $exception = $event->e;
        var_dump($exception);
        /*$errcode = $exception->getCode();
        if ($exception instanceof HttpException && isset(self::$_httpCode[$errcode])) {
            $message = self::$_httpCode[$errcode];
            http_response_code(intval($errcode));
        } else {
            $message = 'An internal server error occurred.';
            http_response_code(500);
        }

        if (PHP_SAPI === 'cli') {
            $err = "[Error Code] :" . $exception->getCode() . "\n";
            $err .= "[Error Info] :" . $exception->getMessage() . "\n";
            $err .= "[Error File] :" . $exception->getFile() . "\n";
            $err .= "[Error Line] :" . $exception->getLine() . "\n";
            foreach (explode("\n", $exception->getTraceAsString()) as $t) {
                $err .= "$t\n";
            }
            fwrite(STDERR, $err);
        } else {
            try {
                list($m, $c, $a) = \Lying::$maker->request()->resolve();
                $errorAction = ['error', 'index', 'index'];
                $res = \Lying::$maker->dispatch()->run($errorAction, ['exception'=>$exception, 'message'=>$message]);
                var_dump($res);die;
            } catch (InvalidRouteException $e) {
                ob_start();
                ob_implicit_flush(false);
                extract(['exception'=>$exception, 'message'=>$message], EXTR_OVERWRITE);
                require DIR_KERNEL . DS . 'view' . DS . ($this->_debug ? 'exception.php' : 'error.php');
                echo ob_get_clean();
            }
        }*/
    }
}
