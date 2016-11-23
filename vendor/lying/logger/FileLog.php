<?php
namespace lying\logger;

class FileLog extends Logger
{
    protected $file = 'default';
    
    protected $maxSize = 10240;
    
    protected $maxFile = 5;
    
    protected function init()
    {
        try {
            parent::init();
            $path = DIR_RUNTIME . '/log';
            if (!is_dir($path)) {
                if (!mkdir($path, 0777, true)) {
                    throw new \Exception("Failed to create directory $path, please check the runtime directory permissions.", 500);
                }
            }
            $this->file = $path . "/$this->file.log";
            //register_shutdown_function([$this, 'flush']);
        }catch (\Exception $e) {
            if (DEV) throw $e;
        }
    }
    
    public function __destruct()
    {
        $this->flush();
    }
    
    public function log($msg, $level = 'debug')
    {
        try {
            if (in_array($level, $this->level)) {
                if (!is_string($msg)) {
                    ob_start();
                    ob_implicit_flush(false);
                    var_dump($msg);
                    $msg = ob_get_clean();
                }
                $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
                $file = $trace[0]['file'];
                $line = $trace[0]['line'];
                $time = date('Y-m-d H:i:s');
                $ip = $_SERVER['REMOTE_ADDR'];
                $msg = "[$time][$ip][$level] In file $file line $line" . PHP_EOL . $msg . PHP_EOL;
                $this->box[] = $msg;
                if (count($this->box) >= $this->maxLength) {
                    $this->flush();
                }
            }
        }catch (\Exception $e) {
            if (DEV) throw $e;
        }
    }
    
    
    public function flush()
    {
        if ($this->box) {
            if (is_file($this->file) && filesize($this->file) >= $this->maxSize * 1024) {
                $this->replaceFile();
                clearstatcache();
            }
            file_put_contents($this->file, $this->box, FILE_APPEND|LOCK_EX);
            $this->box = [];
        }
    }
    
    protected function replaceFile()
    {
        $file = $this->file;
        for ($i = $this->maxFile; $i >= 0; $i--) {
            $backfile = $file . ($i === 0 ? '' : ('.bak' . $i));
            var_dump($backfile);
            if (is_file($backfile)) {
                if ($i == $this->maxFile) {
                    unlink($backfile);
                    continue;
                }
                rename($backfile, $file . '.bak' . ($i + 1));
            }
        }
    }
    
    
}