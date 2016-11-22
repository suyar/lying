<?php
namespace lying\service;

class Session
{
    public function start()
    {
        if (!$this->isActive()) {
            session_start();
        }
    }
    
    
    public function set($key, $value)
    {
        
    }
    
    public function get($key, $defaultValue = null)
    {
        
    }
    
    public function remove($key)
    {
        
    }
    
    public function destroy()
    {
        session_unset();
        session_destroy();
    }
    
    public function isActive()
    {
        return session_status() == PHP_SESSION_ACTIVE;
    }
}