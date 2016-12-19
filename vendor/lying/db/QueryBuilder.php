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
    
    private $where;
    
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
        var_dump($this->where);
    }
    
    /**
     * 设置条件
     * ```
     * where("id = 1 AND name = 'lying'");
     * where("id = :id AND name = :name", [':id'=>1, ':name'=>'suyaqi']);
     * where(['id'=>1, 'name'=>null]); 注：'name'=>null的形式将被解析为name IS NULL
     * where([
     *     ['=', 'id', 1],
     *     ['<=', 'num', $num]
     * ]);
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
     * ```
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
        if (isset($condition[0]) && is_string($condition[0]) && in_array(strtoupper($condition[0]), ['AND', 'OR'])) {
            $op = strtoupper(array_shift($condition));
        }
        $where = [];
        foreach ($condition as $key=>$value) {
            if (is_array($value)) {
                if (isset($value[0]) && is_string($value[0]) && in_array(strtoupper($value[0]), ['AND', 'OR'])) {
                    $where[] = $this->buildCondition($value);
                }else {
                    list($operation, $field, $val) = $value;
                    switch (strtoupper($operation)) {
                        case 'IN':
                            $where[] = "`$field` IN (" . implode(', ', array_fill(0, count($val), '?')) . ")";
                            $this->addParams($val);
                            break;
                        case 'NOT IN':
                            $where[] = "`$field` NOT IN (" . implode(', ', array_fill(0, count($val), '?')) . ")";
                            $this->addParams($val);
                            break;
                        case 'BETWEEN':
                            $where[] = "`$field` BETWEEN ? AND ?";
                            $this->addParams($val);
                            break;
                        case 'NOT BETWEEN':
                            $where[] = "`$field` NOT BETWEEN ? AND ?";
                            $this->addParams($val);
                            break;
                        case 'LIKE':
                            $where[] = "`$field` LIKE ?";
                            $this->addParam($val);
                            break;
                        case 'NOT LIKE':
                            $where[] = "`$field` NOT LIKE ?";
                            $this->addParam($val);
                            break;
                        case 'NULL':
                            if ($val === true) {
                                $where[] = "`$field` IS NULL";
                            }else {
                                $where[] = "`$field` IS NOT NULL";
                            }
                            break;
                        default:
                            $where[] = "`$field` $operation ?";
                            $this->addParam($val);
                    }
                }
            }else {
                if ($value === null) {
                    $where[] = "`$key` IS NULL";
                }else {
                    $where[] = "`$key` = ?";
                    $this->addParam($value);
                }
            }
        }
        return $op === 'OR' ? '(' . implode(" $op ", $where) . ')' : implode(" $op ", $where);
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
     * @param array $data 接收一个关联数组,其中键为字段名,值为字段值.注:键名不在表的字段的数据将被过滤掉.
     * @return boolean 成功返回true,失败返回false
     */
    public function insert($data)
    {
        $this->filterData($data);
        $keys = array_keys($data);
        $placeholder = array_fill(0, count($data), '?');
        $statement = "INSERT INTO `$this->from` (`" . implode('`, `', $keys) . "`) VALUES (" . implode(', ', $placeholder) . ")";
        return $this->connection->PDO()->prepare($statement)->execute(array_values($data));
    }
    
    /**
     * 批量插入数据
     * ```
     * $db->createQuery()->from('user')->batchInsert(['username', 'password'], [
     *     ['q', 'qqq'],
     *     ['w', 'www'],
     *     ['e', 'eee'],
     * ]);
     * ```
     * @param array $fields 要插入的字段,非表中的字段会被过滤掉.
     * @param array $data 要插入的数据,一个二维数组.数组的键值是什么病没有关系,但是第二维的数组的数量应该和字段的数量一致.
     * @return boolean
     */
    public function batchInsert($fields, $data)
    {
        $fields = array_intersect($fields, $this->connection->getSchema($this->from)->fields);
        $statement = "INSERT INTO `$this->from` (`" . implode('`, `', $fields) . "`) VALUES ";
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
    
    /**
     * 过滤键值非标中字段的数据
     * @param array $data
     */
    public function filterData(&$data)
    {
        $fields = $this->connection->getSchema($this->from)->fields;
        foreach ($data as $k=>$v) {
            if (!in_array($k, $fields)) {
                unset($data[$k]);
            }
        }
    }
    
    
}