<?php
namespace lying\logger;

class FileLog extends Logger
{
    protected $file = "default";
    
    protected $maxSize = 10240;
    
    protected $maxLength = 500;
    
    
    
    public function init()
    {
        $this->file = DIR_RUNTIME . "/log/$this->file.log";
        $path = dirname($this->file);
        if (!is_dir($path)) {
            if (!mkdir($path, 0777, true)) {
                throw new \Exception("创建文件夹 $path 失败，请检查文件权限", 500);
            }
        }
    }
    
    public function log()
    {
        
    }
    
    
    
    public function flush()
    {
        
    }
}