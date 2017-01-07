<?php
namespace lying\db;

class Query
{
    
    protected $select;
    
    protected $distinct;
    
    protected $from;
    
    protected $join;
    
    protected $where;

    protected $groupBy;
    
    protected $having;
    
    protected $orderBy;
    
    
    protected $limit;
    
    
    /**
     * 设置要查询的字段
     * @param string|array $columns 要查询的字段
     * select('id, lying.sex, count(id) as count')
     * select(['id', 'lying.sex', 'count'=>'count(id)', 'q'=>$query])
     * 其中$query为Query实例子查询,子查询返回一个字段名,最好如上设置别名
     * 只有$columns为数组的时候才支持子查询
     * @return \lying\db\Query
     */
    public function select($columns)
    {
        if (is_string($columns)) {
            $columns = preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY);
        }
        $this->select = $columns;
        return $this;
    }
    
    /**
     * 是否使用DISTINCT选项
     * @param boolean $use 是否使用,默认为true
     * @return \lying\db\Query
     */
    public function distinct($use = true)
    {
        $this->distinct = true;
        return $this;
    }
    
    /**
     * 设置要查询的表
     * @param string|array $tables 要查询的表
     * from('user, lying.admin as ad')
     * from(['user', 'ad'=>'lying.admin', 'q'=>$query])
     * 其中$query为Query实例子查询,必须指定子查询的别名
     * 只有$tables为数组的时候才支持子查询
     * @return \lying\db\Query
     */
    public function from($tables)
    {
        if (is_string($tables)) {
            $tables = preg_split('/\s*,\s*/', trim($tables), -1, PREG_SPLIT_NO_EMPTY);
        }
        $this->from = $tables;
        return $this;
    }
    
    /**
     * 设置查询条件
     * @param string|array $condition 查询条件
     * @param array $params 当$condition为字符串的时候,绑定参数
     */
    public function where($condition, $params = [])
    {
        $this->where = $condition;
        
    }
    
}