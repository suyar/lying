<?php
/**
 * @author carolkey <me@suyaqi.cn>
 * @link https://github.com/carolkey/lying
 * @copyright 2017 Lying
 * @license MIT
 */

namespace lying\service;

/**
 * Class Logger
 * @package lying\service
 * @since 2.0
 */
class Logger extends Service
{
    /**
     * @var string 日志存储的路径
     */
    protected $path;

    /**
     * @var string 日志文件名
     */
    protected $file = 'lying';

    /**
     * @var integer 单个日志文件的最大值(kb)
     */
    protected $maxSize = 10240;

    /**
     * @var integer 最大的日志文件个数
     */
    protected $maxFile = 5;

    /**
     * @var integer 要开始记录的日志等级
     */
    protected $level = 3;

    /**
     * @var integer 最大存储条数,默认500
     */
    protected $maxItem = 500;

    /**
     * @var array 存储日志的容器
     */
    private $container = [];
    
    /**
     * @var array 日志等级
     */
    private static $levels = [
        5 => 'debug',
        4 => 'info',
        3 => 'notice',
        2 => 'warning',
        1 => 'error',
    ];

    /**
     * 初始化文件路径
     */
    protected function init()
    {
        empty($this->path) && ($this->path = DIR_RUNTIME . '/log');
        !is_dir($this->path) && mkdir($this->path, 0777, true);
        $this->file = $this->path . '/' . $this->file . '.log';
        register_shutdown_function([$this, 'flush']);
    }
    
    /**
     * 格式化数据
     * @param mixed $data 要格式化的数据
     * @param integer $level 数组的第几层
     * @return string 返回格式化后的字符串
     */
    private function formatData($data, $level = 1)
    {
        if (is_string($data) || is_int($data) || is_float($data)) {
            return $data;
        } elseif (is_array($data)) {
            $tmp = '[' . PHP_EOL;
            foreach ($data as $key => $value) {
                $key = $this->formatData($key, $level + 1);
                $value = $this->formatData($value, $level + 1);
                $tmp .= str_repeat(' ', $level * 4) . "$key => $value," . PHP_EOL;
            }
            $tmp .= str_repeat(' ', ($level - 1) * 4) . ']';
            return $tmp;
        } elseif (is_null($data)) {
            return 'null';
        } elseif (is_bool($data)) {
            return $data ? 'true' : 'false';
        } elseif (is_object($data)) {
            return '(' . get_class($data) . ')';
        } else {
            return '(' . gettype($data) . ')';
        }
    }
    
    /**
     * 打印LOG
     * @param mixed $data 日志内容
     * @param integer $level 日志等级，默认5
     */
    public function log($data, $level = 5)
    {
        if ($level <= $this->level) {
            $trace = current(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1));
            $this->container[] = implode('', [
                '[' . date('Y-m-d H:i:s') . ']',
                '[' . $_SERVER['REMOTE_ADDR'] . ']',
                '[' . self::$levels[$level] . ']',
                '[' . $_SERVER['REQUEST_URI'] . ']',
                '[' . $trace['file'] . ']',
                '[' . $trace['line'] . ']',
                PHP_EOL,
                $this->formatData($data),
                PHP_EOL,
                PHP_EOL,
            ]);
            if (count($this->container) >= $this->maxItem) {
                $this->flush();
            }
        }
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
    private function cycleFile()
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
