<?php
namespace lying\core;

class Container
{
    private $instance = [];
    
    
    
    
    public function get($name, $params = [])
    {
        if (isset($this->instance[$name])) {
            return $this->instance[$name];
        }else {
            
        }
        
        
    }
    
    public function register($class, $params = [], $share = false)
    {
        
    }
    
    
    
    
}
