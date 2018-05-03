<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    var_dump($errno);
    if (error_reporting() & $errno) {
        $exception = new \ErrorException($errstr, $errno, $errno, $errfile, $errline);
        //__toString方法中不能抛出异常,所以需要特殊处理
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        array_shift($trace);
        foreach ($trace as $frame) {
            if ($frame['function'] === '__toString') {
                var_dump($frame);
                //die;
                //$this->exceptionHandler($exception);
            }
        }
        //throw $exception;
    }
    return false;
});


class A
{
    private $name;

    public function __construct($name = null)
    {
        $this->name = $name;
    }

    public function getString()
    {
        if ($this->name) {
            return $this->name;
        } else {
            trigger_error('no name');
            return '';
        }
    }

    public function __toString()
    {
        return $this->getString();
    }
}
echo $b;