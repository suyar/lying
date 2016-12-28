<?php
namespace lying\db;

use lying\service\Service;

class Connection extends Service
{
    /**
     * 数据源
     * @var string
     */
    protected $dsn;
    
    /**
     * 数据库账号
     * @var string
     */
    protected $user;
    
    /**
     * 数据库密码
     * @var string
     */
    protected $pass;
    
    /**
     * PDO实例
     * @var \PDO
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
     * 获取表的结构
     * @param string $table
     * @return Schema
     */
    public function tableSchema($table)
    {
        if (isset($this->tableSchema[$table])) {
            return $this->tableSchema[$table];
        }
        $fieldSchema = $this->pdo()->query("DESC `$table`")->fetchAll();
        $this->tableSchema[$table] = new Schema($fieldSchema);
        return $this->tableSchema[$table];
    }
    
    /**
     * 预处理sql语句
     * @param string $statement
     * @return PDOStatement
     */
    public function prepare($statement)
    {
        return $this->pdo()->prepare($statement);
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
        return $this->pdo()->quote($value);
    }
    
    /**
     * 开始事务
     * @return boolean
     */
    public function beginTransaction()
    {
        return $this->pdo()->beginTransaction();
    }
    
    /**
     * 提交事务
     * @return boolean
     */
    public function commit()
    {
        return $this->pdo()->commit();
    }
    
    /**
     * 回滚事务
     * @return boolean
     */
    public function rollBack()
    {
        return $this->pdo()->rollBack();
    }
}