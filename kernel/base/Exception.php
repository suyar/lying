<?php
namespace lying\base;

use lying\service\Hook;

/**
 * 注册全局错误/异常
 *
 * @author carolkey <me@suyaqi.cn>
 * @since 2.0
 * @link https://carolkey.github.io/
 * @license MIT
 */
class Exception
{
    /**
     * 注册错误，异常处理函数
     */
    public static function register()
    {
        set_exception_handler([self::class, 'exceptionHandler']);
        set_error_handler([self::class, 'errorHandler']);
        register_shutdown_function([self::class, 'shutdownHandler']);
    }
    
    /**
     * 注册异常处理函数
     * @param \Exception|\Error $exception 未被捕获的异常
     */
    public static function exceptionHandler($exception)
    {
        $msg = [
            'message' => $exception->getMessage(),
            'file' => self::trimPath($exception->getFile()),
            'line' => $exception->getLine()
        ];
        Hook::trigger(Hook::APP_ERROR, [$msg]);
        self::showHandler(
            $msg,
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
        if ($errno === E_DEPRECATED || error_reporting() === 0) {
            return true;
        }
        throw new \ErrorException($errstr, $errno, $errno, $errfile, $errline);
    }
    
    /**
     * 脚本执行结束后调用的错误处理函数
     */
    public static function shutdownHandler()
    {
        if (null !== $err = error_get_last()) {
            $msg = [
                'message' => $err['message'],
                'file' => self::trimPath($err['file']),
                'line' => $err['line']
            ];
            Hook::trigger(Hook::APP_ERROR, [$msg]);
            self::showHandler($msg, [], $err['type']);
        }
    }
    
    /**
     * 去除绝对路径
     * @param mixed $subject 要去除的信息
     * @return mixed 返回替换后的数据
     */
    public static function trimPath($subject)
    {
        return str_replace([ROOT, WEB_ROOT], '', $subject);
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
        http_response_code($code === 404 ? 404 : 500);
        
        $path = DIR_LYING . '/view/';
        $file = file_exists($file = $path . $code . '.php') ? $file : $path . 'trace.php';
        
        ob_start();
        ob_implicit_flush(false);
        require $file;
        ob_end_flush();
        flush();
        exit(0);
    }
}
