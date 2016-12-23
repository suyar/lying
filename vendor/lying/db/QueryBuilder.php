<?php
namespace lying\db;

class QueryBuilder
{
    /**
     * @var Connection
     */
    private $connection;
    
    
    private $from;
    
    private $select = '*';
    
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
    
    private $limitParams = [];
    
    
    
    /**
     * @param Connection $connection
     */
    public function __construct($connection)
    {
        $this->connection = $connection;
    }
    
    public function getWhere()
    {
        var_dump($this->join, $this->joinParams);
    }
    
    
    
    /**
     * 设置要查询的字段
     * select("id, admin.username, password as pass");
     * select(['id', 'admin.username', 'pass'=>'password']);
     * `
     * @param array|string $fields 接收一个数组,指定键值为别名
     * @return $this
     */
    public function select($fields)
    {
        if (is_array($fields)) {
            foreach ($fields as $k=>$f) {
                $fields[$k] = is_string($k) ? "$f AS $k" : $f;
            }
            $this->select = implode(', ', $fields);
        }else {
            $this->select = $fields;
        }
        return $this;
    }
    
    /**
     * 设置去重
     * @return $this
     */
    public function distinct()
    {
        $this->distinct = 'DISTINCT';
        return $this;
    }
    
    /**
     * 设置要操作的表
     * `
     * from("user, lying.admin, file as f");
     * from(['user', 'lying.admin', 'f'=>'file']);
     * `
     * @param array|string $table 接收一个数组,指定键值为别名
     * @return $this
     */
    public function from($table)
    {
        if (is_array($table)) {
            foreach ($table as $k=>$t) {
                $table[$k] = is_string($k) ? "$t AS $k" : $t;
            }
            $this->from = implode(', ', $table);
        }else {
            $this->from = $table;
        }
        return $this;
    }
    
    /**
     * 设置关联
     * `
     * join('LEFT JOIN', 'user', "admin.id = user.id")
     * join('LEFT JOIN', 'user', "admin.id = user.id AND user = :user", [':user'=>'susu'])
     * `
     * @param string $type 关联类型'INNER JOIN', 'LEFT JOIN', 'RIGHT JOIN'
     * @param string $table 要关联的表
     * @param string $on 关联条件,只能为字符串
     * @param array $params 如果on条件有参数,写在这里.参考where的字符串形式
     * @return $this
     */
    public function join($type, $table, $on, $params = [])
    {
        $type = strtoupper($type);
        if (in_array($type, ['INNER JOIN', 'LEFT JOIN', 'RIGHT JOIN'])) {
            $condition = $this->buildCondition($on, $params,$this->joinParams);
            $this->join .= ($this->join ? ' ' : '') . "$type $table ON $condition";
        }
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
     * @param array|string $condition
     * @param array $params 绑定的参数
     * @return $this
     */
    public function where($condition, $params = [])
    {
        $this->whereParams = [];
        $this->where = 'WHERE ' . $this->buildCondition($condition, $params, $this->whereParams);
        return $this;
    }
    
    /**
     * where添加AND条件
     * @param array|string $condition
     * @param array $params 绑定的参数
     * @return $this
     */
    public function andWhere($condition, $params = [])
    {
        $where = $this->buildCondition($condition, $params, $this->whereParams);
        $this->where .= ($this->where ? " AND $where" : "WHERE $where");
        return $this;
    }
    
    /**
     * where添加OR条件
     * @param array|string $condition
     * @param array $params 绑定的参数
     * @return $this
     */
    public function orWhere($condition, $params = [])
    {
        $where = $this->buildCondition($condition, $params, $this->whereParams);
        $this->where .= ($this->where ? " OR $where" : "WHERE $where");
        return $this;
    }
    
    /**
     * 设置分组
     * `
     * groupBy("id, name");
     * groupBy(['id', 'name']);
     * `
     * @param array|string $columns
     * @return $this
     */
    public function groupBy($columns)
    {
        $this->groupBy = 'GROUP BY ' . (is_string($columns) ? $columns : implode(', ', $columns));
        return $this;
    }
    
    /**
     * 设置having条件
     * @param array|string $condition 和where用法一样
     * @param array $params 绑定的参数
     * @return $this
     */
    public function having($condition, $params = [])
    {
        $this->havingParams = [];
        $this->having = 'HAVING ' . $this->buildCondition($condition, $params, $this->havingParams);
        return $this;
    }
    
    /**
     * having添加AND条件
     * @param array|string $condition
     * @param array $params 绑定的参数
     * @return $this
     */
    public function andHaving($condition, $params = [])
    {
        $having = $this->buildCondition($condition, $params, $this->whereParams);
        $this->having .= ($this->having ? " AND $having" : "HAVING $having");
        return $this;
    }
    
    /**
     * having添加OR条件
     * @param array|string $condition
     * @param array $params 绑定的参数
     * @return $this
     */
    public function orHaving($condition, $params = [])
    {
        $having = $this->buildCondition($condition, $params, $this->whereParams);
        $this->having .= ($this->having ? " OR $having" : "HAVING $having");
        return $this;
    }
    
    /**
     * 设置排序
     * `
     * orderBy("id desc, name asc, sex asc");
     * orderBy(['id'=>SORT_DESC, 'name'=>SORT_ASC, 'sex']);
     * `
     * @param array|string $sort
     * @return $this
     */
    public function orderBy($sort)
    {
        if (is_array($sort)) {
            $sort_arr = [SORT_ASC=>'ASC', SORT_DESC=>'DESC'];
            foreach ($sort as $k=>$v) {
                $sort[$k] = is_string($k) ? "$k $sort_arr[$v]" : "$v ASC";
            }
            $this->orderBy = 'ORDER BY ' . implode(', ', $sort);
        }else {
            $this->orderBy = $sort;
        }
        return $this;
    }
    
    /**
     * 设置查询条数限制
     * `
     * limit(1);
     * limit(1, 2);
     * `
     * @param int $offset
     * @param int $limit
     * @return $this
     */
    public function limit($offset, $limit = null)
    {
        $this->limit = 'LIMIT ?';
        $this->limitParams[] = $offset;
        if ($limit) {
            $this->limit .= ', ?';
            $this->limitParams[] = $limit;
        }
        return $this;
    }
    
    /**
     * 组建where、having等条件
     * @param array|string $condition
     * @param array $params 绑定的参数
     * @param array $paramsContainer 装载绑定参数的容器
     * @return string
     */
    private function buildCondition(&$condition, &$params, &$paramsContainer)
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
     * @param array $paramsContainer 装载绑定参数的容器
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
     * @param array $condition 类似['>=', 'id', 3]
     * @param array $paramsContainer 装载绑定参数的容器
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
     * 添加绑定的参数
     * @param string|array $params
     */
    private function addParams($params, &$paramsContainer)
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
     * 组建查询语句
     * @return array 返回数组[$statement, $params]
     */
    public function buildQuery()
    {
        $statement = [
            'SELECT',
            $this->distinct,
            $this->select,
            'FROM',
            $this->from,
            $this->join,
            $this->where,
            $this->groupBy,
            $this->having,
            $this->orderBy,
            $this->limit,
        ];
        $params = array_merge($this->joinParams, $this->whereParams, $this->havingParams, $this->limitParams);
        $statement = array_filter($statement);
        return [$statement, $params];
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
     * `
     * batchInsert(['username', 'password'], [
     *     ['q', 'qqq'],
     *     ['w', 'www'],
     *     ['e', 'eee'],
     * ]);
     * `
     * @param array $fields 要插入的字段
     * @param array $data 要插入的数据,一个二维数组.数组的键值是什么并没有关系,但是第二维的数组的数量应该和字段的数量一致.
     * @return boolean
     */
    public function batchInsert($fields, $data)
    {
        $statement = "INSERT INTO $this->from (" . implode(', ', $fields) . ") VALUES ";
        $placeholder = "(" . implode(', ', array_fill(0, count($fields), '?')) . ")";
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