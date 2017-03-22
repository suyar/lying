<?php
namespace lying\db;

/**
 * 查询构造器基类，用来组建SQL语句
 *
 * @author carolkey <me@suyaqi.cn>
 * @since 2.0
 * @link https://carolkey.github.io/
 * @license MIT
 */
class QueryBuilder
{
    /**
     * 简单地给表名、字段加上"`"
     * ```
     * 'name' => '`name`'
     * ```
     * @param string $name 字段名
     * @return string 字段名
     */
    protected function quoteSimple($name)
    {
        return strpos($name, '`') !== false || $name === '*' ? $name : "`$name`";
    }
    
    /**
     * 给复杂的表名、字段加上"`"
     * ```
     * 'lying.name' => '`lying`.`name`'
     * ```
     * 注意:'count(id)'并不会转义成'count(`id`)'，而还是原来的'count(id)'
     * @param string $name 字段名
     * @return string 字段名
     */
    protected function quoteColumn($name)
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
     * 给复杂的表名，字段加上"`"，并且编译别名和子查询，请以数组形式传入字段名和表名
     * ```
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
    protected function quoteColumns($columns, &$container)
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
    protected function buildCondition($condition, $params, &$container)
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
    protected function buildArrayCondition($condition, &$container)
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
    protected function buildOperator($condition, &$container)
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
    protected function buildPlaceholders($params, &$container)
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
    protected function buildSelect(&$container)
    {
        $columns = $this->quoteColumns($this->select, $container);
        return ($this->distinct ? 'SELECT DISTINCT ' : 'SELECT ') . (empty($columns) ? '*' : implode(', ', $columns));
    }
    
    /**
     * 编译查询的表
     * @param array $container 参数容器
     * @return string 返回编译后的表
     */
    protected function buildFrom(&$container)
    {
        $tables = $this->quoteColumns($this->from, $container);
        return empty($tables) ? '' : 'FROM ' . implode(', ', $tables);
    }
    
    /**
     * 编译表关联
     * @param array $container 参数容器
     * @return string 返回编译后关联语句
     */
    protected function buildJoin(&$container)
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
    protected function buildWhere(&$container)
    {
        list($condition, $params) = $this->where;
        $where = $this->buildCondition($condition, $params, $container);
        return empty($where) ? '' : "WHERE $where";
    }
    
    /**
     * 编译分组查询
     * @return string 返回编译后的GROUP BY语句
     */
    protected function buildGroupBy()
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
    protected function buildHaving(&$container)
    {
        list($condition, $params) = $this->having;
        $having = $this->buildCondition($condition, $params, $container);
        return empty($where) ? '' : "HAVING $having";
    }
    
    /**
     * 编译排序方式
     * @return string 返回排序语句
     */
    protected function buildOrderBy()
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
    protected function buildLimit()
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
    protected function buildUnion(&$container)
    {
        foreach ($this->union as $union) {
            list($statement, $container) = $union[0]->build($container);
            $unions[] = ($union[1] ? 'UNION ALL ' : 'UNION ') . "($statement)";
        }
        return isset($unions) ? implode(' ', $unions) : '';
    }
    
    /**
     * 组建sql语句
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
}
