<?php
namespace lying\base;

abstract class AR
{
    
    public $isNewRecord = false;
    
    final public function __construct()
    {
        $this->isNewRecord = true;
    }
    
    abstract public static function table();
    
    
}