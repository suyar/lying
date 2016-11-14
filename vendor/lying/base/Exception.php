<?php
namespace lying\base;

class Exception
{
    public static function exceptionHandler($exception)
    {
        var_dump($exception);exit;
    }
    
    public static function errorHandler($errno, $errstr, $errfile, $errline, $errcontext)
    {
        var_dump('错误：' . $errstr);exit;
    }
    
    
}