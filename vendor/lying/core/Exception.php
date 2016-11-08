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
        var_dump('关闭：' . $errno);
    }
    
    public static function shutdownHandler()
    {
        var_dump('关闭：' . error_reporting());
    }
    
    
    
    
}