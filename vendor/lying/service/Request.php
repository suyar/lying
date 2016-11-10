<?php
namespace lying\core;

class Request implements Service
{
    public $name;
    
    public function get($name = null)
    {
        var_dump($name);
    }
}