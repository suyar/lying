<?php
namespace lying\logger;

/**
 * 数据库日志类
 *
 * @author carolkey <me@suyaqi.cn>
 * @since 2.0
 * @link https://carolkey.github.io/
 * @license MIT
 */
class DbLog extends Logger
{
    /**
     * @var \lying\db\Connection 数据库连接实例
     */
    protected $connection = 'db';
    
    /**
     * @var string 日志表名
     */
    protected $table = 'log';
    
    /**
     * @var \lying\db\Query 查询构造器
     */
    private $qb;
    
    /**
     * 初始化数据库链接
     */
    protected function init()
    {
        $this->connection = \Lying::$maker->db($this->connection);
        $this->qb = $this->connection->createQuery();
        parent::init();
    }
    
    /**
     * 生成日志信息
     * @param array $data 编译日志格式
     * @return array 返回数组形式的数据
     */
    protected function buildTrace($data)
    {
        return $data;
    }
    
    /**
     * 刷新输出日志
     */
    public function flush()
    {
        if ($this->container) {
            $this->qb->batchInsert($this->table, [
                'time', 'ip', 'level', 'request', 'file', 'line', 'data'
            ], $this->container);
            $this->container = [];
        }
    }
}
