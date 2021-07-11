<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\db;

/**
 * Class Query
 * @package lying\db
 */
class Query1
{
    /**
     * @var array 要查询的字段
     * @see select()
     */
    private $_select = [];

    /**
     * @var boolean 是否去重
     * @see distinct()
     */
    private $_distinct = false;

    /**
     * @var array 要查询的表
     * @see from()
     */
    private $_from = [];

    /**
     * 设置要查询的字段
     * ```php
     * select('id, lying.sex, count(id) as count')
     * select(['id', 'lying.sex', 'count'=>'count(id)', 'q'=>$query])
     * 其中$query为Query实例,必须指定子查询的别名,只有$columns为数组的时候才支持子查询
     * 注意:当你使用到包含逗号的数据库表达式的时候,你必须使用数组的格式,以避免自动的错误的引号添加
     * select(["CONCAT(first_name, ' ', last_name) AS full_name", 'email']);
     * ```
     * @param string|array $columns 要查询的字段,当没有设置要查询的字段的时候,默认为'*'
     * @return $this
     */
    public function select($columns)
    {
        if (is_string($columns)) {
            $this->_select = preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY);
        } elseif (is_array($columns)) {
            $this->_select = $columns;
        }
        return $this;
    }

    /**
     * 去除重复行
     * @return $this
     */
    public function distinct()
    {
        $this->_distinct = true;
        return $this;
    }

    /**
     * 设置要查询的表
     * ```php
     * from('user, lying.admin as ad')
     * from(['user', 'ad'=>'lying.admin', 'q'=>$query])
     * 其中$query为Query实例,必须指定子查询的别名,只有$tables为数组的时候才支持子查询
     * ```
     * @param string|array $tables 要查询的表
     * @return $this
     */
    public function from($tables)
    {
        if (is_string($tables)) {
            $this->_from = preg_split('/\s*,\s*/', trim($tables), -1, PREG_SPLIT_NO_EMPTY);
        } elseif (is_array($tables)) {
            $this->_from = $tables;
        }
        return $this;
    }
}
