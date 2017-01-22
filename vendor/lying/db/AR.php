<?php
namespace lying\db;

use lying\service\Service;

class AR extends Service
{
    /**
     * @var array 表字段值
     */
    public $attr = [];
    
    /**
     * @var array 旧的字段值
     */
    public $oldAttr;
    
    /**
     * @var array 存表结构
     */
    private static $schema = [];
    

    
    /**
     * 设置模型对应的表名,默认去除末尾Model
     * e.g. UserModel 对应表 user
     * e.g. UserNameModel 对应表 user_name
     * @return string
     */
    public static function table()
    {
        $class = preg_replace('/Model$/', '', basename(get_called_class()));
        return strtolower(preg_replace('/((?<=[a-z])(?=[A-Z]))/', '_', $class));
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
        $this->attr[$name] = $value;
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
    
    
    public static function find()
    {
        return (new ARQuery(self::db(), get_called_class()))->from([self::table()]);
    }
    
    public function toOld(AR $record)
    {
        $record->oldAttr = $record->attr;
    }
    
    
}