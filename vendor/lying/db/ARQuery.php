<?php
namespace lying\db;

class ARQuery extends Query
{
    /**
     * @var string 类名
     */
    private $className;
    
    /**
     * @var boolean 是否返回数组
     */
    private $array = false;
    
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
     * 调用此方法则返回数组
     * @return \lying\db\ARQuery
     */
    public function asArray()
    {
        $this->array = true;
        return $this;
    }
    
    /**
     * 返回查询的对象的实例
     * @param boolean $obj 在这边没作用
     * @param string $class 在这边没作用
     * @return AR|boolean|array
     */
    public function one($obj = false, $class = null)
    {
        $row = $this->array ? parent::one() : parent::one(true, $this->className);
        return $row instanceof AR ? $row::populate($row) : $row;
    }
    
    /**
     * 返回查询的对象的实例数组
     * @param boolean $obj 在这边没作用
     * @param string $class 在这边没作用
     * @return AR[]|boolean|array
     */
    public function all($obj = false, $class = null)
    {
        $rows = $this->array ? parent::all() : parent::all(true, $this->className);
        if (!$this->array && is_array($rows)) {
            foreach ($rows as $key => $row) {
                $rows[$key] = $row::populate($row);
            }
        }
        return $rows;
    }
}
