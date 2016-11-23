<?php
namespace lying\logger;

use lying\service\Service;

abstract class Logger extends Service
{
    
    protected $box = [];
    
    protected $maxLength = 500;
    
    protected $level = ['debug', 'info', 'warning', 'error'];
    
    protected function init()
    {
        if (!is_array($this->level)) {
            throw new \Exception("日志配置项[level]必须为数组", 500);
        }
    }
    
    abstract public function log($msg, $level = 'debug');
    
    
}