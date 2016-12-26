<?php
namespace lying\db;

use lying\service\Service;

class Connection extends Service
{
    protected $dsn;
    
    protected $user;
    
    protected $pass;
    
    /**
     * @var \PDO PDO实例
     */
    private $dbh;
    
    /**
     * @var Schema[] 表结构
     */
    private $tableSchema = [];
    
    /**
     * 获取数据库实例
     * @return \PDO
     */
    public function PDO()
    {
        if ($this->dbh instanceof \PDO) {
            return $this->dbh;
        } else {
            $this->dbh = new \PDO($this->dsn, $this->user, $this->pass, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]);
            return $this->dbh;
        }
    }
    
    /**
     * 获取表的结构
     * @param string $table
     * @return multitype:\lying\db\Schema
     */
    public function getSchema($table)
    {
        if (isset($this->tableSchema[$table])) {
            return $this->tableSchema[$table];
        }
        $fieldSchema = $this->PDO()->query("DESC $table")->fetchAll();
        $this->tableSchema[$table] = new Schema($fieldSchema);
        return $this->tableSchema[$table];
    }
    
    /**
     * 创建查询构造
     * @return QueryBuilder
     */
    public function createQuery()
    {
        return new QueryBuilder($this);
    }
    
    /**
     * 给字符串加引号
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