<?php
/**
 * @author carolkey <me@suyaqi.cn>
 * @link https://github.com/carolkey/lying
 * @copyright 2017 Lying
 * @license MIT
 */

namespace lying\service;

/**
 * Class Exception
 * @package lying\service
 * @since 2.0
 */
class Exception
{
    /**
     * @var boolean 是否允许错误
     */
    private $err = true;

    /**
     * 注册错误/异常处理函数
     */
    public function register()
    {
        set_exception_handler([$this, 'exceptionHandler']);
        set_error_handler([$this, 'errorHandler']);
        register_shutdown_function([$this, 'shutdownHandler']);
    }

    /**
     * 卸载错误/异常处理函数
     */
    public function unregister()
    {
        restore_error_handler();
        restore_exception_handler();
    }
    
    /**
     * 异常处理函数
     * @param \Exception|\Error|\ErrorException $e 未被捕获的异常
     */
    public function exceptionHandler($e)
    {
        $this->err = false;
        $this->unregister();
        $this->renderView([
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getCode()
        ], explode("\n", $e->getTraceAsString()));
    }
    
    /**
     * 错误处理函数
     * @param integer $errno 错误的级别
     * @param string $errstr 错误的信息
     * @param string $errfile 发生错误的文件名
     * @param integer $errline 错误发生的行号
     * @return null|boolean 是否继续执行默认的错误处理
     * @throws \ErrorException 抛出一个错误异常
     */
    public function errorHandler($errno, $errstr, $errfile, $errline)
    {
        if (error_reporting() & $errno) {
            throw new \ErrorException($errstr, $errno, $errno, $errfile, $errline);
        }
        return true;
    }
    
    /**
     * 脚本执行结束后调用的错误处理函数
     */
    public function shutdownHandler()
    {
        if ($this->err && ($e = error_get_last())) {
            $this->renderView([$e['message'], $e['file'], $e['line'], $e['type']]);
        }
    }

    /**
     * 渲染错误页面
     * @param array $info [message, file, line, code]
     * @param array $trace
     */
    private function renderView($info, $trace = [])
    {
        list($msg, $file, $line, $code) = $info;
        while (ob_get_level() !== 0) ob_end_clean();
        http_response_code($code === 404 ? 404 : 500);
        ob_start();
        ob_implicit_flush(false);
        if (php_sapi_name() === 'cli') {
            echo "[root@lying ~]Error Code:$code" . PHP_EOL;
            echo "[root@lying ~]Error Info:$msg" . PHP_EOL;
            echo "[root@lying ~]Error File:$file" . PHP_EOL;
            echo "[root@lying ~]Error Line:$line" . PHP_EOL;
            foreach ($trace as $t) {
                echo "[root@lying ~]$t" . PHP_EOL;
            }
        } else {
            require DIR_KERNEL . DS . 'view' . DS . 'error.php';
        }
        ob_end_flush();
        flush();
        exit(1);
    }
}
