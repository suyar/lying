<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\db;

/**
 * Class ActiveQuery
 * @package lying\db
 */
class ActiveQuery extends Query
{
    /**
     * @var string 类名
     */
    protected $class;
    
    /**
     * @var bool 是否返回数组
     */
    private $_isArray;

    /**
     * 调用此方法则返回数组
     * @return $this
     */
    public function asArray()
    {
        $this->_isArray = true;
        return $this;
    }
    
    /**
     * 返回查询的对象的实例
     * @param bool $obj 在这边没作用
     * @param string $class 在这边没作用
     * @return ActiveRecord|array|false 成功返回查询结果,失败返回false
     */
    public function one($obj = false, $class = null)
    {
        $row = $this->_isArray ? parent::one() : parent::one(true, $this->class);
        return $row instanceof ActiveRecord ? $row->reload() : $row;
    }
    
    /**
     * 返回查询的对象的实例数组
     * @param bool $obj 在这边没作用
     * @param string $class 在这边没作用
     * @return ActiveRecord[]|array|false 成功返回查询结果,失败返回false
     */
    public function all($obj = false, $class = null)
    {
        $rows = $this->_isArray ? parent::all() : parent::all(true, $this->class);
        if (!$this->_isArray && is_array($rows)) {
            foreach ($rows as $key => $row) {
                $rows[$key] = $row->reload();
            }
        }
        return $rows;
    }
}
