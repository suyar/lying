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
class Query extends Service
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
     * @var Query[] 联合查询的Query
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

    private function buildSelect(&$container)
    {
        $select = $this->_distinct ? 'SELECT DISTINCT ' : 'SELECT ';
        if (empty($this->_select)) {
            $select .= '*';
        } else {
            $cols = [];
            foreach ($this->_select as $name => $value) {
                if ($value instanceof Query) {
                    list($sql, $container) = $value->build($container);
                    $cols[] = "($sql) AS " . $this->db->schema()->quoteColumnName($name);
                } elseif (is_string($name)) {
                    //$cols[] = $this->db->schema()->quoteColumnName()
                }
            }
        }
        return $select;
    }

    public function build(&$params)
    {

    }
}
