<?php
namespace lying\db;

/**
 * 查询构造器类
 *
 * @author carolkey <me@suyaqi.cn>
 * @since 2.0
 * @link https://carolkey.github.io/
 * @license MIT
 */
class Query extends QueryBuilder
{
    /**
     * @var Connection 数据库连接实例
     */
    protected $connection;
    
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
     * @var \PDOStatement PDO语句句柄
     */
    protected $sth;
    
    /**
     * 初始化Query查询
     * @param Connection $connection 数据库连接实例
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * 设置要查询的字段
     * @param string|array $columns 要查询的字段，当没有设置要查询的字段的时候，默认为'*'
     * select('id, lying.sex, count(id) as count')
     * select(['id', 'lying.sex', 'count'=>'count(id)', 'q'=>$query])
     * 其中$query为Query实例，必须指定子查询的别名，只有$columns为数组的时候才支持子查询
     * 注意：当你使用到包含逗号的数据库表达式的时候，你必须使用数组的格式，以避免自动的错误的引号添加
     * select(["CONCAT(first_name, ' ', last_name) AS full_name", 'email']);
     * @return $this
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
     * @return $this
     */
    public function distinct()
    {
        $this->distinct = true;
        return $this;
    }
    
    /**
     * 设置要查询的表
     * @param string|array $tables 要查询的表
     * from('user, lying.admin as ad')
     * from(['user', 'ad'=>'lying.admin', 'q'=>$query])
     * 其中$query为Query实例，必须指定子查询的别名，只有$tables为数组的时候才支持子查询
     * @return $this
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
     * 设置表连接，可多次调用
     * @param string $type 连接类型,可以为'left join'，'right join'，'inner join'
     * @param string|array $table 要连接的表，子查询用数组形式表示，键为别名，值为Query实例
     * @param string|array $on 条件，如果要使用'字段1 = 字段2'的形式，请用字符串带入，用数组的话'字段2'将被解析为绑定参数
     * @param array $params 绑定的参数，应为key=>value形式
     * @return $this
     */
    public function join($type, $table, $on = null, $params = [])
    {
        $this->join[] = [$type, $table, $on, $params];
        return $this;
    }
    
    /**
     * 设置查询条件
     * @param string|array $condition 要查询的条件
     * 如果要使用'字段1 = 字段2'的形式，请用字符串带入，用数组的话'字段2'将被解析为绑定参数
     * where("user.id = admin.id and name = :name", [':name'=>'lying']);
     * where(['id'=>1, 'name'=>'lying']);
     * where(['id'=>[1, 2, 3]], ['or', 'name'=>'lying', 'sex'=>1]);
     * @param array $params 当$condition为字符串时，绑定参数的数组
     * @return $this
     */
    public function where($condition, $params = [])
    {
        $this->where = [$condition, $params];
        return $this;
    }
    
    /**
     * 设置分组查询
     * @param string|array 要分组的字段
     * groupBy('id, sex');
     * groupBy(['id', 'sex']);
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
     * @param string|array $columns 要排序的字段和排序方式
     * orderBy('id, name desc');
     * orderBy(['id'=>SORT_DESC, 'name']);
     * @return $this
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
     * @param integer $offset 偏移的条数，如果只提供此参数，则等同于limit(0, $offset)
     * limit(10);
     * limit(5, 20);
     * @param integer $limit 限制的条数
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
     * @param boolean $all 是否使用UNION ALL,默认false
     * @return $this
     */
    public function union(Query $query, $all = false)
    {
        $this->union[] = [$query, $all];
        return $this;
    }
    
    /**
     * 执行一条sql语句
     * @param string $statement sql语句
     * @param array $params 绑定的参数
     * @return boolean 成功返回true，失败返回false
     */
    public function execute($statement, $params = [])
    {
        $this->sth = $this->connection->prepare($statement);
        return $this->sth->execute($params);
    }
    
    /**
     * 执行原生SQL，返回的是语句执行后的PDOStatement对象，直接调用fetch，fetchAll，rowCount等函数即可
     * db()->createQuery()->RawSql('select * from user')->fetchAll(\PDO::FETCH_ASSOC);
     * @param string $statement sql语句
     * @param array $params 绑定的参数
     * @return \PDOStatement|boolean 失败返回false
     */
    public function RawSql($statement, $params = [])
    {
        $this->sth = $this->connection->prepare($statement);
        return $this->execute($statement, $params) ? $this->sth : false;
    }
    
    /**
     * 查询数据
     * @param string $method 查询的方法
     * @param array $args 要带入的参数列表
     * @return mixed 查询的数据，失败返回false
     */
    protected function fetch($method, $args = [])
    {
        list($statement, $params) = $this->build();
        $res = $this->execute($statement, $params) ? call_user_func_array([$this->sth, $method], $args) : false;
        $this->sth->closeCursor();
        return $res;
    }
    
    /**
     * 返回结果集中的一条记录
     * @param boolean $obj 是否返回对象(默认返回关联数组)
     * @param string $class 要实例化的对象，不写默认为匿名对象
     * @return mixed|\lying\db\ActiveRecord
     */
    public function one($obj = false, $class = null)
    {
        return $this->fetch($obj ? 'fetchObject' : 'fetch', $class === null ? [] : [$class]);
    }
    
    /**
     * 返回所有查询结果的数组
     * @param boolean $obj 是否返回对象(默认返回关联数组)
     * @param string $class 要实例化的对象，不写默认为匿名对象
     * @return mixed|\lying\db\ActiveRecord[]
     */
    public function all($obj = false, $class = null)
    {
        return $this->fetch('fetchAll', $obj ? ($class === null ? [\PDO::FETCH_OBJ] : [\PDO::FETCH_CLASS, $class]) : []);
    }
    
    /**
     * 从结果集中的下一行返回单独的一列，查询结果为标量
     * @return mixed
     */
    public function column()
    {
        return $this->fetch('fetchColumn');
    }
    
    /**
     * 插入一条数据
     * @param string $table 要插入的表名
     * @param array $datas 要插入的数据，(name => value)形式的数组
     * 当然value可以是子查询,Query的实例，但是查询的表不能和插入的表是同一个
     * @return integer|boolean 返回受影响的行数，有可能是0行，失败返回false
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
        return $this->execute($statement, $params) ? $this->sth->rowCount() : false;
    }
    
    /**
     * 批量插入数据
     * @param string $table 要插入的表名
     * @param array $columns 要插入的字段名
     * @param array $datas 要插入的数据，应为一个二维数组
     * batchInsert('user', ['name', 'sex'], [
     *     ['user1', 1],
     *     ['user2', 0],
     *     ['user3', 1],
     * ])
     * @return integer|boolean 返回受影响的行数,有可能是0行,失败返回false
     */
    public function batchInsert($table, $columns, $datas)
    {
        $params = [];
        foreach ($datas as $row) {
            $v[] = '(' . implode(', ', $this->buildPlaceholders($row, $params)) . ')';
        }
        $table = $this->quoteColumn($table);
        $columns = array_map(function($col) {
            return $this->quoteColumn($col);
        }, $columns);
        $statement = "INSERT INTO $table (" . implode(', ', $columns) . ') VALUES ' . implode(', ', $v);
        return $this->execute($statement, $params) ? $this->sth->rowCount() : false;
    }
    
    /**
     * 更新数据
     * @param string $table 要更新的表
     * @param array $datas 要更新的数据，(name => value)形式的数组
     * 当然value可以是子查询，Query的实例，但是查询的表不能和更新的表是同一个
     * @param string|array $condition 更新的条件，参见where()
     * @param array $params 条件的参数，参见where()
     * @return integer|boolean 返回受影响的行数，有可能是0行，失败返回false
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
        return $this->execute($statement, $p) ? $this->sth->rowCount() : false;
    }
    
    /**
     * 删除数据
     * @param string $table 要删除的表
     * @param string|array $condition 删除的条件，参见where()
     * @param array $params 条件的参数，参见where()
     * @return integer|boolean 返回受影响的行数，有可能是0行，失败返回false
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
