<?php
namespace lying\db;

class QueryBuilder
{
    /**
     * @var Connection
     */
    private $connection;
    
    
    private $from;
    
    private $select = "";
    
    private $distinct;
    
    private $where = '';
    
    private $orderBy = '';
    
    private $groupBy = '';
    
    private $limit = '';
    
    private $having = '';
    
    private $join = '';
    
    private $whereParams = [];
    
    private $havingParams = [];
    
    private $joinParams = [];
    
    
    
    /**
     * @param Connection $connection
     */
    public function __construct($connection)
    {
        $this->connection = $connection;
    }
    
    /**
     * 把"lying.user"转换成"`lying`.`user`"
     * @param string $value
     * @return string
     */
    private function quoteColumn(&$value)
    {
        if (is_string($value)) {
            if (strpos($value, '.') === false) {
                $value = "`$value`";
            }else {
                list($db, $tb) = explode('.', $value);
                $value = "`$db`.`$tb`";
            }
        }else {
            foreach ($value as $k=>&$v) {
                $this->quoteColumn($v);
            }
        }
        
    }
    
    /**
     * 设置要操作的表
     * @param string|array $table
     * @return $this
     */
    public function from($table)
    {
        if (is_array($table)) {
            $tb = [];
            foreach ($table as $k=>$t) {
                $this->quoteColumn($t);
                $tb[] = is_string($k) ? "$t as $k" : $t;
            }
            $this->from = implode(', ', $tb);
        }else {
            $this->quoteColumn($table);
            $this->from = $table;
        }
        return $this;
    }
    
    /**
     * 设置要查询的字段
     * `
     * select("id, username, password as pass");
     * select(['id', 'username', 'pass'=>'password']);
     * 
     * `
     * @param string|array $fields 如果为$key=>$value对的话,会被解析为$value as $key
     * @return $this
     */
    public function select($fields)
    {
        $this->select = $this->combineSelect($fields);
        return $this;
    }
    
    /**
     * 增加要查询的字段
     * @param string|array $fields
     * @return $this
     */
    public function addSelect($fields)
    {
        $select = $this->combineSelect($fields);
        $this->select .= ($this->select ? ", $select" : $select);
        return $this;
    }
    
    /**
     * 组合要查询的字段
     * @param string|array $fields
     * @return string
     */
    private function combineSelect($fields)
    {
        if (is_string($fields)) {
            $this->quoteColumn($fields);
            return $fields;
        }else {
            $select = [];
            foreach ($fields as $k=>$field) {
                $this->quoteColumn($field);
                $select[] = is_string($k) ? "$field as $k" : $field;
            }
            return implode(', ', $select);
        }
    }
    
    /**
     * 设置查询条数限制
     * `
     * limit(1);
     * limit('1, 2');
     * limit([1, 2]);
     * `
     * @param int|string|array $limit
     * @return $this
     */
    public function limit($limit)
    {
        $this->limit = is_array($limit) ? "LIMIT $limit[0], $limit[1]" : "LIMIT $limit";
        return $this;
    }
    
    /**
     * 排序
     * `
     * orderBy('id ASC');
     * orderBy(['id'=>SORT_DESC, 'name'=>SORT_ASC, 'sex']); //ORDER BY id DESC, name ASC, sex ASC
     * `
     * @param string|array $sort
     * @return $this
     */
    public function orderBy($sort)
    {
        if (is_string($sort)) {
            $this->quoteColumn($sort);
            $this->orderBy = "ORDER BY $sort";
        }else {
            $sorts = [];
            $sort_arr = [SORT_ASC=>'ASC', SORT_DESC=>'DESC'];
            foreach ($sort as $k=>$v) {
                $this->quoteColumn($v);
                $sorts[] = is_string($k) ? "$k $sort_arr[$v]" : "$v ASC";
            }
            $this->orderBy = 'ORDER BY ' . implode(', ', $sorts);
        }
        return $this;
    }
    
    /**
     * 分组
     * `
     * groupBy('id, name');
     * groupBy(['id', 'name']);
     * `
     * @param string|array $columns
     * @return $this
     */
    public function groupBy($columns)
    {
        $this->groupBy = 'GROUP BY ' . (is_string($columns) ? $columns : implode(', ', $columns));
        return $this;
    }
    
    
    public function join($table, $type, $condition, $params = [])
    {
        
    }
    
    /**
     * 去重
     * @return $this
     */
    public function distinct()
    {
        $this->distinct = 'DISTINCT';
        return $this;
    }
    
    public function getWhere()
    {
        var_dump($this->where, $this->whereParams);
    }
    
    /**
     * 设置having条件
     * @see \lying\db\QueryBuilder::where
     * @param string|array $condition
     * @param array $params
     * @return $this
     */
    public function having($condition, $params = [])
    {
        $this->having = $this->buildCondition($condition, $params, $this->havingParams);
        return $this;
    }
    
    /**
     * 添加AND条件
     * @see \lying\db\QueryBuilder::Having
     * @param string|array $condition
     * @param array $params
     * @return $this
     */
    public function andHaving($condition, $params = [])
    {
        $having = $this->buildCondition($condition, $params, $this->whereParams);
        $this->having .= ($this->having ? " AND $having" : $having);
        return $this;
    }
    
    /**
     * 添加OR条件
     * @see \lying\db\QueryBuilder::having
     * @param string|array $condition
     * @param array $params
     * @return $this
     */
    public function orHaving($condition, $params = [])
    {
        $having = $this->buildCondition($condition, $params, $this->whereParams);
        $this->having .= ($this->having ? " OR $having" : $having);
        return $this;
    }
    
    /**
     * 设置条件
     * where("id = 1 AND name = 'lying'");
     * where("id = :id AND name = :name", [':id'=>1, ':name'=>'suyaqi']);
     * where(['id'=>1, 'name'=>null]); 注：'name'=>null的形式将被解析为name IS NULL
     * where(['null', 'name', true]); //name IS NULL
     * eg.//id = ? AND num <= ?
     * where([
     *     ['=', 'id', 1],
     *     ['<=', 'num', $num]
     * ]);
     * eg.//username = ? OR id = ? AND num <= ? OR (id = ? OR num <= ?)
     * where([
     *     'or',
     *     'username'=>'lying',
     *     [
     *         'and',
     *         ['=', 'id', 1],
     *         ['<=', 'num', $num]
     *     ],
     *     [
     *         'or',
     *         ['=', 'id', 2],
     *         ['<=', 'num', $num]
     *     ],
     * ]);
     * @param string|array $condition
     * @param array $params
     * @return $this
     */
    public function where($condition, $params = [])
    {
        $this->where = $this->buildCondition($condition, $params, $this->whereParams);
        return $this;
    }
    
    /**
     * 添加AND条件
     * @see \lying\db\QueryBuilder::where
     * @param string|array $condition
     * @param array $params
     * @return $this
     */
    public function andWhere($condition, $params = [])
    {
        $where = $this->buildCondition($condition, $params, $this->whereParams);
        $this->where .= ($this->where ? " AND $where" : $where);
        return $this;
    }
    
    /**
     * 添加OR条件
     * @see \lying\db\QueryBuilder::where
     * @param string|array $condition
     * @param array $params
     * @return $this
     */
    public function orWhere($condition, $params = [])
    {
        $where = $this->buildCondition($condition, $params, $this->whereParams);
        $this->where .= ($this->where ? " OR $where" : $where);
        return $this;
    }
    
    /**
     * 组建where、having等条件
     * @param string|array $condition
     * @param array $params
     * @return string
     */
    private function buildCondition(&$condition, &$params = [], &$paramsContainer)
    {
        if (is_array($condition)) {
            return $this->buildArrayCondition($condition, $paramsContainer);
        }elseif (is_string($condition)) {
            if ($params) {
                $condition = str_replace(array_keys($params), '?', $condition);
                $this->addParams($params, $paramsContainer);
            }
            return $condition;
        }else {
            return '';
        }
    }
    
    /**
     * 组建数组形式的条件
     * @param array $condition
     * @return string
     */
    private function buildArrayCondition(&$condition, &$paramsContainer)
    {
        $op = 'AND';
        if (isset($condition[0]) && is_string($condition[0])) {
            if (in_array(strtoupper($condition[0]), ['AND', 'OR'])) {
                $op = strtoupper(array_shift($condition));
            }else {
                return $this->buildOperator($condition, $paramsContainer);
            }
        }
        $where = [];
        foreach ($condition as $key=>$value) {
            if (is_array($value)) {
                if (isset($value[0]) && is_string($value[0]) && in_array(strtoupper($value[0]), ['AND', 'OR'])) {
                    $where[] = $this->buildArrayCondition($value, $paramsContainer);
                }else {
                    $where[] = $this->buildOperator($value, $paramsContainer);
                }
            }else {
                if ($value === null) {
                    $where[] = "$key IS NULL";
                }else {
                    $where[] = "$key = ?";
                    $this->addParams($value, $paramsContainer);
                }
            }
        }
        return $op === 'OR' ? '(' . implode(" $op ", $where) . ')' : implode(" $op ", $where);
    }
    
    /**
     * 组件条件运算符
     * @param array $condition
     * @return string
     */
    private function buildOperator(&$condition, &$paramsContainer)
    {
        list($operation, $field, $val) = $condition;
        switch (strtoupper($operation)) {
            case 'IN':
                $this->addParams($val, $paramsContainer);
                return "$field IN (" . implode(', ', array_fill(0, count($val), '?')) . ")";
            case 'NOT IN':
                $this->addParams($val, $paramsContainer);
                return "$field NOT IN (" . implode(', ', array_fill(0, count($val), '?')) . ")";
            case 'BETWEEN':
                $this->addParams($val, $paramsContainer);
                return "$field BETWEEN ? AND ?";
            case 'NOT BETWEEN':
                $this->addParams($val, $paramsContainer);
                return "$field NOT BETWEEN ? AND ?";
            case 'LIKE':
                $this->addParams($val, $paramsContainer);
                return "$field LIKE ?";
            case 'NOT LIKE':
                $this->addParams($val, $paramsContainer);
                return "$field NOT LIKE ?";
            case 'NULL':
                if ($val === true) {
                    return "$field IS NULL";
                }else {
                    return "$field IS NOT NULL";
                }
            default:
                $this->addParams($val, $paramsContainer);
                return "$field $operation ?";
        }
    }
    
    /**
     * 批量添加绑定的参数
     * @param mixed $params
     */
    public function addParams($params, &$paramsContainer)
    {
        if (is_array($params)) {
            foreach ($params as $p) {
                $paramsContainer[] = $p;
            }
        }else {
            $paramsContainer[] = $params;
        }
    }
    
    /**
     * 插入一条数据
     * `
     * insert(['id'=>1, 'name'=>'su']);
     * `
     * @param array $data 接收一个关联数组,其中键为字段名,值为字段值.
     * @return boolean 成功返回true,失败返回false
     */
    public function insert($data)
    {
        $keys = array_keys($data);
        $placeholder = array_fill(0, count($data), '?');
        $statement = "INSERT INTO $this->from (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $placeholder) . ")";
        return $this->connection->PDO()->prepare($statement)->execute(array_values($data));
    }
    
    /**
     * 批量插入数据
     * 
     * batchInsert(['username', 'password'], [
     *     ['q', 'qqq'],
     *     ['w', 'www'],
     *     ['e', 'eee'],
     * ]);
     * 
     * @param array $fields 要插入的字段
     * @param array $data 要插入的数据,一个二维数组.数组的键值是什么并没有关系,但是第二维的数组的数量应该和字段的数量一致.
     * @return boolean
     */
    public function batchInsert($fields, $data)
    {
        $statement = "INSERT INTO $this->from (" . implode(', ', $fields) . ") VALUES ";
        $fieldNum = count($fields);
        $placeholder = "(" . implode(', ', array_fill(0, $fieldNum, '?')) . ")";
        $params = $placeholders = [];
        foreach ($data as $d) {
            $placeholders[] = $placeholder;
            foreach ($d as $v) {
                $params[] = $v;
            }
        }
        return $this->connection->PDO()->prepare($statement . implode(', ', $placeholders))->execute($params);
    }
}