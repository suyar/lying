<?php
namespace lying\logger;

class FileLog extends Logger
{
    /**
     * 日志存储的路径
     * @var string
     */
    protected $path;
    
    /**
     * 日志文件名
     * @var string
     */
    protected $file = 'default';
    
    /**
     * 单个日志文件的最大值(kb)
     * @var int
     */
    protected $maxSize = 10240;
    
    /**
     * 最大的日志文件个数
     * @var int
     */
    protected $maxFile = 5;
    
    /**
     * 初始化文件路径
     */
    protected function init()
    {
        $this->path = $this->path ? $this->path : DIR_RUNTIME . '/log';
        if (!is_dir($this->path)) {
            if (!mkdir($this->path, 0777, true) && DEV) {
                throw new \Exception("Failed to create directory $this->path, please check the runtime directory permissions.", 500);
            }
        }
        $this->file = $this->path . '/' . $this->file . '.log';
        register_shutdown_function([$this, 'flush']);
    }
    
    /**
     * 生成日志信息
     * @param array $trace
     * @return string
     */
    protected function buildTrace($trace)
    {
        return implode('', [
            "[{$trace['time']}][{$trace['ip']}][{$trace['level']}][{$trace['request']}][{$trace['file']}][{$trace['line']}]",
            PHP_EOL,
            $trace['data'],
            PHP_EOL,
            PHP_EOL,
        ]);
    }
    
    /**
     * 刷新输出日志
     */
    public function flush()
    {
        if ($this->logContainer) {
            if (is_file($this->file) && filesize($this->file) >= $this->maxSize * 1024) {
                $this->cycleFile();
            }
            clearstatcache();
            file_put_contents($this->file, $this->logContainer, FILE_APPEND|LOCK_EX);
            $this->logContainer = [];
        }
    }
    
    /**
     * 删除备份日志文件
     */
    protected function cycleFile()
    {
        for ($i = $this->maxFile - 1; $i >= 0; $i--) {
            $backfile = $this->file . ($i === 0 ? '' : ('.bak' . $i));
            if (is_file($backfile)) {
                if ($i === $this->maxFile - 1) {
                    unlink($backfile);
                    continue;
                }
                rename($backfile, $this->file . '.bak' . ($i + 1));
            }
        }
    }
}