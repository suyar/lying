<?php
namespace lying\logger;


class DbLog extends Logger
{
    /**
     * 数据库连接
     * @var \lying\db\Connection
     */
    protected $connection;
    
    /**
     * 日志表名
     * @var string
     */
    protected $table;
    
    /**
     * {@inheritDoc}
     * @see \lying\logger\Logger::init()
     */
    protected function init()
    {
        parent::init();
    }
    
    /**
     * {@inheritDoc}
     * @see \lying\logger\Logger::buildMsg()
     */
    protected function buildMsg($time, $ip, $level, $url, $file, $line, $content)
    {
        return [
            'time'=>$time,
            'ip'=>$ip,
            'level'=>$level,
            'url'=>$url,
            'file'=>$file,
            'line'=>$line,
            'content'=>$content
        ];
    }
    
    /**
     * {@inheritDoc}
     * @see \lying\logger\Logger::flush()
     */
    public function flush()
    {
        
    }
}