<?php
namespace lying\logger;

use lying\service\Service;

abstract class Logger extends Service
{
    /**
     * 存储日志的盒子
     * @var array
     */
    protected $box = [];
    
    /**
     * 最大存储条数,默认500
     * @var integer
     */
    protected $maxLength = 500;
    
    /**
     * 日志分类,可以在配置文件自定义
     * @var array
     */
    protected $level = ['debug', 'info', 'warning', 'error'];
    
    /**
     * 初始化日志分类
     * @throws \Exception
     */
    protected function init()
    {
        if (!is_array($this->level)) {
            throw new \Exception('Configuration item [level] must be an array.', 500);
        }
    }
    
    /**
     * 写日志
     * @param mixed $msg 日志内容
     * @param string $level 日志分类
     */
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
                $request = $this->make()->getRequest();
                $file = $trace[0]['file'];
                $line = $trace[0]['line'];
                $time = date('Y-m-d H:i:s');
                $ip = $request->remoteIp();
                $url = $request->uri();
                $msg = $this->buildMsg($time, $ip, $level, $url, $file, $line, $msg);
                $this->box[] = $msg;
                if (count($this->box) >= $this->maxLength) {
                    $this->flush();
                }
            }
        }catch (\Exception $e) {
            if (DEV) throw $e;
        }
    }
    
    /**
     * 生成日志内容
     * @param int $time 日志生成时间
     * @param string $ip 远程访问ip地址
     * @param string $level 日志类型
     * @param string $url 远程访问url
     * @param string $file 打印日志的文件
     * @param string $line 打印日志文件的行数
     * @param string $content 日志内容
     * @return mixed
     */
    abstract protected function buildMsg($time, $ip, $level, $url, $file, $line, $content);
    
    /**
     * 刷新输出日志
     */
    abstract public function flush();
    
}