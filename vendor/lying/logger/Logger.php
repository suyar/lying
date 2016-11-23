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
            throw new \Exception('Configuration item [level] must be an array.', 500);
        }
    }
    
    abstract public function log($msg, $level = 'debug');
    
    
}