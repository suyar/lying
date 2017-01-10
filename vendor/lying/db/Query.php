<?php
namespace lying\db;

class Query
{
    protected $select = [];
    
    protected $distinct = false;
    
    protected $from = [];
    
    protected $join = [];
    
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
     * 其中$query为Query实例子查询,子查询返回一个字段名,必须指定子查询的别名
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
     * 设置表连接
     * @param string $type 连接类型,left join,right join,inner join
     * @param string|array $table 要连接的表,子查询用数组形式表示,键值为别名
     * @param string|array $on 条件,如果要使用'字段1 = 字段2'的形式,请用字符串带入,用数组的话字段2将被解析为绑定参数
     * @param array $params 绑定的参数
     * @return \lying\db\Query
     */
    public function join($type, $table, $on = '', $params = [])
    {
        $this->join[] = [$type, $table, $on, $params];
        return $this;
    }
    
    
    
    //======================================================================================//
    
    /**
     * 给字段加上"`"
     * @param string $name
     * @return string
     */
    private function quoteSimple($name)
    {
        return strpos($name, '`') !== false || $name === '*' ? $name : "`$name`";
    }
    
    /**
     * 给字段加上"`"
     * @param string $name
     * @return string
     */
    private function quoteColumn($name)
    {
        if (strpos($name, '(') !== false) {
            return $name;
        }elseif (strpos($name, '.') !== false) {
            $cols = array_map(function($v) {
                return $this->quoteSimple($v);
            }, preg_split('/\s*\.\s*/', $name, -1, PREG_SPLIT_NO_EMPTY));
            return implode('.', $cols);
        }else {
            return $this->quoteSimple($name);
        }
    }
    
    /**
     * 给字段加上"`"
     * @param array $tables
     * @param array $params
     * @return array
     */
    private function quoteColumns($columns, &$params)
    {
        foreach ($columns as $key => $val) {
            if ($val instanceof self) {
                list($statememt, $p) = $val->build();
                $params = array_merge($params, $p);
                $columns[$key] = "($statememt) AS " . $this->quoteColumn($key);
            }elseif (is_string($key)) {
                $columns[$key] = $this->quoteColumn($val) . ' AS ' . $this->quoteColumn($key);
            }elseif (preg_match('/^(.*?)(?i:\s+as\s+|\s+)([\w\-_\.]+)$/', $val, $matches)) {
                $columns[$key] = $this->quoteColumn($matches[1]) . ' AS ' . $this->quoteColumn($matches[2]);
            }else {
                $columns[$key] = $this->quoteColumn($val);
            }
        }
        return $columns;
    }
    
    /**
     * 组建查询的字段
     * @param array $params 绑定的参数
     * @return string
     */
    private function buildSelect(&$params)
    {
        $columns = $this->quoteColumns($this->select, $params);
        return ($this->distinct ? 'SELECT DISTINCT ' : 'SELECT ') . (empty($columns) ? '*' : implode(', ', $columns));
    }
    
    /**
     * 组建查询的表
     * @param array $params 绑定的参数
     * @return string
     */
    private function buildFrom(&$params)
    {
        $tables = $this->quoteColumns($this->from, $params);
        return empty($tables) ? '' : 'FROM ' . implode(', ', $tables);
    }
    
    
    private function buildJoin(&$params)
    {
        $joins = [];
        foreach ($this->join as $key => $join) {
            list($type, $table, $on, $par) = $join;
            $type = strtoupper(trim($type));
            $tables = $this->quoteColumns((array)$table, $params);
            $table = reset($tables);
            if (in_array($type, ['LEFT JOIN', 'RIGHT JOIN', 'INNER JOIN'])) {
                $joins[$key] = "$type $table";
            }
        }
        return implode(' ', $joins);
    }
    
    
    public function buildCondition($condition, &$params)
    {
        
    }
    
    public function build()
    {
        $params = [];
        $select = $this->buildSelect($params);
        $from = $this->buildFrom($params);
        $join = $this->buildJoin($params);
        var_dump($select, $from, $join);
    }
    
}