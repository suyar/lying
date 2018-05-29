<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\db;

use lying\event\ActiveRecordEvent;
use lying\service\Service;

/**
 * Class ActiveRecord
 * @package lying\db
 */
class ActiveRecord extends BaseActive
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
     * @var string 插入或更新前触发的事件ID
     */
    const EVENT_BEFORE_SAVE = 'beforeSave';

    /**
     * @var string 插入或更新后触发的事件ID
     */
    const EVENT_AFTER_SAVE = 'afterSave';
    
    /**
     * @var array 新数据
     */
    private $_attr = [];
    
    /**
     * @var array 旧数据
     */
    private $_oldAttr;
    
    /**
     * 设置默认数据库连接
     * @return Connection
     */
    public static function db()
    {
        return \Lying::$maker->db;
    }
    
    /**
     * 设置模型对应的表名
     * ```php
     * UserModel 对应表 user
     * UserNameModel 对应表 user_name
     * ```
     * @return string 返回表名
     */
    public static function table()
    {
        $tmp = explode('\\', get_called_class());
        $table = preg_replace('/Model$/', '', array_pop($tmp));
        return '{{%' . strtolower(preg_replace('/((?<=[a-z])(?=[A-Z]))/', '_', $table)) . '}}';
    }
    
    /**
     * 设置属性值
     * @param string $name 属性名
     * @param mixed $value 属性值
     */
    public function __set($name, $value)
    {
        $this->_attr[$name] = $value;
    }
    
    /**
     * 获取属性值
     * @param string $name 属性名
     * @return mixed 不存在返回null
     */
    public function __get($name)
    {
        return $this->__isset($name) ? $this->_attr[$name] : null;
    }
    
    /**
     * 属性是否存在
     * @param string $name 属性名
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->_attr[$name]);
    }
    
    /**
     * 移除指定的属性
     * @param string $name 属性名
     */
    public function __unset($name)
    {
        unset($this->_attr[$name]);
    }

    /**
     * 查找数据
     * @return ActiveQuery
     */
    public static function find()
    {
        $query = new ActiveQuery([
            'db' => static::db(),
            'class' => get_called_class(),
        ]);
        return $query->from(static::table());
    }

    /**
     * 查找一条记录
     * @param string|array $condition 查找条件
     * @param array $params 参数绑定,在查询条件为字符串的时候有效
     * @return static|false 返回查询结果,失败返回false
     */
    public static function findOne($condition, $params = [])
    {
        return self::find()->where($condition, $params)->limit(1)->one();
    }

    /**
     * 查找所有符合条件的记录
     * @param string|array $condition 查找条件
     * @param array $params 参数绑定,在查询条件为字符串的时候有效
     * @return static[]|false 返回查询结果数组,失败返回false
     */
    public static function findAll($condition = '', $params = [])
    {
        return self::find()->where($condition, $params)->all();
    }

    /**
     * 根据条件删除数据
     * @param string|array $condition 删除的条件
     * @param array $params 参数绑定,在查询条件为字符串的时候有效
     * @return int|false 返回受影响的行数,有可能是0行,失败返回false
     */
    public static function deleteAll($condition = '', $params = [])
    {
        return self::find()->delete(static::table(), $condition, $params);
    }

    /**
     * 更新数据
     * @param array $datas 要更新的数据,[key => value]形式的数组;
     * @param string|array $condition 更新的条件
     * @param array $params 参数绑定,在查询条件为字符串的时候有效
     * @return int|false 返回受影响的行数,有可能是0行,失败返回false
     */
    public static function updateAll($datas, $condition = '', $params = [])
    {
        return self::find()->update(static::table(), $datas, $condition, $params);
    }

    /**
     * 过滤attr,返回表中有的字段的数据
     * @return array
     */
    private function filterAttr()
    {
        $columns = static::db()->schema()->getTableSchema(static::table())->columns;
        $attr = [];
        foreach ($this->_attr as $name => $value) {
            if (in_array($name, $columns)) {
                $attr[$name] = $value;
            }
        }
        return $attr;
    }

    /**
     * 插入当前设置的数据
     * @return int|false 成功返回插入的行数,失败返回false
     */
    private function insert()
    {
        $event = new ActiveRecordEvent();
        $this->trigger(self::EVENT_BEFORE_INSERT, $event);
        $this->trigger(self::EVENT_BEFORE_SAVE, $event);
        $rows = self::find()->insert(static::table(), $this->filterAttr());
        if (false !== $rows) {
            $autoIncrement = static::db()->schema()->getTableSchema(static::table())->autoIncrement;
            if ($autoIncrement) {
                $this->_attr[$autoIncrement] = static::db()->lastInsertId();
            }
            $this->reload();
        }
        $event->rows = $rows;
        $this->trigger(self::EVENT_AFTER_INSERT, $event);
        $this->trigger(self::EVENT_AFTER_SAVE, $event);
        return $rows;
    }

    /**
     * 返回旧数据的条件(主键键值对),用于更新数据
     * @return array 条件数组
     */
    private function oldCondition()
    {
        $primaryKeys = static::db()->schema()->getTableSchema(static::table())->primaryKeys;
        if ($primaryKeys) {
            $values = [];
            foreach ($primaryKeys as $primaryKey) {
                $values[$primaryKey] = isset($this->_oldAttr[$primaryKey]) ? $this->_oldAttr[$primaryKey] : null;
            }
            return $values;
        } else {
            throw new \Exception(static::table() . ' does not have a primary key.');
        }
    }

    /**
     * 更新当前数据
     * @return int|false 成功返回更新的行数,可能是0行,失败返回false
     */
    private function update()
    {
        $event = new ActiveRecordEvent();
        $this->trigger(self::EVENT_BEFORE_UPDATE, $event);
        $this->trigger(self::EVENT_BEFORE_SAVE, $event);
        $rows = self::find()->update(static::table(), $this->filterAttr(), $this->oldCondition());
        if (false !== $rows) {
            $this->reload();
        }
        $event->rows = $rows;
        $this->trigger(self::EVENT_AFTER_UPDATE, $event);
        $this->trigger(self::EVENT_AFTER_SAVE, $event);
        return $rows;
    }

    /**
     * 删除本条数据
     * @return int|false 成功返回删除的行数,可能是0行,失败返回false
     */
    public function delete()
    {
        $event = new ActiveRecordEvent();
        $this->trigger(self::EVENT_BEFORE_DELETE, $event);
        $rows = self::find()->delete(static::table(), $this->oldCondition());
        if (false !== $rows) {
            $this->_oldAttr = null;
        }
        $event->rows = $rows;
        $this->trigger(self::EVENT_AFTER_DELETE, $event);
        return $rows;
    }
    
    /**
     * 是否为新记录
     * @return bool 新纪录返回true,否则返回false
     */
    public function isNewRecord()
    {
        return $this->_oldAttr === null;
    }

    /**
     * 保存数据
     * @return int|false 成功返回保存的行数,失败返回false
     */
    public function save()
    {
        return $this->isNewRecord() ? $this->insert() : $this->update();
    }
    
    /**
     * 把新数据赋值给旧数据
     * @return $this
     */
    protected function reload()
    {
        $this->_oldAttr = $this->_attr;
        return $this;
    }
}
