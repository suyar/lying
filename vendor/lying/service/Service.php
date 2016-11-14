<?php
namespace lying\service;

class Service
{
    final public function __construct($params = [])
    {
        if ($params) {
            foreach ($params as $key=>$param) {
                $this->$key = $param;
            }
        }
    }
    
    final public function get($id)
    {
        return \Lying::$container->get($id);
    }
}