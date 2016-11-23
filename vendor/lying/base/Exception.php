<?php
namespace lying\base;

class Exception
{
    public static function register()
    {
        set_exception_handler([self::class, 'exceptionHandler']);
        set_error_handler([self::class, 'errorHandler']);
        register_shutdown_function([self::class, 'shutdownHandler']);
    }
    
    public static function exceptionHandler($exception)
    {
        echo '异常';
        var_dump($exception);exit;
    }
    
    public static function errorHandler($errno, $errstr, $errfile, $errline, $errcontext)
    {
        var_dump('错误：' . $errstr);exit;
    }
    
    public static function shutdownHandler()
    {
        \Lying::instance()->getLogger()->log('关闭');
        /*$err = error_get_last();
        if ($err) {
            while (ob_get_level() !== 0) ob_end_clean();
            echo '关闭出错';
            var_dump($err);
            exit;
        }*/
    }
    
    
}