<?php
/**
 * @author carolkey <me@suyaqi.cn>
 * @link https://github.com/carolkey/lying
 * @copyright 2017 Lying
 * @license MIT
 */

namespace lying\db;

use lying\service\Service;

/**
 * Class Connection
 * @package lying\db
 * @since 2.0
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
    private $dbh;

    /**
     * @var Schema 数据库表的结构
     */
    private $schema;

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
        if ($this->dbh === null) {
            $this->dbh = new \PDO($this->dsn, $this->user, $this->pass, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]);
        }
        return $this->dbh;
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
        } else {
            $this->_slave = $this;
        }
        return $this->_slave->pdo();
    }
    
    /**
     * 创建查询构造器
     * @return Query
     */
    public function createQuery()
    {
        return new Query($this);
    }

    /**
     * 获取数据表前缀
     * @return string
     */
    public function prefix()
    {
        return $this->prefix;
    }

    /**
     * 获取Schema实例
     * @return Schema
     */
    public function schema()
    {
        if ($this->schema == null) {
            $this->schema = new Schema($this, $this->dsn, $this->cache);
        }
        return $this->schema;
    }

    /**
     * 返回最后插入行的ID,或者是一个序列对象最后的值
     * @param string $name 应该返回ID的那个序列对象的名称
     * @return string 返回ID
     */
    public function lastInsertId($name = null)
    {
        return $this->masterPdo()->lastInsertId($name);
    }
    
    /**
     * 启动一个事务
     * @return boolean 成功时返回true,或者在失败时返回false
     */
    public function begin()
    {
        return $this->masterPdo()->beginTransaction();
    }
    
    /**
     * 提交一个事务
     * @return boolean 成功时返回true,或者在失败时返回false
     */
    public function commit()
    {
        return $this->masterPdo()->commit();
    }
    
    /**
     * 回滚一个事务
     * @return boolean 成功时返回true,或者在失败时返回false
     */
    public function rollBack()
    {
        return $this->masterPdo()->rollBack();
    }
}
