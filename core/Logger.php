<?php
namespace core;
class Logger {
    
    private static $logger;
    
    private $maxSize;
    
    private function __construct() {
        $this->maxSize = isset(\App::$config['log']['maxSize']) ? \App::$config['log']['maxSize'] : 1*1024*1024;
    }
    
    private function __clone() {}
    
    public static function getInstance() {
        if (!self::$logger instanceof self) {
            self::$logger = new self;
        }
        return self::$logger;
    }
    
    /**
     * 写日志
     * @param mixed $var 要输出的变量
     * @param string $fileName 日志文件名
     * @return number
     */
    public function log($var, $fileName = 'default') {
        $fileName = $this->getLogFile($fileName);
        if (!is_string($var)) {
            ob_start();
            var_export($var);
            $var = ob_get_clean();
        }
        $ip = Request::getInstance()->remoteIp();
        $time = date('Y-m-d H:i:s');
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $target = "in {$trace[0]['file']} on line {$trace[0]['line']}\r\n";
        $content = "[$time][$ip]$target{$var}\r\n";
        return file_put_contents($fileName, $content, FILE_APPEND|LOCK_EX);
    }
    
    /**
     * 获取文件名
     * @param string $fileName
     * @throws \Exception
     * @return string
     */
    private function getLogFile($fileName) {
        $dir = ROOT.'/runtime/log';
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        $file = $dir.'/'.preg_replace('/\W/', '', $fileName).'.log';
        if (is_file($file) && filesize($file) >= $this->maxSize) {
            if (!rename($file, $dir.'/'.$fileName.date('Y-m-d-H-i-s').'.log')) throw new \Exception('Faile to rename file');
        }
        clearstatcache();
        return $file;
    }
    
}