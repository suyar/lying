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
    
    private $params = [];
    
    
    
    /**
     * @param Connection $connection
     */
    public function __construct($connection)
    {
        $this->connection = $connection;
    }
    
    /**
     * 设置要操作的表
     * @param string $table
     * @return $this
     */
    public function from($table)
    {
        $this->from = "$table";
        return $this;
    }
    
    /**
     * 设置要查询的字段
     * @param array $fields 如果为$key=>$value对的话,会被解析为$key as $val
     * @return $this
     */
    public function select($fields)
    {
        $select = [];
        foreach ($fields as $key=>$field) {
            if (is_string($key)) {
                $select[] = "$key as $field";
            }else {
                $select[] = $field;
            }
        }
        $select = implode(', ', $select);
        $this->select = $select ? $select : '*';
        return $this;
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
        if (is_array($limit)) {
            $this->limit = "LIMIT $limit[0], $limit[1]";
        }else {
            $this->limit = "LIMIT $limit";
        }
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
            $this->orderBy = "ORDER BY $sort";
        }else {
            $sorts = [];
            $sort_arr = [SORT_ASC=>'ASC', SORT_DESC=>'DESC'];
            foreach ($sort as $k=>$v) {
                if (is_string($k)) {
                    $sorts[] = "$k $sort_arr[$v]";
                }else {
                    $sorts[] = "$v ASC";
                }
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
        var_dump($this->orderBy);
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
        $this->where = $this->buildWhere($condition, $params);
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
        $where = $this->buildWhere($condition, $params);
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
        $where = $this->buildWhere($condition, $params);
        $this->where .= ($this->where ? " OR $where" : $where);
        return $this;
    }
    
    /**
     * 组建where条件
     * @param string|array $condition
     * @param array $params
     * @return string
     */
    private function buildWhere($condition, $params = [])
    {
        if (is_array($condition)) {
            return $this->buildCondition($condition);
        }elseif (is_string($condition)) {
            if ($params) {
                $condition = str_replace(array_keys($params), '?', $condition);
                $this->addParams($params);
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
    private function buildCondition($condition)
    {
        $op = 'AND';
        if (isset($condition[0]) && is_string($condition[0])) {
            if (in_array(strtoupper($condition[0]), ['AND', 'OR'])) {
                $op = strtoupper(array_shift($condition));
            }else {
                return $this->buildOperator($condition);
            }
        }
        $where = [];
        foreach ($condition as $key=>$value) {
            if (is_array($value)) {
                if (isset($value[0]) && is_string($value[0]) && in_array(strtoupper($value[0]), ['AND', 'OR'])) {
                    $where[] = $this->buildCondition($value);
                }else {
                    $where[] = $this->buildOperator($value);
                }
            }else {
                if ($value === null) {
                    $where[] = "$key IS NULL";
                }else {
                    $where[] = "$key = ?";
                    $this->addParam($value);
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
    private function buildOperator(&$condition)
    {
        list($operation, $field, $val) = $condition;
        switch (strtoupper($operation)) {
            case 'IN':
                $this->addParams($val);
                return "$field IN (" . implode(', ', array_fill(0, count($val), '?')) . ")";
            case 'NOT IN':
                $this->addParams($val);
                return "$field NOT IN (" . implode(', ', array_fill(0, count($val), '?')) . ")";
            case 'BETWEEN':
                $this->addParams($val);
                return "$field BETWEEN ? AND ?";
            case 'NOT BETWEEN':
                $this->addParams($val);
                return "$field NOT BETWEEN ? AND ?";
            case 'LIKE':
                $this->addParam($val);
                return "$field LIKE ?";
            case 'NOT LIKE':
                $this->addParam($val);
                return "$field NOT LIKE ?";
            case 'NULL':
                if ($val === true) {
                    return "$field IS NULL";
                }else {
                    return "$field IS NOT NULL";
                }
            default:
                $this->addParam($val);
                return "$field $operation ?";
        }
    }
    
    
    /**
     * 添加一个绑定的参数
     * @param string|int|null $param
     */
    public function addParam($param) {
        $this->params[] = $param;
    }
    
    /**
     * 批量添加绑定的参数
     * @param array $params
     */
    public function addParams($params)
    {
        foreach ($params as $p) {
            $this->params[] = $p;
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