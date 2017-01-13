<?php
namespace lying\db;

class Query
{
    /**
     * @var Connection 数据库连接实例
     */
    private $connection;
    
    /**
     * @var array 要查询的字段
     * @see select()
     */
    protected $select = [];
    
    /**
     * @var boolean 是否去重
     * @see distinct()
     */
    protected $distinct = false;
    
    /**
     * @var array 要查询的表
     * @see from()
     */
    protected $from = [];
    
    /**
     * @var array 要关联的表
     * @see join()
     */
    protected $join = [];
    
    /**
     * @var array 查询的条件
     * @see where()
     */
    protected $where = [[], []];

    /**
     * @var array 分组查询的条件
     * @see groupBy()
     */
    protected $groupBy = [];
    
    /**
     * @var array 筛选的条件
     * @see having()
     */
    protected $having = [[], []];
    
    /**
     * @var array 要排序的字段
     * @see orderBy()
     */
    protected $orderBy = [];
    
    /**
     * @var array 偏移和限制的条数
     * @see limit()
     */
    protected $limit = [];
    
    /**
     * @var array 联合查询的Query
     * @see union()
     */
    protected $union = [];
    
    /**
     * 初始化Query查询
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
    
    /**
     * 设置要查询的字段
     * @param string|array $columns 要查询的字段,,当没有设置要查询的字段的时候,默认为'*'
     * e.g. select('id, lying.sex, count(id) as count')
     * e.g. select(['id', 'lying.sex', 'count'=>'count(id)', 'q'=>$query])
     * 其中$query为Query实例,必须指定子查询的别名,只有$columns为数组的时候才支持子查询
     * 注意:当你使用到包含逗号的数据库表达式的时候,你必须使用数组的格式,以避免自动的错误的引号添加
     * e.g. select(["CONCAT(first_name, ' ', last_name) AS full_name", 'email']);
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
     * 去除重复行
     * @return \lying\db\Query
     */
    public function distinct()
    {
        $this->distinct = true;
        return $this;
    }
    
    /**
     * 设置要查询的表
     * @param string|array $tables 要查询的表
     * e.g. from('user, lying.admin as ad')
     * e.g. from(['user', 'ad'=>'lying.admin', 'q'=>$query])
     * 其中$query为Query实例,必须指定子查询的别名,只有$tables为数组的时候才支持子查询
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
     * @param string $type 连接类型,可以为'left join', 'right join', 'inner join'
     * @param string|array $table 要连接的表,子查询用数组形式表示,键为别名,值为Query实例
     * @param string|array $on 条件,如果要使用'字段1 = 字段2'的形式,请用字符串带入,用数组的话'字段2'将被解析为绑定参数
     * @param array $params 绑定的参数,应为key=>value形式
     * @return \lying\db\Query
     */
    public function join($type, $table, $on = null, $params = [])
    {
        $this->join[] = [$type, $table, $on, $params];
        return $this;
    }
    
    /**
     * 设置查询条件
     * @param string|array $condition 要查询的条件
     * 如果要使用'字段1 = 字段2'的形式,请用字符串带入,用数组的话'字段2'将被解析为绑定参数
     * e.g. where("user.id = admin.id and name = :name", [':name'=>'lying']);
     * e.g. where(['id'=>1, 'name'=>'lying']);
     * e.g. where(['id'=>[1, 2, 3]], ['or', 'name'=>'lying', 'sex'=>1]);
     * @param array $params 当$condition为字符串时,绑定参数的数组
     * @return \lying\db\Query
     */
    public function where($condition, $params = [])
    {
        $this->where = [$condition, $params];
        return $this;
    }
    
    /**
     * 设置分组查询
     * @param string|array 要分组的字段
     * e.g. groupBy('id, sex');
     * e.g. groupBy(['id', 'sex']);
     * @return \lying\db\Query
     */
    public function groupBy($columns)
    {
        if (is_string($columns)) {
            $tables = preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY);
        }
        $this->groupBy = $columns;
        return $this;
    }
    
    /**
     * 聚合筛选
     * @param string|array $condition 参见where()
     * @param array $params 参见where()
     * @return \lying\db\Query
     */
    public function having($condition, $params = [])
    {
        $this->having = [$condition, $params];
        return $this;
    }
    
    /**
     * 设置排序
     * @param $columns 要排序的字段和排序方式
     * e.g. orderBy('id, name desc');
     * e.g. orderBy(['id'=>SORT_DESC, 'name']);
     * @return \lying\db\Query
     */
    public function orderBy($columns)
    {
        if (is_string($columns)) {
            $columns = preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY);
            foreach ($columns as $key => $column) {
                if (preg_match('/^(.*?)\s+(asc|desc)$/i', $column, $matches)) {
                    $res[$matches[1]] = strcasecmp($matches[2], 'DESC') ? SORT_ASC : SORT_DESC;
                } else {
                    $res[$column] = SORT_ASC;
                }
            }
            $this->orderBy = isset($res) ? $res : [];
        }else {
            $this->orderBy = $columns;
        }
        return $this;
    }
    
    /**
     * 设置限制查询的条数
     * @param int $offset 偏移的条数,如果只提供此参数,则等同于limit(0, $offset)
     * e.g. limit(10);
     * e.g. limit(5, 20);
     * @param int $limit 限制的条数
     * @return \lying\db\Query
     */
    public function limit($offset, $limit = null)
    {
        $this->limit = [$offset, $limit];
        return $this;
    }
    
    /**
     * 设置联合查询,可多次使用
     * @param Query $query 子查询
     * @param boolean $all 是否使用UNION ALL,默认false
     * @return \lying\db\Query
     */
    public function union(Query $query, $all = false)
    {
        $this->union[] = [$query, $all];
        return $this;
    }
    
    /**
     * 简单地给表名,字段加上"`"
     * e.g. 'name' => '`name`'
     * @param string $name 字段名
     * @return string 字段名
     */
    private function quoteSimple($name)
    {
        return strpos($name, '`') !== false || $name === '*' ? $name : "`$name`";
    }
    
    /**
     * 给复杂的表名,字段加上"`"
     * e.g. 'lying.name' => '`lying`.`name`'
     * 注意:'count(id)'并不会转义成'count(`id`)',而还是原来的'count(id)'
     * @param string $name 字段名
     * @return string 字段名
     */
    private function quoteColumn($name)
    {
        if (strpos($name, '(') !== false) {
            return $name;
        } elseif (strpos($name, '.') !== false) {
            $cols = array_map(function($v) {
                return $this->quoteSimple($v);
            }, preg_split('/\s*\.\s*/', $name, -1, PREG_SPLIT_NO_EMPTY));
            return implode('.', $cols);
        } else {
            return $this->quoteSimple($name);
        }
    }
    
    /**
     * 给复杂的表名,字段加上"`",并且编译别名和子查询,请以数组形式传入字段名和表名
     * 如,
     * e.g. ['lying.name'] => ['`lying`.`name`']
     * e.g. ['lying.name n'] => ['`lying`.`name` AS `n`']
     * e.g. ['lying.name as n'] => ['`lying`.`name` AS `n`']
     * e.g. ['n'=>'lying.name'] => ['`lying`.`name` AS `n`']
     * e.g. ['n'=>$query] => ['(select ...) AS `n`'],其中$query为Query实例
     * @param array $tables 一个存字段名或者表名的数组
     * @param array $container 参数容器
     * @return array 返回编译后的表名数组,数组键名和传入时一样
     */
    private function quoteColumns($columns, &$container)
    {
        foreach ($columns as $key => $val) {
            if ($val instanceof self) {
                list($statement, $container) = $val->build($container);
                $columns[$key] = "($statement) AS " . $this->quoteColumn($key);
            } elseif (is_string($key)) {
                $columns[$key] = $this->quoteColumn($val) . ' AS ' . $this->quoteColumn($key);
            } elseif (preg_match('/^(.*?)(?i:\s+as\s+|\s+)([\w\-_\.]+)$/', $val, $matches)) {
                $columns[$key] = $this->quoteColumn($matches[1]) . ' AS ' . $this->quoteColumn($matches[2]);
            } else {
                $columns[$key] = $this->quoteColumn($val);
            }
        }
        return $columns;
    }
    
    /**
     * 编译条件
     * @param string|array $condition 条件字符串或者数组
     * @param array $params 当$condition为字符串时,绑定参数的数组
     * @param array $container 参数容器
     * @return string 返回编译后的条件字符串
     */
    private function buildCondition($condition, $params, &$container)
    {
        if (empty($condition)) {
            return '';
        } elseif (is_string($condition)) {
            $keys = array_keys($params);
            $place = $this->buildPlaceholders($params, $container);
            return str_replace($keys, $place, $condition);
        } elseif (is_array($condition)) {
            return $this->buildArrayCondition($condition, $container);
        } else {
            return '';
        }
    }
    
    /**
     * 编译数组形式的条件
     * @param array $condition 条件数组
     * @param array $container 参数容器
     * @return string 返回编译后的条件字符串
     */
    private function buildArrayCondition($condition, &$container)
    {
        if (isset($condition[0]) && is_string($condition[0])) {
            if (in_array(strtoupper($condition[0]), ['AND', 'OR'])) {
                $op = strtoupper(array_shift($condition));
            } else {
                return $this->buildOperator($condition, $container);
            }
        }
        foreach ($condition as $key => $value) {
            if (is_string($key)) {
                if (is_array($value)) {
                    $where[$key] = $this->buildOperator(['IN', $key, $value], $container);
                } elseif ($value === null) {
                    $where[$key] = $this->buildOperator(['NULL', $key, true], $container);
                } else {
                    $where[$key] = $this->quoteColumn($key) . " = " . $this->buildPlaceholders($value, $container);
                }
            } elseif (is_array($value)) {
                $where[$key] = $this->buildArrayCondition($value, $container);
            }
        }
        return isset($op) && $op === 'OR' ? '(' . implode(' OR ', $where) . ')' : implode(' AND ', $where);
    }
    
    /**
     * 编译['<', 'id', 1]形式的条件
     * @param array $condition 条件数组
     * @param array $container 参数容器
     * @return string 返回编译后的条件字符串
     */
    private function buildOperator($condition, &$container)
    {
        list($operation, $field, $val) = $condition;
        $field = $this->quoteColumn($field);
        $place = is_bool($val) ? '' : $this->buildPlaceholders($val, $container);
        switch (trim(strtoupper($operation))) {
            case 'IN':
                return "$field IN (" . implode(', ', $place) . ")";
            case 'NOT IN':
                return "$field NOT IN (" . implode(', ', $place) . ")";
            case 'BETWEEN':
                list($p1, $p2) = $place;
                return "$field BETWEEN $p1 AND $p2";
            case 'NOT BETWEEN':
                list($p1, $p2) = $place;
                return "$field NOT BETWEEN $p1 AND $p2";
            case 'LIKE':
                return "$field LIKE $place";
            case 'NOT LIKE':
                return "$field NOT LIKE $place";
            case 'NULL':
                return $val === true ? "$field IS NULL" : "$field IS NOT NULL";
            case 'EXISTS':
                return "EXISTS $place";
            case 'NOT EXISTS':
                return "NOT EXISTS $place";
            default:
                return "$field $operation $place";
        }
    }
    
    /**
     * 绑定参数,编译占位符
     * @param string|array $params 绑定的参数
     * @param array $container 参数容器
     * @return string|array 返回编译后的占位符字符串或者数组
     */
    private function buildPlaceholders($params, &$container)
    {
        if (is_array($params)) {
            foreach ($params as $k => $p) {
                $params[$k] = $this->buildPlaceholders($p, $container);
            }
            return $params;
        } elseif ($params instanceof self) {
            list($statement, $container) = $params->build($container);
            return "($statement)";
        } else {
            $container[] = $params;
            return '?';
        }
    }
    
    /**
     * 编译查询的字段
     * @param array $container 参数容器
     * @return string 返回编译后的查询字段
     */
    private function buildSelect(&$container)
    {
        $columns = $this->quoteColumns($this->select, $container);
        return ($this->distinct ? 'SELECT DISTINCT ' : 'SELECT ') . (empty($columns) ? '*' : implode(', ', $columns));
    }
    
    /**
     * 编译查询的表
     * @param array $container 参数容器
     * @return string 返回编译后的表
     */
    private function buildFrom(&$container)
    {
        $tables = $this->quoteColumns($this->from, $container);
        return empty($tables) ? '' : 'FROM ' . implode(', ', $tables);
    }
    
    /**
     * 编译表关联
     * @param array $container 参数容器
     * @return string 返回编译后关联语句
     */
    private function buildJoin(&$container)
    {
        foreach ($this->join as $key => $join) {
            list($type, $table, $on, $params) = $join;
            $type = strtoupper(trim($type));
            if (in_array($type, ['LEFT JOIN', 'RIGHT JOIN', 'INNER JOIN'])) {
                $tables = $this->quoteColumns((array)$table, $container);
                $table = reset($tables);
                $joins[$key] = "$type $table";
                if (!empty($on)) {
                    $joins[$key] .= ' ON ' . $this->buildCondition($on, $params, $container);
                }
            }
        }
        return isset($joins) ? implode(' ', $joins) : '';
    }
    
    /**
     * 编译查询条件
     * @param array $container 参数容器
     * @return string 返回编译后的条件语句
     */
    private function buildWhere(&$container)
    {
        list($condition, $params) = $this->where;
        $where = $this->buildCondition($condition, $params, $container);
        return empty($where) ? '' : "WHERE $where";
    }
    
    /**
     * 编译分组查询
     * @return string 返回编译后的GROUP BY语句
     */
    private function buildGroupBy()
    {
        foreach ($this->groupBy as $key => $col) {
            $columns[$key] = $this->quoteColumn($col);
        }
        return isset($columns) ? 'GROUP BY ' . implode(', ', $columns) : '';
    }
    
    /**
     * 编译筛选条件
     * @param array $container 参数容器
     * @return string 返回编译后的条件语句
     */
    private function buildHaving(&$container)
    {
        list($condition, $params) = $this->having;
        $having = $this->buildCondition($condition, $params, $container);
        return empty($where) ? '' : "HAVING $having";
    }
    
    /**
     * 编译排序方式
     * @return string 返回排序语句
     */
    private function buildOrderBy()
    {
        $sort_type = [SORT_ASC => 'ASC', SORT_DESC => 'DESC'];
        foreach ($this->orderBy as $name => $type) {
            if (is_string($name)) {
                $sort[] = $this->quoteColumn($name) . ' ' . $sort_type[$type];
            } else {
                $sort[] = $this->quoteColumn($type) . ' ASC';
            }
        }
        return isset($sort) ? 'ORDER BY ' . implode(', ', $sort) : '';
    }
    
    /**
     * 编译偏移和限制的条数
     * @return string 返回编译后的LIMIT语句
     */
    private function buildLimit()
    {
        if (isset($this->limit[1]) && $this->limit[1] !== null) {
            return "LIMIT " . $this->limit[0] . ', ' . $this->limit[1];
        } elseif (isset($this->limit[0])) {
            return "LIMIT " . $this->limit[0];
        } else {
            return '';
        }
    }
    
    /**
     * 编译联合查询语句
     * @param array $container 参数容器
     * @return string 返回联合查询的语句
     */
    private function buildUnion(&$container)
    {
        foreach ($this->union as $union) {
            list($statement, $container) = $union[0]->build($container);
            $unions[] = ($union[1] ? 'UNION ALL ' : 'UNION ') . "($statement)";
        }
        return isset($unions) ? implode(' ', $unions) : '';
    }
    
    /**
     * 组件sql语句
     * @param array $params 引用获取参数
     * @return array 返回[$statement, $params]
     */
    public function build(&$params = [])
    {
        $statement = implode(' ', array_filter([
            $this->buildSelect($params),
            $this->buildFrom($params),
            $this->buildJoin($params),
            $this->buildWhere($params),
            $this->buildGroupBy(),
            $this->buildHaving($params),
            $this->buildOrderBy(),
            $this->buildLimit(),
            $this->buildUnion($params),
        ]));
        return [$statement, $params];
    }
    
    /**
     * 查询数据
     * @param string $method 查询的方法
     * @return mixed 查询的数据
     */
    private function fetch($method)
    {
        list($statement, $params) = $this->build();
        $sth = $this->connection->prepare($statement);
        $sth->execute($params);
        $res = call_user_func([$sth, $method]);
        $sth->closeCursor();
        return $res;
    }
    
    /**
     * 返回结果集中的一条记录
     * @return mixed
     */
    public function one()
    {
        return $this->fetch('fetch');
    }
    
    /**
     * 返回所有查询结果的数组
     * @return mixed
     */
    public function all()
    {
        return $this->fetch('fetchAll');
    }
    
    /**
     * 从结果集中的下一行返回单独的一列,查询结果为标量
     * @return mixed
     */
    public function column()
    {
        return $this->fetch('fetchColumn');
    }
    
    /**
     * 插入一条数据
     * @param string $table 要插入的表名
     * @param array $datas 要插入的数据,(name => value)形式的数组
     * 当然value可以是子查询,Query的实例,但是查询的表不能和插入的表是同一个
     * @return int 返回受影响的行数,有可能是0行
     */
    public function insert($table, $datas)
    {
        foreach ($datas as $col => $data) {
            $cols[] = $this->quoteColumn($col);
            if ($data instanceof self) {
                list($statement, $params) = $data->build($params);
                $palceholders[] = "($statement)";
            } else {
                $palceholders[] = $this->buildPlaceholders($data, $params);
            }
        }
        $table = $this->quoteColumn($table);
        $statement = "INSERT INTO $table (" . implode(', ', $cols) . ') VALUES (' . implode(', ', $palceholders) . ')';
        $sth = $this->connection->prepare($statement);
        $sth->execute($params);
        return $sth->rowCount();
    }
    
    /**
     * 批量插入数据
     * @param string $table 要插入的表名
     * @param array $columns 要插入的字段名
     * @param array $datas 要插入的数据,应为一个二维数组
     * e.g. batchInsert('user', ['name', 'sex'], [
     *     ['user1', 1],
     *     ['user2', 0],
     *     ['user3', 1],
     * ])
     * @return int 返回受影响的行数,有可能是0行
     */
    public function batchInsert($table, $columns, $datas)
    {
        foreach ($datas as $row) {
            $v[] = '(' . implode(', ', $this->buildPlaceholders($row, $params)) . ')';
        }
        $table = $this->quoteColumn($table);
        $columns = array_map(function($col) {
            return $this->quoteColumn($col);
        }, $columns);
        $statement = "INSERT INTO $table (" . implode(', ', $columns) . ') VALUES ' . implode(', ', $v);
        $sth = $this->connection->prepare($statement);
        $sth->execute($params);
        return $sth->rowCount();
    }
    
    /**
     * 更新数据
     * @param string $table 要更新的表
     * @param array $datas 要更新的数据,(name => value)形式的数组
     * 当然value可以是子查询,Query的实例,但是查询的表不能和更新的表是同一个
     * @param string|array $condition 更新的条件,参见where()
     * @param array $params 条件的参数,参见where()
     * @return int 返回受影响的行数,有可能是0行
     */
    public function update($table, $datas, $condition = '', $params = [])
    {
        foreach ($datas as $name => $data) {
            if ($data instanceof self) {
                list($statement, $p) = $data->build($p);
                $palceholders[] = $this->quoteColumn($name) . " = ($statement)";
            } else {
                $palceholders[] = $this->quoteColumn($name) . ' = ' . $this->buildPlaceholders($data, $p);
            }
        }
        $table = $this->quoteColumn($table);
        $statement = "UPDATE $table SET " . implode(', ', $palceholders);
        $where = $this->buildCondition($condition, $params, $p);
        $statement = $statement . (empty($where) ? '' : " WHERE $where");
        $sth = $this->connection->prepare($statement);
        $sth->execute($p);
        return $sth->rowCount();
    }
    
    /**
     * 删除数据
     * @param string $table 要删除的表
     * @param string|array $condition 删除的条件,参见where()
     * @param array $params 条件的参数,参见where()
     * @return int 返回受影响的行数,有可能是0行
     */
    public function delete($table, $condition = '', $params = [])
    {
        $table = $this->quoteColumn($table);
        $statement = "DELETE FROM $table";
        $where = $this->buildCondition($condition, $params, $p);
        $statement = $statement . (empty($where) ? '' : " WHERE $where");
        $sth = $this->connection->prepare($statement);
        $sth->execute($p);
        return $sth->rowCount();
    }
}
