<?php
namespace lying\logger;

class FileLog extends Logger
{
    /**
     * 日志文件名/日志路径
     * @var string
     */
    protected $file = 'default';
    
    /**
     * 单个日志文件的最大值(kb)
     * @var integer
     */
    protected $maxSize = 10240;
    
    /**
     * 最大的日志文件个数
     * @var integer
     */
    protected $maxFile = 5;
    
    /**
     * 初始化文件路径
     * {@inheritDoc}
     * @see \lying\logger\Logger::init()
     */
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
            register_shutdown_function([$this, 'flush']);
        }catch (\Exception $e) {
            if (DEV) throw $e;
        }
    }
    
    /**
     * {@inheritDoc}
     * @see \lying\logger\Logger::buildMsg()
     */
    protected function buildMsg($time, $ip, $level, $url, $file, $line, $content)
    {
        return "[$time][$ip][$level][$url] In file $file line $line" . PHP_EOL . $content . PHP_EOL;
    }
    
    /**
     * {@inheritDoc}
     * @see \lying\logger\Logger::flush()
     */
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
    
    /**
     * 删除/备份日志文件
     */
    protected function replaceFile()
    {
        $file = $this->file;
        for ($i = $this->maxFile; $i >= 0; $i--) {
            $backfile = $file . ($i === 0 ? '' : ('.bak' . $i));
            var_dump($backfile);
            if (is_file($backfile)) {
                if ($i === $this->maxFile) {
                    unlink($backfile);
                    continue;
                }
                rename($backfile, $file . '.bak' . ($i + 1));
            }
        }
    }
}