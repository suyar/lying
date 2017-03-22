<?php
namespace lying\db;

use lying\service\Service;

/**
 * 数据库连接实例
 *
 * @author carolkey <me@suyaqi.cn>
 * @since 2.0
 * @link https://carolkey.github.io/
 * @license MIT
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
     * @var \PDO PDO实例
     */
    private $dbh;
    
    /**
     * 获取数据库实例
     * @return \PDO
     */
    public function pdo()
    {
        if (!($this->dbh instanceof \PDO)) {
            $this->dbh = new \PDO($this->dsn, $this->user, $this->pass, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]);
        }
        return $this->dbh;
    }
    
    /**
     * 预处理sql语句
     * @param string $statement 准备要执行的语句
     * @return \PDOStatement
     */
    public function prepare($statement)
    {
        return $this->pdo()->prepare($statement);
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
     * 返回最后插入行的ID，或者是一个序列对象最后的值
     * @param string $name 应该返回ID的那个序列对象的名称
     * @return string 返回ID
     */
    public function lastInsertId($name = null)
    {
        return $this->pdo()->lastInsertId($name);
    }
    
    /**
     * 启动一个事务
     * @return boolean 成功时返回true，或者在失败时返回false
     */
    public function beginTransaction()
    {
        return $this->pdo()->beginTransaction();
    }
    
    /**
     * 提交一个事务
     * @return boolean 成功时返回true，或者在失败时返回false
     */
    public function commit()
    {
        return $this->pdo()->commit();
    }
    
    /**
     * 回滚一个事务
     * @return boolean 成功时返回true，或者在失败时返回false
     */
    public function rollBack()
    {
        return $this->pdo()->rollBack();
    }
}