<?php
/**
 * @author carolkey <me@suyaqi.cn>
 * @link https://github.com/carolkey/lying
 * @copyright 2017 Lying
 * @license MIT
 */

namespace lying\db;

/**
 * Class ActiveRecordQuery
 * @package lying\db
 * @since 2.0
 */
class ActiveRecordQuery extends Query
{
    /**
     * @var string 类名
     */
    private $class;
    
    /**
     * @var boolean 是否返回数组
     */
    private $array;
    
    /**
     * 初始化连接
     * @param Connection $connection 数据库连接
     * @param string $class 要实例化的类名
     */
    public function __construct(Connection $connection, $class = null)
    {
        parent::__construct($connection);
        $this->class = $class;
    }
    
    /**
     * 调用此方法则返回数组
     * @return \lying\db\ActiveRecordQuery
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
     * @return boolean|array|\stdClass|\lying\db\ActiveRecord
     */
    public function one($obj = false, $class = null)
    {
        $row = $this->array ? parent::one() : parent::one(true, $this->class);
        return $row instanceof ActiveRecord ? $row->reload() : $row;
    }
    
    /**
     * 返回查询的对象的实例数组
     * @param boolean $obj 在这边没作用
     * @param string $class 在这边没作用
     * @return boolean|array|\stdClass[]|\lying\db\ActiveRecord[]
     */
    public function all($obj = false, $class = null)
    {
        $rows = $this->array ? parent::all() : parent::all(true, $this->class);
        if (!$this->array && is_array($rows)) {
            foreach ($rows as $key => $row) {
                $rows[$key] = $row->reload();
            }
        }
        return $rows;
    }
}
