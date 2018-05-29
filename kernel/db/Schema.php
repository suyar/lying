<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\db;

use lying\cache\Cache;
use lying\service\Service;

/**
 * Class Schema
 * @package lying\db
 */
class Schema extends Service
{
    /**
     * @var Connection 数据库连接实例
     */
    protected $db;

    /**
     * @var string 表前缀
     */
    protected $prefix = '';

    /**
     * @var Cache 缓存服务
     */
    protected $cache;

    /**
     * @var string 缓存键名
     */
    protected $cacheKey;

    /**
     * @var array 所有的表名
     */
    private $_tableNames = [];

    /**
     * @var TableSchema[] 所有的表结构
     */
    private $_tables = [];

    /**
     * @inheritdoc
     */
    protected function init()
    {
        parent::init();
        $this->cache = $this->cache ? \Lying::$maker->cache($this->cache) : null;
        if ($this->cache) {
            if ($this->cache->exists($this->cacheKey)) {
                $this->_tables = $this->cache->get($this->cacheKey);
            }
        }
    }

    /**
     * 获取数据库中所有的表名
     * @return array 返回数据库中所有的表名
     */
    public function getTableNames()
    {
        if (empty($this->_tableNames)) {
            $this->_tableNames = $this->db->prepare('SHOW TABLES')->column();
        }
        return $this->_tableNames;
    }

    /**
     * 获取表结构
     * @param string $tableName 完整的表名
     * @return TableSchema 获取表信息
     */
    public function getTableSchema($tableName)
    {
        $tableName = $this->quoteTableName($tableName);
        if (!isset($this->_tables[$tableName])) {
            $columnsInfo = $this->db->prepare("DESC $tableName")->all();
            $this->_tables[$tableName] = new TableSchema($tableName, $columnsInfo);
            $this->cache && $this->cache->set($this->cacheKey, $this->_tables);
        }
        return $this->_tables[$tableName];
    }

    /**
     * 清除表结构缓存(当开启了缓存时,更改表结构后调用)
     * @return bool 成功返回true,失败返回false
     */
    public function clearSchemaCache()
    {
        return $this->cache && $this->cache->del($this->cacheKey);
    }

    /**
     * 简单的给字段名加上反引号
     * @param string $name 字段
     * @return string 字段
     */
    public function quoteSimpleColumnName($name)
    {
        return $name === '*' || strpos($name, '`') === false ? "`$name`" : $name;
    }

    /**
     * 简单的给表名加上反引号
     * @param string $name 表名
     * @return string 返回处理后的表名
     */
    public function quoteSimpleTableName($name)
    {
        return strpos($name, '`') === false ? "`$name`" : $name;
    }

    /**
     * 处理字段名
     * @param string $name 要处理的字段名(如果字段名包含前缀,也会被一同处理;如果字段名有'('或者'[['或者'{{'将不被处理)
     * @return string 返回处理后的字段名
     */
    public function quoteColumnName($name)
    {
        if (strpos($name, '(') !== false || strpos($name, '[[') !== false) {
            return $name;
        }
        if (($pos = strrpos($name, '.')) !== false) {
            $prefix = $this->quoteTableName(substr($name, 0, $pos)) . '.';
            $name = substr($name, $pos + 1);
        } else {
            $prefix = '';
        }
        if (strpos($name, '{{') !== false) {
            return $name;
        }

        return $prefix . $this->quoteSimpleColumnName($name);
    }

    /**
     * 处理表名
     * @param string $name 要处理的表名(如果表名包含前缀,也会被一同处理;如果表名有'('或者'{{'将不被处理)
     * @return string 返回被处理后的表名
     */
    public function quoteTableName($name)
    {
        if (strpos($name, '(') !== false || strpos($name, '{{') !== false) {
            return $name;
        }
        if (strpos($name, '.') === false) {
            return $this->quoteSimpleTableName($name);
        }
        $parts = explode('.', $name);
        foreach ($parts as $i => $part) {
            $parts[$i] = $this->quoteSimpleTableName($part);
        }

        return implode('.', $parts);
    }

    /**
     * 处理SQL语句的表名和字段名
     * @param string $sql
     * @return string
     */
    public function quoteSql($sql)
    {
        return preg_replace_callback('/(\\{\\{(%?[\w\-\. ]+%?)\\}\\}|\\[\\[([\w\-\. ]+)\\]\\])/', function ($matches) {
            return isset($matches[3]) ? $this->quoteColumnName($matches[3]) : str_replace('%', $this->prefix, $this->quoteTableName($matches[2]));
        }, $sql);
    }

    /**
     * 获取php数据类型对应的PDO数据类型
     * @param mixed $data
     * @return int PDO数据类型
     */
    public function getPdoType($data)
    {
        static $typeMap = [
            'boolean' => \PDO::PARAM_BOOL,
            'integer' => \PDO::PARAM_INT,
            'string' => \PDO::PARAM_STR,
            'resource' => \PDO::PARAM_LOB,
            'NULL' => \PDO::PARAM_NULL,
        ];
        $type = gettype($data);
        return isset($typeMap[$type]) ? $typeMap[$type] : \PDO::PARAM_STR;
    }

    /**
     * 是否是查询语句
     * @param string $statement 要检测的语句
     * @return bool 返回是否是查询语句
     */
    public function isReadStatement($statement)
    {
        $pattern = '/^\s*(SELECT|SHOW|DESCRIBE|DESC|EXPLAIN)\b/i';
        return preg_match($pattern, $statement) > 0;
    }

    /**
     * 处理值
     * @param string $str 要被处理的值
     * @return string 返回处理后的值
     */
    public function quoteValue($str)
    {
        return is_string($str) ? $this->db->slavePdo()->quote($str) : $str;
    }
}
