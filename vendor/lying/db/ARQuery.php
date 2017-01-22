<?php
namespace lying\db;

class ARQuery extends Query
{
    /**
     * @var string 类名
     */
    private $className;
    
    /**
     * 初始化连接
     * @param Connection $connection 数据库连接
     * @param string $class 要实例化的类名
     */
    public function __construct(Connection $connection, $class = null)
    {
        $this->connection = $connection;
        $this->className = $class;
    }
    
    /**
     * 返回查询的对象的实例
     * @param boolean $obj 在这边没作用
     * @param string $class 在这边没作用
     * @return AR
     */
    public function one($obj = false, $class = null)
    {
        $instance = parent::one(true, $this->className);
        $instance->toOld($instance);
        return $instance;
    }
    

    
}