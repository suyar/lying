<?php
namespace lying\logger;

use lying\service\Service;

abstract class Logger extends Service
{
    /**
     * 存储日志的容器
     * @var array
     */
    protected $logContainer = [];
    
    /**
     * 日志等级
     * @var array
     */
    protected static $levels = [
        LOG_DEBUG=>'debug',
        LOG_INFO=>'info',
        LOG_NOTICE=>'notice',
        LOG_WARNING=>'warning',
        LOG_ERR=>'error'
    ];
    
    /**
     * 要记录的日志等级
     * @var int
     */
    protected $level = LOG_NOTICE;
    
    /**
     * 最大存储条数,默认500
     * @var int
     */
    protected $maxItem = 500;
    
    /**
     * 格式化数据
     * @param mixed $data
     * @param number $level
     * @return string
     */
    protected static function formatData($data, $level = 1)
    {
        if (is_string($data)) {
            return "'$data'";
        }elseif (is_int($data) || is_float($data)) {
            return $data;
        }elseif (is_array($data)) {
            $tmp = '[' . PHP_EOL;
            foreach ($data as $key=>$value) {
                $key = self::formatData($key, $level + 1);
                $value = self::formatData($value, $level + 1);
                $tmp .= str_repeat(' ', $level * 4) . "$key => $value," . PHP_EOL;
            }
            $tmp .= str_repeat(' ', ($level - 1) * 4) . ']';
            return $tmp;
        }elseif (is_null($data)) {
            return 'null';
        }elseif (is_bool($data)) {
            return $data ? 'true' : 'false';
        }elseif (is_object($data)) {
            return '(' . get_class($data) . ')';
        }else {
            return '(' . gettype($data) . ')';
        }
    }
    
    /**
     * 写日志
     * @param mixed $msg 日志内容
     * @param string $level 日志分类
     */
    public function log($data, $level = LOG_DEBUG)
    {
        if ($level <= $this->level) {
            $trace = current(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1));
            $trace = [
                'time' => date('Y-m-d H:i:s'),
                'ip' => $_SERVER['REMOTE_ADDR'],
                'level' => self::$levels[$level],
                'request' => $_SERVER['REQUEST_URI'],
                'file' => $trace['file'],
                'line' => $trace['line'],
                'data' => self::formatData($data),
            ];
            $this->logContainer[] = $this->buildTrace($trace);
            if (count($this->logContainer) >= $this->maxItem) {
                $this->flush();
            }
        }
    }
    
    /**
     * 生成日志信息
     * @param array $data
     * @return string|array
     */
    abstract protected function buildTrace($data);
    
    /**
     * 刷新输出日志
     */
    abstract public function flush();
}