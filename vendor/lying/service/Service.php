<?php
namespace lying\service;

class Service
{
    private $instance = [];
    
    private $register = [];
    
    public function __construct($params)
    {
        $this->register = $params;
    }
    
    public function get($id)
    {
        if (isset($this->instance[$id])) {
            return $this->instance[$id];
        }elseif (isset($this->register[$id])) {
            if (is_array($this->register[$id]) && isset($this->register[$id]['class'])) {
                $class = array_shift($this->register[$id]);
                $this->instance[$id] = new $class($this->register[$id]);
            }elseif (is_string($this->register[$id])) {
                $this->instance[$id] = new $this->register[$id]();
            }else {
                throw new \Exception("Service configuration error.");
            }
            unset($this->register[$id]);
            return $this->instance[$id];
        }else {
            throw new \Exception("Service $id not found.");
        }
    }
}