<?php
namespace lying\base;

class Exception
{
    /**
     * 注册错误,异常处理函数
     */
    public static function register()
    {
        set_exception_handler([self::class, 'exceptionHandler']);
        set_error_handler([self::class, 'errorHandler']);
        register_shutdown_function([self::class, 'shutdownHandler']);
    }
    
    /**
     * 注册异常处理函数
     * @param \Exception|\Error $exception 未捕获的异常
     */
    public static function exceptionHandler($exception)
    {
        self::showHandler(
            [
                'message' => $exception->getMessage(),
                'file' => self::trimPath($exception->getFile()),
                'line' => $exception->getLine()
            ],
            self::trimPath(explode("\n", $exception->getTraceAsString())),
            $exception->getCode()
        );
    }
    
    /**
     * 注册错误处理函数
     * @param integer $errno 错误的级别
     * @param string $errstr 错误的信息
     * @param string $errfile 发生错误的文件名
     * @param integer $errline 错误发生的行号
     * @throws \ErrorException 抛出一个错误异常
     */
    public static function errorHandler($errno, $errstr, $errfile, $errline)
    {
        throw new \ErrorException($errstr, 500, $errno, $errfile, $errline);
    }
    
    /**
     * 脚本执行结束后调用的错误处理函数
     */
    public static function shutdownHandler()
    {
        if (null !== $err = error_get_last()) {
            self::showHandler([
                'message' => $err['message'],
                'file' => self::trimPath($err['file']),
                'line' => $err['line']
            ], [], 500);
        }
    }
    
    /**
     * 去除绝对路径
     * @param mixed $subject 要去除的信息
     * @return mixed
     */
    public static function trimPath($subject)
    {
        return str_replace(ROOT, '', $subject);
    }
    
    /**
     * 显示错误页面
     * @param array $msg 错误信息[message, file, line]
     * @param array $trace 代码回溯
     * @param integer $code 错误代码
     */
    public static function showHandler($msg, $trace, $code)
    {
        while (ob_get_level() !== 0) ob_end_clean();
        http_response_code($code);
        
        $path = DIR_LYING . '/view/exception/';
        $file = file_exists($file = $path . $code . '.php') ? $file : $path . 'trace.php';
        
        ob_start();
        ob_implicit_flush(false);
        require $file;
        ob_end_flush();
        flush();
        exit(0);
    }
}
