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
 * Class Statement
 * @package lying\db
 */
class Statement extends Service
{
    /**
     * SQL执行之前的事件
     */
    const EVENT_BEFORE_EXECUTE = 'beforeExecute';

    /**
     * SQL执行之后的事件
     */
    const EVENT_AFTER_EXECUTE = 'afterExecute';

    /**
     * @var Connection 使用的数据库链接
     */
    protected $db;

    /**
     * @var \PDOStatement 当前语句关联的\PDOStatement对象
     */
    private $_statement;

    /**
     * @var array 当前语句绑定的参数
     */
    protected $params = [];

    /**
     * @var string 当前处理的sql语句
     */
    protected $sql;

    /**
     * @var bool 是否强制使用主库
     */
    private $_useMaster = false;

    /**
     * @var array 还没有绑定的参数
     */
    private $_toBind = [];

    /**
     * 初始化的时候处理SQL语句
     */
    protected function init()
    {
        parent::init();
        $this->sql = $this->db->schema()->quoteSql($this->sql);
        foreach ($this->params as $name => $value) {
            $this->bindValue($name, $value);
        }
    }

    /**
     * 使用主库
     * @param bool $useMaster 是否使用主库,默认true
     * @return $this
     */
    public function useMaster($useMaster = true)
    {
        $this->_useMaster = $useMaster;
        return $this;
    }

    /**
     * 预处理语句
     * @param bool $isRead 是否为从库
     */
    private function prepare($isRead = null)
    {
        if ($this->_statement === null) {
            if (!$this->_useMaster && ($isRead || $isRead == null && $this->db->schema()->isReadStatement($this->sql))) {
                $this->_statement = $this->db->slavePdo()->prepare($this->sql);
            } else {
                $this->_statement = $this->db->masterPdo()->prepare($this->sql);
            }
        }
        foreach ($this->_toBind as $name => $value) {
            $this->_statement->bindValue($name, $value[0], $value[1]);
        }
        $this->_toBind = [];
    }

    /**
     * 获取SQL语句,这个语句只是参数替换的,不一定是PDO执行的语句
     * @param bool $raw 是否获取不替换参数的语句,默认否
     * @param array $params 引用返回绑定的参数
     * @return string 返回SQL语句
     */
    public function getSql($raw = false, &$params = [])
    {
        $sql = $this->sql;
        $params = $this->params;
        if (!$raw && $this->params) {
            $params_str = $params_num = [];
            foreach ($this->params as $name => $value) {
                if (is_string($value)) {
                    $value = $this->db->schema()->quoteValue($value);
                } elseif (is_bool($value)) {
                    $value = ($value ? 'TRUE' : 'FALSE');
                } elseif ($value === null) {
                    $value = 'NULL';
                } else {
                    $value = (string)$value;
                }

                if (is_string($name)) {
                    if (strncmp(':', $name, 1)) {
                        $name = ":$name";
                    }
                    $params_str[$name] = $value;
                } else {
                    $params_num[$name] = $value;
                }
            }

            //PDO中,一条语句不能同时出现命名占位符和问号占位符
            if ($params_str) {
                $sql = strtr($sql, $params_str);
            } elseif ($params_num) {
                $sql = preg_replace_callback('/\?/', function ($matches) use (&$params_num, &$i) {
                    $i++;
                    return isset($params_num[$i]) ? $params_num[$i] : $matches[0];
                }, $sql);
            }
        }

        return $sql;
    }

    /**
     * 绑定值到语句
     * @param string|int $name 参数名
     * @param mixed $value 参数值
     * @param int $dataType PDO数据类型,不传的话自动获取
     * @return $this
     */
    public function bindValue($name, $value, $dataType = null)
    {
        if ($dataType === null) {
            $dataType = $this->db->schema()->getPdoType($value);
        }
        $this->_toBind[$name] = [$value, $dataType];
        $this->params[$name] = $value;
        return $this;
    }

    /**
     * 绑定变量到语句;注意:使用变量绑定后已经生成预处理语句,所以再使用useMaster()已经不再有效果
     * @param string|int $name 参数名
     * @param mixed $value 变量
     * @param int $dataType PDO数据类型
     * @param int $length 数据类型长度
     * @param mixed $driverOptions 特殊的参数
     * @return $this
     */
    public function bindParam($name, &$value, $dataType = null, $length = null, $driverOptions = null)
    {
        $this->prepare();
        if ($dataType === null) {
            $dataType = $this->db->schema()->getPdoType($value);
        }
        if ($length === null) {
            $this->_statement->bindParam($name, $value, $dataType);
        } elseif ($driverOptions === null) {
            $this->_statement->bindParam($name, $value, $dataType, $length);
        } else {
            $this->_statement->bindParam($name, $value, $dataType, $length, $driverOptions);
        }
        $this->params[$name] = &$value;
        return $this;
    }

    /**
     * 执行SQL语句
     * @param bool $isRead 是否为从库
     */
    private function execute($isRead = null)
    {
        $this->prepare($isRead);
        $this->_statement->execute();
    }

    /**
     * 执行SQL语句
     * @return int 返回受影响的行数
     */
    public function exec()
    {
        $this->execute(false);
        return $this->_statement->rowCount();
    }

    /**
     * 返回获取到的数据
     * @param string $method 要执行的方法
     * @param array $args 额外的执行参数
     * @return mixed 返回执行结果
     */
    private function fetch($method, $args = [])
    {
        $this->execute(true);
        $res = call_user_func_array([$this->_statement, $method], $args);
        $this->_statement->closeCursor();
        return $res;
    }

    /**
     * 返回结果集中的一条记录
     * @param bool $obj 是否返回对象(默认否返回关联数组)
     * @param string $class 要实例化的对象,不写默认为\stdClass
     * @return mixed 成功返回查询结果,失败返回false
     */
    public function one($obj = false, $class = null)
    {
        return $this->fetch($obj ? 'fetchObject' : 'fetch', $class === null ? [] : [$class]);
    }

    /**
     * 返回所有查询结果的数组
     * @param bool $obj 是否返回对象(默认否返回关联数组)
     * @param string $class 要实例化的对象,不写默认为\stdClass
     * @return mixed 成功返回查询结果,失败返回false
     */
    public function all($obj = false, $class = null)
    {
        return $this->fetch('fetchAll', $obj ? ($class === null ? [\PDO::FETCH_OBJ] : [\PDO::FETCH_CLASS, $class]) : []);
    }

    /**
     * 从结果集中的下一行返回单独的一个字段值,查询结果为标量
     * @param int $columnNumber 你想从行里取回的列的索引数字,以0开始
     * @return mixed 返回查询结果,查询结果为标量
     */
    public function scalar($columnNumber = 0)
    {
        return $this->fetch('fetchColumn', [$columnNumber]);
    }

    /**
     * 从结果集中的取出第N列的值
     * @param int $columnNumber 你想从行里取回的列的索引数字,以0开始
     * @return mixed 返回查询结果
     */
    public function column($columnNumber = 0)
    {
        return $this->fetch('fetchAll', [\PDO::FETCH_COLUMN, $columnNumber]);
    }
}
