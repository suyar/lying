<?php
/**
 * @author carolkey <me@suyaqi.cn>
 * @link https://github.com/carolkey/lying
 * @copyright 2017 Lying
 * @license MIT
 */

namespace lying\db;

/**
 * Class Schema
 * @package lying\db
 */
class Schema
{
    private $tableNames = [];

    private $tables = [];

    private $db;

    private $cache;

    public function __construct(Connection $db, $cache)
    {
        $this->db = $db;
        $this->cache = $cache;
    }

    /**
     * 获取数据库中所有的表名
     * @return array
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
     * @return TableSchema
     */
    public function getTableSchema($tableName)
    {
        if (!isset($this->tables[$tableName])) {
            $sql = "SHOW FULL COLUMNS FROM $tableName";
            $columnsInfo = $this->db->createQuery()->raw($sql)->fetchAll();
            $this->tables[$tableName] = new TableSchema($columnsInfo);
        }
        return $this->tables[$tableName];
    }

}
