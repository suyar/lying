<?php
namespace lying\service;

class Request
{
    public function uri()
    {
        return $_SERVER['REQUEST_URI'];
    }
    
    public function host()
    {
        return $_SERVER['HTTP_HOST'];
    }
    
    public function scheme()
    {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    }
    
    //public function 
}