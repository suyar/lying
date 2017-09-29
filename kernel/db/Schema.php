<?php
/**
 * @author carolkey <me@suyaqi.cn>
 * @link https://github.com/carolkey/lying
 * @copyright 2017 Lying
 * @license MIT
 */

namespace lying\db;

use lying\cache\Cache;

/**
 * Class Schema
 * @package lying\db
 */
class Schema
{
    /**
     * @var array 所有的表名
     */
    private $tableNames = [];

    /**
     * @var TableSchema[] 所有的表结构
     */
    private $tables = [];

    /**
     * @var Connection 数据库连接实例
     */
    private $db;

    /**
     * @var Cache 缓存服务
     */
    private $cache;

    /**
     * @var string 缓存键名
     */
    private $cacheKey;

    /**
     * Schema constructor.
     * @param Connection $db 数据库连接实例
     * @param string $dsn 实例唯一标识
     * @param string $cache 使用的缓存服务名
     */
    public function __construct(Connection $db, $dsn, $cache)
    {
        $this->db = $db;
        $this->cacheKey = $dsn;
        $this->cache = $cache ? \Lying::$maker->cache($cache) : null;
        if ($cache) {
            $this->cache = \Lying::$maker->cache($cache);
            if ($this->cache->exist($this->cacheKey)) {
                $this->tables = $this->cache->get($this->cacheKey);
            }
        }
    }

    /**
     * 获取数据库中所有的表名
     * @return array 返回数据库中所有的表名
     */
    public function getTableNames()
    {
        if (empty($this->tableNames)) {
            $this->tableNames = $this->db->createQuery()->raw('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);
        }
        return $this->tableNames;
    }

    /**
     * 获取表结构
     * @param string $tableName 完整的表名
     * @return TableSchema 获取表信息
     */
    public function getTableSchema($tableName)
    {
        if (!isset($this->tables[$tableName])) {
            $query = $this->db->createQuery();
            $columnsInfo = $query->query("DESC $tableName");
            $this->tables[$tableName] = new TableSchema($tableName, $columnsInfo);
            if ($this->cache instanceof Cache) {
                $this->cache->set($this->cacheKey, $this->tables);
            }
        }
        return $this->tables[$tableName];
    }

    /**
     * 清除表结构缓存(当开启了缓存时,更改表结构后调用)
     * @return boolean 总是返回true
     */
    public function clearCache()
    {
        if ($this->cache instanceof Cache) {
            $this->cache->del($this->cacheKey);
        }
        return true;
    }

    /**
     * 获取数据表创建语句
     * @param string $tableName 表名
     * @return string SQL语句
     */
    public function getCreateTableSql($tableName)
    {
        $row = $this->db->createQuery()->raw("SHOW CREATE TABLE $tableName")->fetch();
        return $row['Create Table'];
    }
}
