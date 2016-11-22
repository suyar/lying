<?php
namespace lying\logger;

class FileLog extends Logger
{
    protected $file;
    
    protected $maxSize = 10240;
    
    protected $maxLength = 500;
    
    
    
    public function init()
    {
        
    }
    
    public function flush()
    {
        
    }
}