<?php
namespace lying\service;

class Config
{
    private $config = [];
    
    public function load($config)
    {
        if (isset($this->config[$config])) {
            return $this->config[$config];
        }else {
            $this->config[$config] = require ROOT . "/config/$config.php";
            return $this->config[$config];
        }
    }
}