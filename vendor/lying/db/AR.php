<?php
namespace lying\db;

use lying\service\Service;

class AR extends Service
{
    /**
     * @var array 数据
     */
    private $attr = [];
    
    /**
     * @var array 旧数据
     */
    private $oldAttr;
    
    /**
     * @var array 存表结构
     */
    private static $schema = [];

    /**
     * 返回表的结构
     */
    private static function schema()
    {
        $table = self::table();
        if (!isset(self::$schema[$table])) {
            $struct = self::db()->pdo()->query("DESC " . self::table())->fetchAll();
            foreach ($struct as $column) {
                self::$schema[$table]['fields'][] = $column['Field'];
                if ($column['Key'] === 'PRI') {
                    self::$schema[$table]['keys'][] = $column['Field'];
                }
            }
        }
        return  self::$schema[$table];
    }
    
    /**
     * 返回主键的字段名
     * @throws \Exception
     * @return string
     */
    private static function pk()
    {
        if (false === ($pk = isset(self::schema()['keys']) ? array_shift(self::schema()['keys']) : false)) {
            throw new \Exception(get_called_class() . ' does not have a primary key.');
        }
        return $pk;
    }
    
    /**
     * 设置模型对应的表名
     * e.g. User 对应表 user
     * e.g. UserName 对应表 user_name
     * @return string
     */
    public static function table()
    {
        return strtolower(preg_replace('/((?<=[a-z])(?=[A-Z]))/', '_', basename(get_called_class())));
    }
    
    /**
     * 设置数据库连接
     * @return \lying\db\Connection
     */
    public static function db()
    {
        return maker()->db();
    }
    
    /**
     * 设置字段值
     * @param string $name 字段名
     * @param mixed $value 字段值
     */
    public function __set($name, $value)
    {
        if (in_array($name, self::schema()['fields'])) {
            $this->attr[$name] = $value;
        }
    }
    
    /**
     * 取字段值
     * @param string $name 字段名
     * @return mixed 不存在返回null
     */
    public function __get($name)
    {
        return isset($this->attr[$name]) ? $this->attr[$name] : null;
    }
    
    /**
     * 查找数据
     * @return \lying\db\ARQuery
     */
    public static function find()
    {
        return (new ARQuery(self::db(), get_called_class()))->from([self::table()]);
    }
    
    /**
     * 查找一条记录
     * @param mixed $condition 如果为数组,则为查找条件;否则的话为查找主键
     * @return AR|mixed|boolean
     */
    public static function findOne($condition)
    {
        $query = (new ARQuery(self::db(), get_called_class()))->from([self::table()]);
        return $query->where(is_array($condition) ? $condition : [self::pk() => $condition])->one();
    }
    
    /**
     * 查找所有符合条件的记录
     * @param array $condition
     * @return AR|mixed|boolean
     */
    public static function findAll($condition)
    {
        return (new ARQuery(self::db(), get_called_class()))->from([self::table()])->all();
    }
    
    /**
     * 查找数据的时候,设置旧数据
     * @param AR $record 要设置的对象
     * @return \lying\db\AR
     */
    public static function populate(AR $record)
    {
        $record->oldAttr = $record->attr;
        return $record;
    }
}
