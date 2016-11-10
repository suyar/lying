<?php
namespace lying\core;

class Config implements Service
{
    private static $config = [];
    
    public function get($config)
    {
        if (isset(self::$config[$config])) {
            return self::$config[$config];
        } else {
            $configFile = ROOT . '/config/' . $config . '.php';
            self::$config[$config] = require $configFile;
            return self::$config[$config];
        }
    }
}