<?php
namespace core;
/**
 * 模型基类
 * @author suyq
 * @version 1.0
 * 如果Model要关联一个表,就必须设置静态函数Model::tableName()返回一个表名,并且继承此类
 * Model不要设置__construct,就必须在此函数的第一行调用父类parent::__construct();
 * 如果有多个数据库配置,选用不同的数据库需要在对应的Model写静态函数Model::db()并且返回数据库配置名,如果不写,默认为db
 */
class BaseModel {
    /**
     * 是否为新纪录
     * @var boolean
     */
    private $_isNew = true;
    
    /**
     * 主键的值
     * @var mixed
     */
    private $_pk = null;
    
    /**
     * 用来存放纪录
     * @var array
     */
    private $_data = [];
    
    /**
     * 用来存放各个表的表名、字段、主键
     * @var array
     */
    private static $_struct = [];
    
    /**
     * 返回表的结构
     * @throws \Exception
     * @return array
     */
    private static function _struct() {
        $tableName = self::_tableName();
        if (isset(self::$_struct[$tableName])) return self::$_struct[$tableName];
        $sth = self::_connection()->query("DESC $tableName");
        if ($sth === false) throw new \Exception("Table $tableName not found");
        $fieldInfo = $sth->fetchAll(\PDO::FETCH_ASSOC);
        $tmp = ['name' => $tableName, 'fields' => array_column($fieldInfo, 'Field')];
        $pk = array_filter($fieldInfo, function($v) { return $v['Key'] === 'PRI'; });
        $pk = array_shift($pk);
        $tmp['key'] = $pk ? $pk['Field'] : null;
        self::$_struct[$tableName] = (object)$tmp;
        return self::$_struct[$tableName];
    }
    
    /**
     * 返回关联的表名
     * @throws \Exception
     * @return string
     */
    private static function _tableName() {
        $called_class = get_called_class();
        if (!method_exists($called_class, 'tableName')) throw new \Exception("Method $called_class::tableName() not found");
        return $called_class::tableName();
    }
    
    /**
     * 获取PDO连接
     * @return PDO
     */
    private static function _connection() {
        $called_class = get_called_class();
        return method_exists($called_class, 'db') ? \App::db($called_class::db()) : \App::db();
    }
    
    /**
     * 设置属性
     * @param string $name 属性名
     * @param mixed $value 属性值
     */
    public function __set($name, $value) {
        $this->_data[$name] = $value;
    }
    
    /**
     * 获取属性值
     * @param string $name 属性名,不存在此属性返回null
     * @return NULL|mixed
     */
    public function __get($name) {
        return isset($this->_data[$name]) ? $this->_data[$name] : null;
    }
    
    
    public function save($condition = "", $params = []) {
        $struct = self::_struct();
        $fields = $struct->fields;
        $data = $update = [];
        foreach ($this->_data as $k => $v) {
            if (in_array($k, $fields)) {
                $update[] = "$k = ?";
                $data[] = $v;
            }
        }
        $update = implode(", ", $update);
        if ($struct->key) {
            $condition = " WHERE $struct->key = ?";
            $data[] = $this->_data[$struct->key];
        }else if ($condition) {
            $condition = " WHERE $condition";
        }
        $sql = "UPDATE $struct->name SET $update$condition";
        return $sth = self::_connection()->prepare($sql)->execute($data);
    }
    
    /**
     * 
     * @return $this|boolean
     */
    public function get() {
        $sql = "select * from user where id = 1";
        $class = get_class($this);
        $result = self::_connection()->query($sql)->fetchObject($class);
        if ($result instanceof $class) $result->_isNew = false;
        return $result;
    }
    
    
    
    
    
}