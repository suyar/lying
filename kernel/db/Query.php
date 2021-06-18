<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\db;

use lying\service\Service;

/**
 * Class Query
 * @package lying\db
 */
class Query extends BaseActive
{
    /**
     * @var array 要查询的字段
     * @see select()
     */
    protected $_select = [];

    /**
     * @var boolean 是否去重
     * @see distinct()
     */
    protected $_distinct = false;

    /**
     * @var array 要查询的表
     * @see from()
     */
    protected $_from = [];

    /**
     * @var array 要关联的表
     * @see join()
     */
    protected $_join = [];

    /**
     * @var array 查询的条件
     * @see where()
     */
    protected $_where = [];

    /**
     * @var array 分组查询的条件
     * @see groupBy()
     */
    protected $_groupBy = [];

    /**
     * @var array 筛选的条件
     * @see having()
     */
    protected $_having = [];

    /**
     * @var array 要排序的字段
     * @see orderBy()
     */
    protected $_orderBy = [];

    /**
     * @var array 偏移和限制的条数
     * @see limit()
     */
    protected $_limit = [];

    /**
     * @var array 联合查询的Query
     * @see union()
     */
    protected $_union = [];

    /**
     * @var Connection 数据库连接实例
     */
    protected $db;

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
     * @param bool $distinct 是否去重
     * @return $this
     */
    public function distinct($distinct = true)
    {
        $this->_distinct = $distinct;
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
        $this->_join[] = [$type, $table, $on, $params];
        return $this;
    }

    /**
     * 设置表左连接,可多次调用
     * @param string|array $table 要连接的表,子查询用数组形式表示,键为别名,值为Query实例
     * @param string|array $on 条件,如果要使用'字段1 = 字段2'的形式,请用字符串带入,用数组的话'字段2'将被解析为绑定参数
     * @param array $params 绑定的参数,应为key=>value形式
     * @return $this
     */
    public function leftJoin($table, $on = null, $params = [])
    {
        return $this->join('LEFT JOIN', $table, $on, $params);
    }

    /**
     * 设置表右连接,可多次调用
     * @param string|array $table 要连接的表,子查询用数组形式表示,键为别名,值为Query实例
     * @param string|array $on 条件,如果要使用'字段1 = 字段2'的形式,请用字符串带入,用数组的话'字段2'将被解析为绑定参数
     * @param array $params 绑定的参数,应为key=>value形式
     * @return $this
     */
    public function rightJoin($table, $on = null, $params = [])
    {
        return $this->join('RIGHT JOIN', $table, $on, $params);
    }

    /**
     * 设置表连接,可多次调用
     * @param string|array $table 要连接的表,子查询用数组形式表示,键为别名,值为Query实例
     * @param string|array $on 条件,如果要使用'字段1 = 字段2'的形式,请用字符串带入,用数组的话'字段2'将被解析为绑定参数
     * @param array $params 绑定的参数,应为key=>value形式
     * @return $this
     */
    public function innerJoin($table, $on = null, $params = [])
    {
        return $this->join('INNER JOIN', $table, $on, $params);
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
        $this->_where = [[$condition, $params]];
        return $this;
    }

    /**
     * 添加AND条件
     * @param string|array $condition 要查询的条件
     * @param array $params 当$condition为字符串时,绑定参数的数组
     * @return $this
     * @see where()
     */
    public function  andWhere($condition, $params = [])
    {
        if ($this->_where) {
            $this->_where[] = ['AND', $condition, $params];
        } else {
            $this->where($condition, $params);
        }
        return $this;
    }

    /**
     * 添加OR条件
     * @param string|array $condition 要查询的条件
     * @param array $params 当$condition为字符串时,绑定参数的数组
     * @return $this
     * @see where()
     */
    public function orWhere($condition, $params = [])
    {
        if ($this->_where) {
            $this->_where[] = ['OR', $condition, $params];
        } else {
            $this->where($condition, $params);
        }
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
            $this->_groupBy = preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY);
        } elseif (is_array($columns)) {
            $this->_groupBy = $columns;
        }
        return $this;
    }

    /**
     * 聚合筛选
     * @param string|array $condition 参见where()
     * @param array $params 参见where()
     * @return $this
     * @see where()
     */
    public function having($condition, $params = [])
    {
        $this->_having = [[$condition, $params]];
        return $this;
    }

    /**
     * 添加AND条件
     * @param string|array $condition 要查询的条件
     * @param array $params 当$condition为字符串时,绑定参数的数组
     * @return $this
     * @see having()
     */
    public function  andHaving($condition, $params = [])
    {
        if ($this->_having) {
            $this->_having[] = ['AND', $condition, $params];
        } else {
            $this->having($condition, $params);
        }
        return $this;
    }

    /**
     * 添加OR条件
     * @param string|array $condition 要查询的条件
     * @param array $params 当$condition为字符串时,绑定参数的数组
     * @return $this
     * @see having()
     */
    public function orHaving($condition, $params = [])
    {
        if ($this->_having) {
            $this->_having[] = ['OR', $condition, $params];
        } else {
            $this->having($condition, $params);
        }
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
                    $this->_orderBy[$matches[1]] = strcasecmp($matches[2], 'DESC') ? SORT_ASC : SORT_DESC;
                } else {
                    $this->_orderBy[$column] = SORT_ASC;
                }
            }
        } elseif (is_array($columns)) {
            $this->_orderBy = $columns;
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
        $this->_limit = [$offset, $limit];
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
        $this->_union[] = [$query, $all];
        return $this;
    }

    /**
     * 给复杂的字段加上"`",并且编译别名和子查询,请以数组形式传入字段名
     * ```php
     * ['lying.name'] => ['`lying`.`name`']
     * ['lying.name n'] => ['`lying`.`name` AS `n`']
     * ['lying.name as n'] => ['`lying`.`name` AS `n`']
     * ['n'=>'lying.name'] => ['`lying`.`name` AS `n`']
     * ['n'=>$query] => ['(select ...) AS `n`'],其中$query为Query实例
     * ```
     * @param array $columns 一个字段名数组
     * @param array $container 参数容器
     * @return array 返回编译后的数组,数组键名和传入时一样
     */
    private function quoteColumnNames($columns, &$container)
    {
        foreach ($columns as $name => $value) {
            if ($value instanceof Query) {
                list($sql, $container) = $value->build($container);
                $columns[$name] = "($sql) AS " . $this->db->schema()->quoteColumnName($name);
            } elseif (is_string($name)) {
                $columns[$name] = $this->db->schema()->quoteColumnName($value) . ' AS ' . $this->db->schema()->quoteColumnName($name);
            } elseif (preg_match('/^(.*?)(?i:\s+as\s+|\s+)([\w\-_\.]+)$/', $value, $matches)) {
                $columns[$name] = $this->db->schema()->quoteColumnName($matches[1]) . ' AS ' . $this->db->schema()->quoteColumnName($matches[2]);
            } else {
                $columns[$name] = $this->db->schema()->quoteColumnName($value);
            }
        }
        return $columns;
    }

    /**
     * 给复杂的表名加上"`",并且编译别名和子查询,请以数组形式传入表名
     * ```php
     * ['lying.name'] => ['`lying`.`name`']
     * ['lying.name n'] => ['`lying`.`name` AS `n`']
     * ['lying.name as n'] => ['`lying`.`name` AS `n`']
     * ['n'=>'lying.name'] => ['`lying`.`name` AS `n`']
     * ['n'=>$query] => ['(select ...) AS `n`'],其中$query为Query实例
     * ```
     * @param array $tables 一个表名的数组
     * @param array $container 参数容器
     * @return array 返回编译后的数组,数组键名和传入时一样
     */
    private function quoteTableNames($tables, &$container)
    {
        foreach ($tables as $name => $value) {
            if ($value instanceof Query) {
                list($sql, $container) = $value->build($container);
                $tables[$name] = "($sql) AS " . $this->db->schema()->quoteTableName($name);
            } elseif (is_string($name)) {
                $tables[$name] = $this->db->schema()->quoteTableName($value) . ' AS ' . $this->db->schema()->quoteTableName($name);
            } elseif (preg_match('/^(.*?)(?i:\s+as|)\s+([^ ]+)$/', $value, $matches)) {
                $tables[$name] = $this->db->schema()->quoteTableName($matches[1]) . ' AS ' . $this->db->schema()->quoteTableName($matches[2]);
            } else {
                $tables[$name] = $this->db->schema()->quoteTableName($value);
            }
        }
        return $tables;
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
            $placeholders = $this->buildPlaceholders($params, $container);
            $params_str = $params_num = [];
            foreach ($placeholders as $name => $placeholder) {
                if (is_string($name)) {
                    $params_str[$name] = $placeholder;
                } else {
                    $params_num[] = $placeholder;
                }
            }

            if ($params_str) {
                $condition = strtr($condition, $params_str);
            }

            if ($params_num) {
                $condition = preg_replace_callback('/\?/', function ($matches) use (&$params_num) {
                    return count($params_num) > 0 ? array_shift($params_num) : $matches[0];
                }, $condition);
            }
            return $condition;
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
            $_op = strtoupper(trim($condition[0]));
            if (in_array($_op, ['AND', 'OR'])) {
                unset($condition[0]);
                $op = $_op;
            } else {
                return $this->buildOperator($condition, $container);
            }
        }
        $where = [];
        foreach ($condition as $key => $value) {
            if (is_string($key)) {
                if (is_array($value) || $value instanceof Query) {
                    $where[$key] = $this->buildOperator(['IN', $key, $value], $container);
                } elseif ($value === null) {
                    $where[$key] = $this->buildOperator(['NULL', $key, true], $container);
                } else {
                    $where[$key] = $this->buildOperator(['=', $key, $value], $container);
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
        $field = $this->db->schema()->quoteColumnName($field);
        $place = $this->buildPlaceholders($val, $container);
        switch (strtoupper(trim($operation))) {
            case 'IN':
                $place = is_array($place) ? ('(' . implode(', ', $place) . ')') : $place;
                return "$field IN $place";
            case 'NOT IN':
                $place = is_array($place) ? ('(' . implode(', ', $place) . ')') : $place;
                return "$field NOT IN $place";
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
                return $val == true ? "$field IS NULL" : "$field IS NOT NULL";
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
     * @param string|array|Query $params 绑定的参数
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
        } elseif ($params instanceof Query) {
            list($statement, $container) = $params->build($container);
            return "($statement)";
        } else {
            $placeholder = ':qp' . count($container);
            $container[$placeholder] = $params;
            return $placeholder;
        }
    }

    /**
     * 编译查询的字段
     * @param array $container 参数容器
     * @return string 返回编译后的查询字段
     */
    private function buildSelect(&$container)
    {
        $select = $this->_distinct ? 'SELECT DISTINCT ' : 'SELECT ';
        return $select . (empty($this->_select) ? '*' : implode(', ', $this->quoteColumnNames($this->_select, $container)));
    }

    /**
     * 编译查询的表
     * @param array $container 参数容器
     * @return string 返回编译后的表
     */
    private function buildFrom(&$container)
    {
        return empty($this->_from) ? '' : ('FROM ' . implode(', ', $this->quoteTableNames($this->_from, $container)));
    }

    /**
     * 编译表关联
     * @param array $container 参数容器
     * @return string 返回编译后关联语句
     */
    private function buildJoin(&$container)
    {
        $joins = [];
        foreach ($this->_join as $key => $join) {
            list($type, $table, $on, $params) = $join;
            $type = strtoupper(trim($type));
            $tables = $this->quoteTableNames((array)$table, $container);
            $table = reset($tables);
            $joins[$key] = "$type $table";
            if (!empty($on)) {
                $joins[$key] .= ' ON ' . $this->buildCondition($on, $params, $container);
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
        if ($this->_where) {
            $_where = '';
            foreach ($this->_where as $where) {
                if (count($where) === 2) {
                    list($condition, $params) = $where;
                    if ($condition) {
                        $_where .= $this->buildCondition($condition, $params, $container);
                    }
                } else {
                    list($op, $condition, $params) = $where;
                    if ($condition) {
                        $_where = "($_where) $op (" . $this->buildCondition($condition, $params, $container) . ")";
                    }
                }
            }
            return $_where ? "WHERE $_where" : '';
        } else {
            return '';
        }
    }

    /**
     * 编译分组查询
     * @return string 返回编译后的GROUP BY语句
     */
    private function buildGroupBy()
    {
        if ($this->_groupBy) {
            $_group = [];
            foreach ($this->_groupBy as $group) {
                $_group[] = $this->db->schema()->quoteColumnName($group);
            }
            return 'GROUP BY ' . implode(', ', $_group);
        } else {
            return '';
        }
    }

    /**
     * 编译筛选条件
     * @param array $container 参数容器
     * @return string 返回编译后的条件语句
     */
    private function buildHaving(&$container)
    {
        if ($this->_having) {
            $_having = '';
            foreach ($this->_having as $having) {
                if (count($having) === 2) {
                    list($condition, $params) = $having;
                    $_having .= $this->buildCondition($condition, $params, $container);
                } else {
                    list($op, $condition, $params) = $having;
                    $_having = "($_having) $op (" . $this->buildCondition($condition, $params, $container) . ")";
                }
            }
            return "HAVING $_having";
        } else {
            return '';
        }
    }

    /**
     * 编译排序方式
     * @return string 返回排序语句
     */
    private function buildOrderBy()
    {
        static $sortType = [SORT_ASC => 'ASC', SORT_DESC => 'DESC'];
        if ($this->_orderBy) {
            $sort = [];
            foreach ($this->_orderBy as $name => $type) {
                if (is_string($name)) {
                    $sort[] = $this->db->schema()->quoteColumnName($name) . ' ' . $sortType[$type];
                } else {
                    $sort[] = $this->db->schema()->quoteColumnName($type) . ' ASC';
                }
            }
            return 'ORDER BY ' . implode(', ', $sort);
        } else {
            return '';
        }
    }

    /**
     * 编译偏移和限制的条数
     * @return string 返回编译后的LIMIT语句
     */
    private function buildLimit()
    {
        if (isset($this->_limit[1]) && $this->_limit[1] !== null) {
            return 'LIMIT ' . $this->_limit[0] . ', ' . $this->_limit[1];
        } elseif (isset($this->limit[0])) {
            return 'LIMIT ' . $this->_limit[0];
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
        if ($this->_union) {
            $unions = [];
            foreach ($this->_union as $union) {
                /** @var Query $query */
                list($query, $all) = $union;
                list($statement, $container) = $query->build($container);
                $unions[] = ($all ? 'UNION ALL ' : 'UNION ') . "($statement)";
            }
            return implode(' ', $unions);
        } else {
            return '';
        }
    }

    /**
     * 组建SQL语句
     * @param array $params 传入参数
     * @return array 返回[$statement, $params]
     */
    public function build($params = [])
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
     * 预处理语句
     * @return Statement 返回语句对象
     */
    private function prepare()
    {
        list($sql, $params) = $this->build();
        return $this->db->prepare($sql, $params);
    }

    /**
     * 返回结果集中的一条记录
     * @param bool $obj 是否返回对象(默认返回关联数组)
     * @param string $class 要实例化的对象,不写默认为匿名对象
     * @return mixed 成功返回查询结果,失败返回false
     */
    public function one($obj = false, $class = null)
    {
        return $this->prepare()->one($obj, $class);
    }

    /**
     * 返回所有查询结果的数组
     * @param bool $obj 是否返回对象(默认返回关联数组)
     * @param string $class 要实例化的对象,不写默认为匿名对象
     * @return mixed 成功返回查询结果,失败返回false
     */
    public function all($obj = false, $class = null)
    {
        return $this->prepare()->all($obj, $class);
    }

    /**
     * 从结果集中的下一行返回单独的一个字段值,查询结果为标量
     * @param int $columnNumber 你想从行里取回的列的索引数字,以0开始
     * @return mixed 返回查询结果,查询结果为标量
     */
    public function scalar($columnNumber = 0)
    {
        return $this->prepare()->scalar($columnNumber);
    }

    /**
     * 从结果集中的取出第N列的值
     * @param int $columnNumber 你想从行里取回的列的索引数字,以0开始
     * @return mixed 返回查询结果
     */
    public function column($columnNumber = 0)
    {
        return $this->prepare()->column($columnNumber);
    }

    /**
     * 获取指定列的记录数
     * @param string $column 要统计的列名
     * @return bool|int 返回记录数
     */
    public function count($column = '*')
    {
        $column = $column == '*' ? $column : "[[$column]]";
        return $this->select(['count' => "COUNT($column)"])->scalar();
    }

    /**
     * 获取指定列所有值之和
     * @param string $column 要相加的列名
     * @return bool|float 返回相加后的值
     */
    public function sum($column)
    {
        return $this->select(['sum' => "SUM([[$column]])"])->scalar();
    }

    /**
     * 获取指定列的最大值
     * @param string $column 计算的列名
     * @return bool|float 最大值
     */
    public function max($column)
    {
        return $this->select(['max' => "MAX([[$column]])"])->scalar();
    }

    /**
     * 获取指定列的最小值
     * @param string $column 计算的列名
     * @return bool|float 最小值
     */
    public function min($column)
    {
        return $this->select(['min' => "MIN([[$column]])"])->scalar();
    }

    /**
     * 获取指定列的平均值
     * @param string $column 计算的列名
     * @return bool|float 平均值
     */
    public function avg($column)
    {
        return $this->select(['avg' => "AVG([[$column]])"])->scalar();
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
        $place = $this->buildPlaceholders(abs(intval($num)),$params);
        $table = $this->db->schema()->quoteTableName(reset($this->_from));
        $field = $this->db->schema()->quoteColumnName($field);
        $where = $this->buildWhere($params);
        $statement = "UPDATE $table SET $field=$field+{$place}" . ($where ? " $where" : '');
        return $this->db->prepare($statement, $params)->exec();
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
        $place = $this->buildPlaceholders(abs(intval($num)),$params);
        $table = $this->db->schema()->quoteTableName(reset($this->_from));
        $field = $this->db->schema()->quoteColumnName($field);
        $where = $this->buildWhere($params);
        $statement = "UPDATE $table SET $field=$field-{$place}" . ($where ? " $where" : '');
        return $this->db->prepare($statement, $params)->exec();
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
            $cols[] = $this->db->schema()->quoteColumnName($col);
            $placeholders[] = $this->buildPlaceholders($data, $params);
        }
        $table = $this->db->schema()->quoteTableName($table);
        $statement = ($replace ? 'REPLACE INTO' : 'INSERT INTO') . " $table (" . implode(', ', $cols) . ') VALUES (' . implode(', ', $placeholders) . ')';
        return $this->db->prepare($statement, $params)->exec();
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
        foreach ($columns as $key => $col) {
            $columns[$key] = $this->db->schema()->quoteColumnName($col);
        }
        foreach ($datas as $row) {
            $v[] = '(' . implode(', ', $this->buildPlaceholders($row, $params)) . ')';
        }
        $table = $this->db->schema()->quoteTableName($table);
        $statement = ($replace ? 'REPLACE INTO' : 'INSERT INTO') . " $table (" . implode(', ', $columns) . ') VALUES ' . implode(', ', $v);
        return $this->db->prepare($statement, $params)->exec();
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
        $placeholders = $p = [];
        foreach ($datas as $name => $data) {
            $placeholders[] = $this->db->schema()->quoteColumnName($name) . ' = ' . $this->buildPlaceholders($data, $p);
        }
        $table = $this->db->schema()->quoteTableName($table);
        $this->andWhere($condition, $params);
        $where = $this->buildWhere($p);
        $statement = "UPDATE $table SET " . implode(', ', $placeholders) . ($where ? " $where" : '');
        return $this->db->prepare($statement, $p)->exec();
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
        $p = [];
        $table = $this->db->schema()->quoteTableName($table);
        $this->andWhere($condition, $params);
        $where = $this->buildWhere($p);
        $statement = "DELETE FROM $table" . ($where ? " $where" : '');
        return $this->db->prepare($statement, $p)->exec();
    }
}
