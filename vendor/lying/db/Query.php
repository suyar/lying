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
     * 设置要查询的字段,当没有设置要查询的字段的时候,默认为'*'
     * @param string|array $columns 要查询的字段
     * select('id, lying.sex, count(id) as count')
     * select(['id', 'lying.sex', 'count'=>'count(id)', 'q'=>$query])
     * 其中$query为Query实例子查询,子查询返回一个字段名,必须指定子查询的别名
     * 只有$columns为数组的时候才支持子查询
     * 注意:当你使用到包含逗号的数据库表达式的时候,你必须使用数组的格式,以避免自动的错误的引号添加,例如:
     * select(["CONCAT(first_name, ' ', last_name) AS full_name", 'email']);
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
     * 去重
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
     * 设置表连接,可多次调用
     * @param string $type 连接类型,left join,right join,inner join
     * @param string|array $table 要连接的表,子查询用数组形式表示,键值为别名
     * @param string|array $on 条件,如果要使用'字段1 = 字段2'的形式,请用字符串带入,用数组的话字段2将被解析为绑定参数
     * @param array $params 绑定的参数,应为key=>value形式
     * @return \lying\db\Query
     */
    public function join($type, $table, $on = null, $params = [])
    {
        $this->join[] = [$type, $table, $on, $params];
        return $this;
    }
    
    
    /**
     * 
     * @param string|array $condition
     * where("id = 1 and name = :name", [':name'=>'lying']);
     * where(['id'=>1, 'name'=>'lying']);
     * where();
     * @param array $params
     * @return \lying\db\Query
     */
    public function where($condition, $params = [])
    {
        $this->where = [$condition, $params];
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
    private function quoteColumns($columns, &$container)
    {
        foreach ($columns as $key => $val) {
            if ($val instanceof self) {
                list($statememt, $params) = $val->build();
                $container = array_merge($container, $params);
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
    private function buildSelect(&$container)
    {
        $columns = $this->quoteColumns($this->select, $container);
        return ($this->distinct ? 'SELECT DISTINCT ' : 'SELECT ') . (empty($columns) ? '*' : implode(', ', $columns));
    }
    
    /**
     * 组建查询的表
     * @param array $params 绑定的参数
     * @return string
     */
    private function buildFrom(&$container)
    {
        $tables = $this->quoteColumns($this->from, $container);
        return empty($tables) ? '' : 'FROM ' . implode(', ', $tables);
    }
    
    /**
     * 组建表关联
     * @param array $params 绑定的参数
     * @return string
     */
    private function buildJoin(&$container)
    {
        $joins = [];
        foreach ($this->join as $key => $join) {
            list($type, $table, $on, $params) = $join;
            $type = strtoupper(trim($type));
            if (in_array($type, ['LEFT JOIN', 'RIGHT JOIN', 'INNER JOIN'])) {
                $tables = $this->quoteColumns((array)$table, $container);
                $table = reset($tables);
                $joins[$key] = "$type $table";
                if (!empty($on)) {
                    $condition = $this->buildCondition($on, $params, $container);
                    $joins[$key] .= " ON $condition";
                }
            }
        }
        return implode(' ', $joins);
    }
    
    
    
    private function buildWhere(&$container)
    {
        if (empty($this->where)) {
            return '';
        }
        list($condition, $params) = $this->where;
        $where = $this->buildCondition($condition, $params, $container);
        return empty($where) ? '' : "WHERE $where";
    }
    
    
    
    private function buildPlaceholders($params, &$container)
    {
        if (is_array($params)) {
            foreach ($params as $k => $p) {
                $params[$k] = $this->buildPlaceholders($p, $container);
            }
            return $params;
        }elseif ($params instanceof self) {
            list($statememt, $p) = $params->build();
            $container = array_merge($container, $p);
            return "($statememt)";
        }else {
            $container[] = $params;
            return '?';
        }
    }
    
    
    public function buildCondition($condition, $params, &$container)
    {
        if (empty($condition)) {
            return '';
        }elseif (is_string($condition)) {
            $keys = array_keys($params);
            $place = $this->buildPlaceholders($params, $container);
            return str_replace($keys, $place, $condition);
        }elseif (is_array($condition)) {
            return $this->buildArrayCondition($condition, $container);
        }else {
            return '';
        }
    }
    
    
    public function buildArrayCondition($condition, &$container)
    {
        $op = 'AND';
        if (isset($condition[0]) && is_string($condition[0])) {
            if (in_array(strtoupper($condition[0]), ['AND', 'OR'])) {
                $op = strtoupper(array_shift($condition));
            }
        }
    }
    
    public function build()
    {
        $params = [];
        $sql = implode(' ', [
            $this->buildSelect($params),
            $this->buildFrom($params),
            $this->buildJoin($params),
            $this->buildWhere($params),
            
        ]);
        var_dump($sql, $params);
    }
    
}