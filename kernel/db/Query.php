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
class Query
{
    /**
     * @var Connection 数据库连接实例
     */
    private $conn;

    /**
     * @var \PDOStatement
     */
    private $sth;

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
     * 初始化查询构造器
     * @param Connection $conn 数据库连接实例
     */
    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

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
        $this->select = is_string($columns) ? preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY) : $columns;
        return $this;
    }

    /**
     * 去除重复行
     * @return $this
     */
    public function distinct()
    {
        $this->distinct = true;
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
        $this->from = is_string($tables) ? preg_split('/\s*,\s*/', trim($tables), -1, PREG_SPLIT_NO_EMPTY) : $tables;
        return $this;
    }

    /**
     * 设置表连接,可多次调用
     * @param string $type 连接类型,可以为'left join','right join','inner join'
     * @param string|array $table 要连接的表,子查询用数组形式表示,键为别名,值为Query实例
     * @param string|array $on 条件,如果要使用'字段1 = 字段2'的形式,请用字符串带入,用数组的话'字段2'将被解析为绑定参数
     * @param array $params 绑定的参数,应为key=>value形式
     * @return $this
     */
    public function join($type, $table, $on = null, $params = [])
    {
        $this->join[] = [$type, $table, $on, $params];
        return $this;
    }

    /**
     * 设置查询条件
     * ```php
     * 如果要使用'字段1 = 字段2'的形式,请用字符串带入,用数组的话'字段2'将被解析为绑定参数
     * where("user.id = admin.id and name = :name", [':name'=>'lying']);
     * where(['id'=>1, 'name'=>'lying']);
     * where(['id'=>[1, 2, 3], ['or', 'name'=>'lying', 'sex'=>1]]);
     * ```
     * @param string|array $condition 要查询的条件
     * @param array $params 当$condition为字符串时,绑定参数的数组
     * @return $this
     */
    public function where($condition, $params = [])
    {
        $this->where = [$condition, $params];
        return $this;
    }

    /**
     * 设置分组查询
     * ```php
     * groupBy('id, sex');
     * groupBy(['id', 'sex']);
     * ```
     * @param string|array 要分组的字段
     * @return $this
     */
    public function groupBy($columns)
    {
        if (is_string($columns)) {
            $columns = preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY);
        }
        $this->groupBy = $columns;
        return $this;
    }

    /**
     * 聚合筛选
     * @see where()
     * @param string|array $condition 参见where()
     * @param array $params 参见where()
     * @return $this
     */
    public function having($condition, $params = [])
    {
        $this->having = [$condition, $params];
        return $this;
    }

    /**
     * 设置排序
     * ```php
     * orderBy('id, name desc');
     * orderBy(['id'=>SORT_DESC, 'name']);
     * ```
     * @param string|array $columns 要排序的字段和排序方式
     * @return $this
     */
    public function orderBy($columns)
    {
        if (is_string($columns)) {
            $columns = preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY);
            foreach ($columns as $key => $column) {
                if (preg_match('/^(.*?)\s+(asc|desc)$/i', $column, $matches)) {
                    $this->orderBy[$matches[1]] = strcasecmp($matches[2], 'DESC') ? SORT_ASC : SORT_DESC;
                } else {
                    $this->orderBy[$column] = SORT_ASC;
                }
            }
        } else {
            $this->orderBy = $columns;
        }
        return $this;
    }

    /**
     * 设置限制查询的条数
     * ```php
     * limit(10);
     * limit(5, 20);
     * ```
     * @param int $offset 偏移的条数,如果只提供此参数,则等同于limit(0, $offset)
     * @param int $limit 限制的条数
     * @return $this
     */
    public function limit($offset, $limit = null)
    {
        $this->limit = [$offset, $limit];
        return $this;
    }

    /**
     * 设置联合查询,可多次使用
     * @param Query $query 子查询
     * @param bool $all 是否使用UNION ALL,默认false
     * @return $this
     */
    public function union(Query $query, $all = false)
    {
        $this->union[] = [$query, $all];
        return $this;
    }

    /**
     * 简单地给表名、字段加上"`"
     * ```php
     * 'name' => '`name`'
     * ```
     * @param string $name 字段名
     * @return string 字段名
     */
    private function quoteSimple($name)
    {
        return strpos($name, '`') !== false || $name === '*' ? $name : "`$name`";
    }

    /**
     * 给复杂的表名、字段加上"`"
     * ```php
     * 'lying.name' => '`lying`.`name`'
     * ```
     * 注意:'count(id)'并不会转义成'count(`id`)',而还是原来的'count(id)'
     * @param string $name 字段名
     * @return string 字段名
     */
    private function quoteColumn($name)
    {
        if (strpos($name, '(') !== false) {
            return $name;
        } elseif (strpos($name, '.') !== false) {
            $cols = array_map(function ($v) {
                return $this->quoteSimple($v);
            }, preg_split('/\s*\.\s*/', $name, -1, PREG_SPLIT_NO_EMPTY));
            return implode('.', $cols);
        } else {
            return $this->quoteSimple($name);
        }
    }

    /**
     * 给复杂的表名,字段加上"`",并且编译别名和子查询,请以数组形式传入字段名和表名
     * ```php
     * ['lying.name'] => ['`lying`.`name`']
     * ['lying.name n'] => ['`lying`.`name` AS `n`']
     * ['lying.name as n'] => ['`lying`.`name` AS `n`']
     * ['n'=>'lying.name'] => ['`lying`.`name` AS `n`']
     * ['n'=>$query] => ['(select ...) AS `n`'],其中$query为Query实例
     * ```
     * @param array $columns 一个存字段名或者表名的数组
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
        $where = [];
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
        $field = is_array($field) ? implode(', ', array_map([$this, 'quoteColumn'], $field)) : $this->quoteColumn($field);
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
            case 'MATCH':
                return "MATCH ($field) AGAINST (?)";
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
        $joins = [];
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
        return implode(' ', $joins);
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
        $columns = [];
        foreach ($this->groupBy as $key => $col) {
            $columns[$key] = $this->quoteColumn($col);
        }
        return empty($columns) ? '' : 'GROUP BY ' . implode(', ', $columns);
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
        $sort = [];
        foreach ($this->orderBy as $name => $type) {
            if (is_string($name)) {
                $sort[] = $this->quoteColumn($name) . ' ' . $sort_type[$type];
            } else {
                $sort[] = $this->quoteColumn($type) . ' ASC';
            }
        }
        return empty($sort) ? '' : 'ORDER BY ' . implode(', ', $sort);
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
        $unions = [];
        foreach ($this->union as $union) {
            list($statement, $container) = $union[0]->build($container);
            $unions[] = ($union[1] ? 'UNION ALL ' : 'UNION ') . "($statement)";
        }
        return implode(' ', $unions);
    }

    /**
     * 组建SQL语句
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
     * 判断是否为读取数据
     * @param string $statement
     * @return bool
     */
    private function isRead($statement)
    {
        $pattern = '/^\s*(SELECT|SHOW|DESCRIBE|DESC|EXPLAIN)\b/i';
        return preg_match($pattern, $statement) > 0;
    }

    /**
     * 执行一条SQL语句
     * @param string $statement SQL语句
     * @param array $params 绑定的参数
     * @return bool 成功返回true,失败返回false
     */
    private function execute($statement, $params = [])
    {
        $pdo = $this->isRead($statement) ? $this->conn->slavePdo() : $this->conn->masterPdo();
        $this->sth = $pdo->prepare($statement);
        return $this->sth->execute($params);
    }

    /**
     * 查询数据
     * @param string $method 查询的方法
     * @param array $args 要带入的参数列表
     * @return bool|array 查询的数据,失败返回false
     */
    private function fetch($method, $args = [])
    {
        list($statement, $params) = $this->build();
        $res = $this->execute($statement, $params) ? call_user_func_array([$this->sth, $method], $args) : false;
        $this->sth->closeCursor();
        return $res;
    }

    /**
     * 返回结果集中的一条记录
     * @param bool $obj 是否返回对象(默认返回关联数组)
     * @param string $class 要实例化的对象,不写默认为匿名对象
     * @return mixed 成功返回查询结果,失败返回false
     */
    public function one($obj = false, $class = null)
    {
        return $this->fetch($obj ? 'fetchObject' : 'fetch', $class === null ? [] : [$class]);
    }

    /**
     * 返回所有查询结果的数组
     * @param bool $obj 是否返回对象(默认返回关联数组)
     * @param string $class 要实例化的对象,不写默认为匿名对象
     * @return mixed 成功返回查询结果,失败返回false
     */
    public function all($obj = false, $class = null)
    {
        return $this->fetch('fetchAll', $obj ? ($class === null ? [\PDO::FETCH_OBJ] : [\PDO::FETCH_CLASS, $class]) : []);
    }

    /**
     * 从结果集中的下一行返回单独的一个字段值,查询结果为标量
     * @param int $column_number 你想从行里取回的列的索引数字,以0开始
     * @return mixed 返回查询结果,查询结果为标量
     */
    public function scalar($column_number = 0)
    {
        return $this->fetch('fetchColumn', [$column_number ]);
    }

    /**
     * 从结果集中的取出第一列的值
     * @return array|bool 返回查询结果
     */
    public function column()
    {
        return $this->fetch('fetchAll', [\PDO::FETCH_COLUMN]);
    }

    /**
     * 获取指定列的记录数
     * @param string $column 要统计的列
     * @return int 返回记录数
     */
    public function count($column = '*')
    {
        return $this->select(['count' => "COUNT($column)"])->scalar();
    }

    /**
     * 获取指定列所有值之和
     * @param string $column 要相加的列
     * @return int 返回相加后的值
     */
    public function sum($column)
    {
        return $this->select(['sum' => "SUM($column)"])->scalar();
    }

    /**
     * 获取指定列的最大值
     * @param string $column 计算的列
     * @return int 最大值
     */
    public function max($column)
    {
        return $this->select(['max' => "MAX($column)"])->scalar();
    }

    /**
     * 获取指定列的最小值
     * @param string $column 计算的列
     * @return int 最小值
     */
    public function min($column)
    {
        return $this->select(['min' => "MIN($column)"])->scalar();
    }

    /**
     * 获取指定列的平均值
     * @param string $column 计算的列
     * @return int 平均值
     */
    public function avg($column)
    {
        return $this->select(['avg' => "AVG($column)"])->scalar();
    }

    /**
     * 执行原生SQL,返回的是语句执行后的\PDOStatement对象,直接调用fetch,fetchAll,rowCount等函数即可
     * ```php
     * $query->raw('select * from user')->fetchAll(\PDO::FETCH_ASSOC);
     * ```
     * @param string $statement SQL语句
     * @param array $params 绑定的参数
     * @param bool $master 是否使用主库执行语句,这个参数适合FOR UPDATE等操作
     * @return bool|\PDOStatement 失败返回false
     */
    public function raw($statement, $params = [], $master = false)
    {
        $pdo = !$master && $this->isRead($statement) ? $this->conn->slavePdo() : $this->conn->masterPdo();
        $this->sth = $pdo->prepare($statement);
        return $this->execute($statement, $params) ? $this->sth : false;
    }

    /**
     * 执行查询语句(使用从库)
     * ```php
     * $query->query('select * from user where id=:id', [':id'=>1]);
     * ```
     * @param string $statement SQL语句
     * @param array $params 绑定的参数
     * @return array|bool 成功返回查询的数据数组,失败返回false
     */
    public function query($statement, $params = [])
    {
        $sth = $this->raw($statement, $params);
        return false == $sth ? $sth : $sth->fetchAll();
    }

    /**
     * 执行写入语句(使用主库)
     * ```php
     * $query->exec('update user set num=num+1 where id=:id', [':id'=>1]);
     * ```
     * @param string $statement SQL语句
     * @param array $params 绑定的参数
     * @return bool|int 成功返回受影响的行数(可能为0行),失败返回false
     */
    public function exec($statement, $params = [])
    {
        $sth = $this->raw($statement, $params, true);
        return false == $sth ? $sth : $sth->rowCount();
    }

    /**
     * 字段值自增
     * ```php
     * $query->from('user')->where(['id'=>1])->inc('num');
     * ```
     * @param string $field 字段名
     * @param int $num 自增的值,必须为正整数
     * @return bool|int 成功返回受影响的行数,失败返回false
     */
    public function inc($field, $num = 1)
    {
        $params = [];
        $tables = implode(', ', $this->quoteColumns($this->from, $params));
        $field = $this->quoteColumn($field);
        $where = $this->buildWhere($params);
        array_unshift($params, abs(intval($num)));
        $statement = "UPDATE $tables SET $field=$field+?" . ($where ? " $where" : '');
        return $this->exec($statement, $params);
    }

    /**
     * 字段值自减
     * ```php
     * $query->from('user')->where(['id'=>1])->dec('num');
     * ```
     * @param string $field 字段名
     * @param int $num 自减的值,必须为正整数
     * @return bool|int 成功返回受影响的行数,失败返回false
     */
    public function dec($field, $num = 1)
    {
        $params = [];
        $tables = implode(', ', $this->quoteColumns($this->from, $params));
        $field = $this->quoteColumn($field);
        $where = $this->buildWhere($params);
        array_unshift($params, abs(intval($num)));
        $statement = "UPDATE $tables SET $field=$field-?" . ($where ? " $where" : '');
        return $this->exec($statement, $params);
    }

    /**
     * 插入一条数据
     * @param string $table 要插入的表名
     * @param array $datas 要插入的数据,(name => value)形式的数组
     * 当然value可以是子查询,Query的实例,但是查询的表不能和插入的表是同一个
     * @param bool $replace 是否用REPLACE INTO
     * @return int|bool 返回受影响的行数,有可能是0行,失败返回false
     */
    public function insert($table, $datas, $replace = false)
    {
        $cols = $placeholders = $params = [];
        foreach ($datas as $col => $data) {
            $cols[] = $this->quoteColumn($col);
            if ($data instanceof self) {
                list($statement, $params) = $data->build($params);
                $placeholders[] = "($statement)";
            } else {
                $placeholders[] = $this->buildPlaceholders($data, $params);
            }
        }
        $table = $this->quoteColumn($table);
        $statement = ($replace ? 'REPLACE INTO' : 'INSERT INTO') . " $table (" . implode(', ', $cols) . ') VALUES (' . implode(', ', $placeholders) . ')';
        return $this->execute($statement, $params) ? $this->sth->rowCount() : false;
    }

    /**
     * 批量插入数据
     * ```php
     * batchInsert('user', ['name', 'sex'], [
     *     ['user1', 1],
     *     ['user2', 0],
     *     ['user3', 1],
     * ])
     * ```
     * @param string $table 要插入的表名
     * @param array $columns 要插入的字段名
     * @param array $datas 要插入的数据,应为一个二维数组
     * @param bool $replace 是否用REPLACE INTO
     * @return int|bool 返回受影响的行数,有可能是0行,失败返回false
     */
    public function batchInsert($table, $columns, $datas, $replace = false)
    {
        $params = $v = [];
        foreach ($datas as $row) {
            $v[] = '(' . implode(', ', $this->buildPlaceholders($row, $params)) . ')';
        }
        $table = $this->quoteColumn($table);
        $columns = array_map(function($col) {
            return $this->quoteColumn($col);
        }, $columns);
        $statement = ($replace ? 'REPLACE INTO' : 'INSERT INTO') . " $table (" . implode(', ', $columns) . ') VALUES ' . implode(', ', $v);
        return $this->execute($statement, $params) ? $this->sth->rowCount() : false;
    }

    /**
     * 更新数据
     * @param string $table 要更新的表名
     * @param array $datas 要更新的数据,(name => value)形式的数组
     * 当然value可以是子查询,Query的实例,但是查询的表不能和更新的表是同一个
     * @param string|array $condition 更新的条件,参见where()
     * @param array $params 条件的参数,参见where()
     * @return int|bool 返回受影响的行数,有可能是0行,失败返回false
     */
    public function update($table, $datas, $condition = '', $params = [])
    {
        $placeholders = [];
        foreach ($datas as $name => $data) {
            if ($data instanceof self) {
                list($statement, $p) = $data->build($p);
                $placeholders[] = $this->quoteColumn($name) . " = ($statement)";
            } else {
                $placeholders[] = $this->quoteColumn($name) . ' = ' . $this->buildPlaceholders($data, $p);
            }
        }
        $table = $this->quoteColumn($table);
        $statement = "UPDATE $table SET " . implode(', ', $placeholders);
        $where = $this->buildCondition($condition, $params, $p);
        $statement = $statement . (empty($where) ? '' : " WHERE $where");
        return $this->execute($statement, $p) ? $this->sth->rowCount() : false;
    }

    /**
     * 删除数据
     * @param string $table 要删除的表名
     * @param string|array $condition 删除的条件,参见where()
     * @param array $params 条件的参数,参见where()
     * @return int|bool 返回受影响的行数,有可能是0行,失败返回false
     */
    public function delete($table, $condition = '', $params = [])
    {
        $table = $this->quoteColumn($table);
        $statement = "DELETE FROM $table";
        $p = [];
        $where = $this->buildCondition($condition, $params, $p);
        $statement = $statement . (empty($where) ? '' : " WHERE $where");
        return $this->execute($statement, $p) ? $this->sth->rowCount() : false;
    }
}
