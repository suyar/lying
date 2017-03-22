<?php
namespace lying\db;

use lying\service\Hook;
use lying\service\Service;

/**
 * 活动记录基类
 *
 * @author carolkey <me@suyaqi.cn>
 * @since 2.0
 * @link https://carolkey.github.io/
 * @license MIT
 */
class ActiveRecord extends Service
{
    /**
     * @var string 插入前触发的事件ID
     */
    const EVENT_BEFORE_INSERT = 'beforeInsert';
    
    /**
     * @var string 插入后触发的事件ID
     */
    const EVENT_AFTER_INSERT = 'afterInsert';
    
    /**
     * @var string 更新前触发的事件ID
     */
    const EVENT_BEFORE_UPDATE = 'beforeUpdate';
    
    /**
     * @var string 更新后触发的事件ID
     */
    const EVENT_AFTER_UPDATE = 'afterUpdate';
    
    /**
     * @var string 删除前触发的事件ID
     */
    const EVENT_BEFORE_DELETE = 'beforeDelete';
    
    /**
     * @var string 删除后触发的事件ID
     */
    const EVENT_AFTER_DELETE = 'afterDelete';
    
    /**
     * @var array 新数据
     */
    private $attr = [];
    
    /**
     * @var array 旧数据
     */
    private $oldAttr;
    
    /**
     * @var array 表结构
     */
    private static $schema = [];
    
    /**
     * 设置数据库连接
     * @return \lying\db\Connection
     */
    public static function db()
    {
        return \Lying::$maker->db();
    }
    
    /**
     * 设置模型对应的表名
     * User 对应表 user
     * UserName 对应表 user_name
     * @return string 返回表名
     */
    public static function table()
    {
        $tmp = explode('\\', get_called_class());
        return strtolower(preg_replace('/((?<=[a-z])(?=[A-Z]))/', '_', array_pop($tmp)));
    }
    
    /**
     * 返回表的结构
     * @return array 表的结构
     */
    private static function schema()
    {
        $table = static::table();
        if (!isset(self::$schema[$table])) {
            $struct = self::db()->pdo()->query("DESC $table")->fetchAll();
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
     * 返回所有的主键，没有主键返回false
     * @return array|boolean
     */
    private static function pk()
    {
        $schema = self::schema();
        return isset($schema['keys']) && !empty($schema['keys']) ? $schema['keys'] : false;
    }
    
    /**
     * 设置属性值
     * @param string $name 属性名
     * @param mixed $value 属性值
     */
    public function __set($name, $value)
    {
        if (in_array($name, self::schema()['fields'])) {
            $this->attr[$name] = $value;
        }
    }
    
    /**
     * 取属性值
     * @param string $name 属性名
     * @return mixed 不存在返回null
     */
    public function __get($name)
    {
        return isset($this->attr[$name]) ? $this->attr[$name] : null;
    }
    
    /**
     * 属性是否存在
     * @param string $name 属性名
     * @return boolean
     */
    public function __isset($name)
    {
        return $this->__get($name) !== null;
    }
    
    /**
     * 释放给定的属性
     * @param string $name 属性名
     */
    public function __unset($name)
    {
        if (isset($this->attr[$name])) {
            unset($this->attr[$name]);
        }
    }
    
    /**
     * 查找数据
     * @return \lying\db\ActiveRecordQuery
     */
    public static function find()
    {
        return (new ActiveRecordQuery(static::db(), get_called_class()))->from([static::table()]);
    }
    
    /**
     * 查找一条记录
     * @param mixed $condition 如果为数组，则为查找条件；否则的话为查找第一个主键
     * @return ActiveRecord|boolean 返回查询结果或者false
     * @throws \Exception 主键不存在抛出异常
     */
    public static function findOne($condition)
    {
        if (!is_array($condition)) {
            if (false === $pks = self::pk()) {
                throw new \Exception(get_called_class() . ' does not have a primary key.');
            } else {
                $condition = [reset($pks) => $condition];
            }
        }
        return self::find()->where($condition)->limit(1)->one();
    }
    
    /**
     * 查找所有符合条件的记录
     * @param array $condition 查看Query::where()的数组使用方式
     * @return ActiveRecord[]|boolean 返回查询结果数组，失败返回false
     */
    public static function findAll($condition = [])
    {
        return self::find()->where($condition)->all();
    }
    
    /**
     * 插入当前设置的数据
     * @return integer|boolean 成功返回插入的行数，失败返回false
     */
    public function insert()
    {
        $this->trigger(self::EVENT_BEFORE_INSERT);
        Hook::trigger(self::EVENT_BEFORE_INSERT);
        $res = self::db()->createQuery()->insert(static::table(), $this->attr);
        if (false !== $res && (false !== $keys = self::pk())) {
            foreach ($keys as $key) {
                $this->attr[$key] = self::db()->lastInsertId($key);
            }
            self::populate($this);
        }
        $this->trigger(self::EVENT_AFTER_INSERT, $res);
        Hook::trigger(self::EVENT_AFTER_INSERT, $res);
        return $res;
    }
    
    /**
     * 返回旧数据的条件(主键键值对)
     * @param array $pks 主键数组
     * @return array 条件数组
     */
    public function oldCondition($pks)
    {
        $values = [];
        foreach ($pks as $pk) {
            $values[$pk] = isset($this->oldAttr[$pk]) ? $this->oldAttr[$pk] : null;
        }
        return $values;
    }
    
    /**
     * 更新当前数据
     * @return number|boolean 成功返回更新的行数，失败返回false
     * @throws \Exception 主键不存在抛出异常
     */
    public function update()
    {
        $this->trigger(self::EVENT_BEFORE_UPDATE);
        Hook::trigger(self::EVENT_BEFORE_UPDATE);
        if (false === $pks = self::pk()) {
            throw new \Exception(get_called_class() . ' does not have a primary key.');
        }
        $res = self::db()->createQuery()->update(static::table(), $this->attr, $this->oldCondition($pks));
        if (false !== $res) {
            self::populate($this);
        }
        $this->trigger(self::EVENT_AFTER_UPDATE, $res);
        Hook::trigger(self::EVENT_AFTER_UPDATE, $res);
        return $res;
    }
    
    /**
     * 删除本条数据
     * @return integer|boolean 成功返回删除的行数，失败返回false
     * @throws \Exception 主键不存在抛出异常
     */
    public function delete()
    {
        $this->trigger(self::EVENT_BEFORE_DELETE);
        Hook::trigger(self::EVENT_BEFORE_DELETE);
        if (false === $pks = self::pk()) {
            throw new \Exception(get_called_class() . ' does not have a primary key.');
        }
        $res = self::db()->createQuery()->delete(static::table(), $this->oldCondition($pks));
        if (false !== $res) {
            $this->oldAttr = null;
        }
        $this->trigger(self::EVENT_AFTER_DELETE, $res);
        Hook::trigger(self::EVENT_AFTER_DELETE, $res);
        return $res;
    }
    
    /**
     * 是否为新记录
     * @return boolean 新纪录返回true，否则返回false
     */
    public function isNewRecord()
    {
        return $this->oldAttr === null;
    }
    
    /**
     * 保存数据
     * @return integer|boolean 成功返回保存的行数，失败返回false
     */
    public function save()
    {
        return $this->isNewRecord() ? $this->insert() : $this->update();
    }
    
    /**
     * 把新数据赋值给旧数据
     * @param ActiveRecord $record 要设置的对象
     * @return \lying\db\ActiveRecord
     */
    public static function populate(ActiveRecord $record)
    {
        $record->oldAttr = $record->attr;
        return $record;
    }
}
