<?php
namespace core;
class Logger {
    
    private static $logger;
    
    /**
     * 单个日志文件大小限制,默认1M
     * @var int
     */
    private $maxSize;
    
    /**
     * 收集多少条log后输出到文件;
     * log并不是直接输出到文件,而是先存在数组里面,等到条数超过$maxLenth后才会写入到文件,并且重新收集;
     * 这个值不建议设置太大,否则会占用太多内存,如果日志的数据太大,请把条数设置少一点,默认为20条
     * @var int
     */
    private $maxLenth;
    
    /**
     * 用来存log信息的数组
     * @var array
     */
    private $logArr = [];
    
    private function __construct($config) {
        $this->maxSize = isset($config['maxSize']) ? $config['maxSize'] : 1*1024*1024;
        $this->maxLenth = isset($config['maxLenth']) ? $config['maxLenth'] : 20;
    }
    
    /**
     * 变量被销毁的时候输出$logArr里的所有信息
     */
    public function __destruct() {
        foreach ($this->logArr as $fileName=>$data) {
            $file = $this->getLogFile($fileName);
            file_put_contents($file, $data, FILE_APPEND|LOCK_EX);
            unset($this->logArr[$fileName]);
        }
    }
    
    private function __clone() {}
    
    public static function getInstance() {
        if (!self::$logger instanceof self) {
            self::$logger = new self(isset(\App::$config['log']) ? \App::$config['log'] : []);
        }
        return self::$logger;
    }
    
    /**
     * 写日志
     * @param mixed $var 要输出的变量
     * @param string $fileName 日志文件名
     * @param boolean $flush 是否立即输出所有log(会降低性能;如果log是大数据的话,可以选用此项来减少内存的使用)
     */
    public function log($var, $fileName = 'default', $flush = false) {
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
        $this->logArr[$fileName][] = $content;
        if (count($this->logArr[$fileName], COUNT_RECURSIVE) >= $this->maxLenth || $flush) {
            $this->__destruct();
        }
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