<?php
namespace lying\logger;

use lying\service\Service;

/**
 * 日志服务基类，所有的日志类都应该继承此类
 *
 * @author carolkey <me@suyaqi.cn>
 * @since 2.0
 * @link https://carolkey.github.io/
 * @license MIT
 */
abstract class Logger extends Service
{
    /**
     * @var array 存储日志的容器
     */
    protected $container = [];
    
    /**
     * @var array 日志等级
     */
    protected static $levels = [
        5 => 'debug',
        4 => 'info',
        3 => 'notice',
        2 => 'warning',
        1 => 'error',
    ];
    
    /**
     * @var integer 要开始记录的日志等级
     */
    protected $level = 3;
    
    /**
     * @var integer 最大存储条数,默认500
     */
    protected $maxItem = 500;
    
    /**
     * 结束的时候刷新日志
     */
    protected function init()
    {
        register_shutdown_function([$this, 'flush']);
    }
    
    /**
     * 格式化数据
     * @param mixed $data 要格式化的数据
     * @param integer $level 数组的第几层
     * @return string 返回格式化后的字符串
     */
    protected static function formatData($data, $level = 1)
    {
        if (is_string($data)) {
            return "'$data'";
        } elseif (is_int($data) || is_float($data)) {
            return $data;
        } elseif (is_array($data)) {
            $tmp = '[' . PHP_EOL;
            foreach ($data as $key => $value) {
                $key = self::formatData($key, $level + 1);
                $value = self::formatData($value, $level + 1);
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
            $trace = [
                'time' => date('Y-m-d H:i:s'),
                'ip' => \Lying::$maker->request()->remoteAddr(),
                'level' => self::$levels[$level],
                'request' => \Lying::$maker->request()->requestUri(),
                'file' => $trace['file'],
                'line' => $trace['line'],
                'data' => self::formatData($data),
            ];
            $this->container[] = $this->buildTrace($trace);
            if (count($this->container) >= $this->maxItem) {
                $this->flush();
            }
        }
    }
    
    /**
     * 生成日志信息
     * @param array $data 要记录的数据
     * @return mixed 返回日志信息
     */
    abstract protected function buildTrace($data);
    
    /**
     * 刷新输出日志
     */
    abstract public function flush();
}
