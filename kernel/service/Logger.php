<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\service;

/**
 * Class Logger
 * @package lying\service
 */
class Logger extends Service
{
    /**
     * @var string 日志存储的路径
     */
    protected $dir;

    /**
     * @var string 日志文件名
     */
    protected $file = 'runtime';

    /**
     * @var int 单个日志文件的最大值(kb)
     */
    protected $maxSize = 10240;

    /**
     * @var int 最大的日志文件个数
     */
    protected $maxFile = 5;

    /**
     * @var int 要开始记录的日志等级
     */
    protected $level = 3;

    /**
     * @var int 最大存储条数,默认500
     */
    protected $maxItem = 500;

    /**
     * @var array 存储日志的容器
     */
    private $_container = [];
    
    /**
     * @var array 日志等级
     */
    private static $_levels = [
        5 => 'debug',
        4 => 'info',
        3 => 'notice',
        2 => 'warning',
        1 => 'error',
    ];

    /**
     * 初始化文件路径
     * @throws \Exception 文件夹创建失败抛出异常
     */
    protected function init()
    {
        $this->maxFile < 1 && ($this->maxFile = 1);

        $this->maxSize < 1 && ($this->maxSize = 10240);

        $this->maxItem < 1 && ($this->maxItem = 500);

        empty($this->dir) && ($this->dir = DIR_RUNTIME . DS . 'log');

        empty($this->file) && ($this->file = 'runtime');

        if (!\Lying::$maker->helper->mkdir($this->dir)) {
            throw new \Exception("Failed to create directory: {$this->dir}");
        }

        $this->file = $this->dir . DS . $this->file . '.log';

        register_shutdown_function(function () {
            $this->flush();
            register_shutdown_function([$this, 'flush'], true);
        });
    }
    
    /**
     * 格式化数据
     * @param mixed $data 要格式化的数据
     * @return string 返回格式化后的字符串
     */
    private function formatData($data)
    {
        if (is_string($data)) {
            return $data;
        } elseif ($data instanceof \Throwable || $data instanceof \Exception) {
            return (string)$data;
        } else {
            return \Lying::$maker->helper->export($data);
        }
    }
    
    /**
     * 打印
     * @param mixed $data 日志内容
     * @param int $level 日志等级,默认5
     */
    public function record($data, $level = 5)
    {
        if ($level <= $this->level) {
            $trace = current(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1));
            $request = \Lying::$maker->request;
            $this->_container[] = implode('', [
                '[' . date('Y-m-d H:i:s') . ']',
                '[' . $request->userIP() . ']',
                '[' . (isset(self::$_levels[$level]) ? self::$_levels[$level] : 'unknow') . ']',
                '[' . $request->uri() . ']',
                '[' . $trace['file'] . ']',
                '[' . $trace['line'] . ']',
                PHP_EOL,
                $this->formatData($data),
                PHP_EOL,
                PHP_EOL,
            ]);
            count($this->_container) >= $this->maxItem && $this->flush();
        }
    }

    /**
     * 刷新输出日志
     * @throws \Exception 文件打开失败抛出异常
     */
    public function flush()
    {
        if ($this->_container) {
            $fp = @fopen($this->file, 'a');
            if ($fp === false) {
                throw new \Exception("Unable to append to log file: {$this->file}");
            }
            @flock($fp, LOCK_EX);
            clearstatcache();
            if (@filesize($this->file) >  $this->maxSize * 1024) {
                $this->cycleFile();
            }
            @fwrite($fp, implode('', $this->_container));
            $this->_container = [];
            @flock($fp, LOCK_UN);
            @fclose($fp);
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
                    @unlink($backfile);
                    continue;
                }
                @copy($backfile, $this->file . '.bak' . ($i + 1));
                if ($i === 0) {
                    if ($fp = @fopen($backfile, 'a')) {
                        @ftruncate($fp, 0);
                        @fclose($fp);
                    }
                }
            }
        }
    }
}
