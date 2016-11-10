<?php
namespace lying\core;

class Exception
{
    public static function exceptionHandler($exception)
    {
        var_dump($exception);
    }
    
    public static function errorHandler($errno, $errstr, $errfile, $errline, $errcontext)
    {
        var_dump('错误：' . $errstr);
    }
    
    
}