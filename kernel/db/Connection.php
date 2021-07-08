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
 * Class Connection
 * @package lying\db
 */
class Connection extends Service
{
    /**
     * @var string 数据源
     * @see http://php.net/manual/en/pdo.construct.php
     */
    protected $dsn;
    
    /**
     * @var string 数据库账号
     */
    protected $user;
    
    /**
     * @var string 数据库密码
     */
    protected $pass;

    /**
     * @var string 数据表前缀
     */
    protected $prefix = '';

    /**
     * @var array 额外的PDO选项
     */
    protected $options = [];

    /**
     * @var string 将要使用的CacheID
     */
    protected $cache;

    /**
     * @var array 主库列表
     */
    protected $master = [];

    /**
     * @var array 从库列表
     */
    protected $slave = [];
    
    /**
     * @var \PDO PDO实例
     */
    private $_dbh;

    /**
     * @var Schema 数据库表的结构
     */
    private $_schema;

    /**
     * @var Connection 主库实例
     */
    private $_master;

    /**
     * @var Connection 从库实例
     */
    private $_slave;
    
    /**
     * 获取数据库实例
     * @return \PDO
     */
    protected function pdo()
    {
        if ($this->_dbh === null) {
            $this->_dbh = new \PDO($this->dsn, $this->user, $this->pass, $this->options);
            $this->_dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->_dbh->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
            $this->_dbh->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        }
        return $this->_dbh;
    }

    /**
     * 获取主库PDO实例
     * @return \PDO
     */
    public function masterPdo()
    {
        if ($this->_master instanceof self) {
            return $this->_master->pdo();
        } elseif ($this->master) {
            shuffle($this->master);
            $this->_master = new self(array_shift($this->master));
            $this->_master->options = $this->options;
        } else {
            $this->_master = $this;
        }
        return $this->_master->pdo();
    }

    /**
     * 获取从库PDO实例
     * @return \PDO
     */
    public function slavePdo()
    {
        if ($this->_slave instanceof self) {
            return $this->_slave->pdo();
        } elseif ($this->slave) {
            shuffle($this->slave);
            $this->_slave = new self(array_shift($this->slave));
            $this->_slave->options = $this->options;
        } else {
            $this->_slave = $this;
        }
        return $this->_slave->pdo();
    }

    /**
     * 启动一个事务
     * @return bool 成功时返回true,或者在失败时返回false
     */
    public function begin()
    {
        return $this->masterPdo()->beginTransaction();
    }

    /**
     * 提交一个事务
     * @return bool 成功时返回true,或者在失败时返回false
     */
    public function commit()
    {
        return $this->masterPdo()->commit();
    }

    /**
     * 回滚一个事务
     * @return bool 成功时返回true,或者在失败时返回false
     */
    public function rollBack()
    {
        return $this->masterPdo()->rollBack();
    }

    /**
     * 返回最后插入行的自增ID
     * @return string 返回ID
     */
    public function lastInsertId()
    {
        return $this->masterPdo()->lastInsertId();
    }

    /**
     * 预处理语句
     * @param string $sql 要执行的SQL语句
     * @param array $params SQL语句绑定的参数
     * @return Statement 返回处理语句对象
     */
    public function prepare($sql, array $params = [])
    {
        return new Statement([
            'db' => $this,
            'sql' => $sql,
            'params' => $params,
        ]);
    }

    /**
     * 获取Schema实例
     * @return Schema
     */
    public function schema()
    {
        if ($this->_schema == null) {
            $this->_schema = new Schema([
                'db' => $this,
                'prefix' => $this->prefix,
                'cache' => $this->cache,
                'cacheKey' => $this->dsn,
            ]);
        }
        return $this->_schema;
    }
    
    /**
     * 创建查询构造器
     * @return Query
     */
    public function query()
    {
        return new Query(['db'=>$this]);
    }
}
