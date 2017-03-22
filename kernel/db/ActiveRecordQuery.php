<?php
namespace lying\db;

/**
 * 活动记录查询基类
 *
 * @author carolkey <me@suyaqi.cn>
 * @since 2.0
 * @link https://carolkey.github.io/
 * @license MIT
 */
class ActiveRecordQuery extends Query
{
    /**
     * @var string 类名
     */
    private $classMode;
    
    /**
     * @var boolean 是否返回数组
     */
    private $isArray = false;
    
    /**
     * 初始化连接
     * @param Connection $connection 数据库连接
     * @param string $class 要实例化的类名
     */
    public function __construct(Connection $connection, $class = null)
    {
        $this->connection = $connection;
        $this->classMode = $class;
    }
    
    /**
     * 调用此方法则返回数组
     * @return \lying\db\ActiveRecordQuery
     */
    public function asArray()
    {
        $this->isArray = true;
        return $this;
    }
    
    /**
     * 返回查询的对象的实例
     * @param boolean $obj 在这边没作用
     * @param string $class 在这边没作用
     * @return ActiveRecord|boolean|array
     */
    public function one($obj = false, $class = null)
    {
        $row = $this->isArray ? parent::one() : parent::one(true, $this->classMode);
        return $row instanceof ActiveRecord ? $row::populate($row) : $row;
    }
    
    /**
     * 返回查询的对象的实例数组
     * @param boolean $obj 在这边没作用
     * @param string $class 在这边没作用
     * @return ActiveRecord[]|boolean|array
     */
    public function all($obj = false, $class = null)
    {
        $rows = $this->isArray ? parent::all() : parent::all(true, $this->classMode);
        if (!$this->isArray && is_array($rows)) {
            foreach ($rows as $key => $row) {
                $rows[$key] = $row::populate($row);
            }
        }
        return $rows;
    }
}
