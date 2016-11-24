<?php
namespace lying\db;

use lying\service\Service;

class Connection extends Service
{
    protected $dsn;
    
    protected $user;
    
    protected $pass;
    
    /**
     * PDO实例
     * @var \PDO
     */
    private $dbh;
    
    
    protected function init()
    {
        
    }
    
    /**
     * 获取数据库实例
     * @return \PDO
     */
    public function PDO()
    {
        if ($this->dbh instanceof \PDO) {
            return $this->dbh;
        } else {
            $this->dbh = new \PDO($this->dsn, $this->user, $this->pass);
            return $this->dbh;
        }
    }
    
    
    public function getSchema($table)
    {
        $sth = $this->PDO()->prepare("DESC $table");
        $sth->execute();
        var_dump($sth->fetchAll(\PDO::FETCH_ASSOC));
    }
    
    
    
    
    /**
     * 转义字符串
     * @param string $value
     * @return boolean|string
     */
    public function quoteValue($value)
    {
        if (!is_string($value)) {
            return $value;
        }
        return $this->PDO()->quote($value);
    }
    
    /**
     * 开始事务
     * @return boolean
     */
    public function beginTransaction()
    {
        return $this->PDO()->beginTransaction();
    }
    
    /**
     * 提交事务
     * @return boolean
     */
    public function commit()
    {
        return $this->PDO()->commit();
    }
    
    /**
     * 回滚事务
     * @return boolean
     */
    public function rollBack()
    {
        return $this->PDO()->rollBack();
    }
    
    
}