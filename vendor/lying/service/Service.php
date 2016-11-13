<?php
namespace lying\service;

class Service
{
    private $instance = [];
    
    private $register = [];
    
    public function __construct()
    {
        $this->register = [
            'request'=>'lying\service\Request',
            
        ];
        
        
    }
    
    public function get($id)
    {
        if (isset($this->instance[$id])) {
            return $this->instance[$id];
        }elseif (isset($this->register[$id])) {
            $this->instance[$id] = new $this->register[$id];
            return $this->instance[$id];
        }else {
            throw new \Exception("Service $id not found.");
        }
    }
    
}