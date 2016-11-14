<?php
namespace lying\base;

class Exception
{
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
        $err = error_get_last();
        if ($err) {
            while (ob_get_level() !== 0) ob_end_clean();
            echo '关闭出错';
            var_dump($err);
            exit;
        }
    }
    
    
}