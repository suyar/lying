<?php
namespace lying\logger;

/**
 * 文件日志类
 *
 * @author carolkey <me@suyaqi.cn>
 * @since 2.0
 * @link https://carolkey.github.io/
 * @license MIT
 */
class FileLog extends Logger
{
    /**
     * @var string 日志存储的路径，默认runtime/log
     */
    protected $path;
    
    /**
     * @var string 日志文件名，默认default
     */
    protected $file = 'default';
    
    /**
     * @var integer 单个日志文件的最大值(kb)
     */
    protected $maxSize = 10240;
    
    /**
     * @var integer 最大的日志文件个数
     */
    protected $maxFile = 5;
    
    /**
     * 初始化文件路径
     */
    protected function init()
    {
        $this->path = $this->path ? $this->path : DIR_RUNTIME . '/log';
        !is_dir($this->path) && mkdir($this->path, 0777, true);
        $this->file = $this->path . '/' . $this->file . '.log';
        parent::init();
    }
    
    /**
     * 生成日志信息
     * @param array $trace 编译日志格式
     * @return string 返回字符串形式的数据
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
        if ($this->container) {
            if (is_file($this->file) && filesize($this->file) >= $this->maxSize * 1024) {
                $this->cycleFile();
            }
            clearstatcache();
            file_put_contents($this->file, $this->container, FILE_APPEND|LOCK_EX);
            $this->container = [];
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
