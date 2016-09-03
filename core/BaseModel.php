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
     * @return Struct
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
        self::$_struct[$tableName] = new Struct($tmp);
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
     * @param string $name 属性名,只有当属性名在表的字段中有的时候,属性才会设置成功
     * @param mixed $value 属性值
     */
    public function __set($name, $value) {
        if(in_array($name, $this->_struct()->fields)) $this->_data[$name] = $value;
    }
    
    /**
     * 获取属性值
     * @param string $name 属性名,不存在此属性返回null
     * @return NULL|mixed
     */
    public function __get($name) {
        return isset($this->_data[$name]) ? $this->_data[$name] : null;
    }
    
    /**
     * 插入一条数据
     * @return boolean 成功返回true,失败返回false
     */
    private function _insert() {
        $struct = self::_struct();
        $keys = array_keys($this->_data);
        $vals = array_fill(0, count($keys), "?");
        $sql = "INSERT INTO $struct->name (" . implode(", ", $keys) . ") VALUES (" . implode(", ", $vals) . ")";
        return self::_connection()->prepare($sql)->execute(array_values($this->_data));
    }
    
    /**
     * 更新一条数据
     * @param string $condition 条件
     * @param array $params 参数
     */
    private function _update($condition, $params) {
        $struct = self::_struct();
        $keys = array_map(function($v) { return "$v = ?"; }, array_keys($this->_data));
        $vals = array_values($this->_data);
        if ($condition) $vals = array_merge($vals, $params);
        $sql = "UPDATE $struct->name SET " . implode(", ", $keys) . $condition;
        return self::_connection()->prepare($sql)->execute($vals);
    }
    
    /**
     * 删除一条记录
     * @param string $condition 查询条件
     * @param array $params 参数
     * @return boolean
     */
    private function _delete($condition, $params) {
        $struct = self::_struct();
        $sql = "DELETE FROM $struct->name" . $condition;
        return self::_connection()->prepare($sql)->execute($params);
    }
    
    /**
     * 新增或者保存一条记录
     * @param string $condition 更新时带入的WHERE条件
     * 如果对象里有主键并且主键的值存在,传入此参数和$params参数并没有什么卵用
     * 如果对象里没有主键或者主键的值不存在,请传入此参数,并且把所有的值用“?”表示
     * 如："id = ? AND sex = ?"
     * @param array $params 代替$condition里的“?”的数组
     * 此参数的长度必须跟上面?的个数一样多,并且按照顺序填入,否则出错
     * @return boolean
     */
    public function save($condition = '', $params = []) {
        $struct = self::_struct();
        if ($this->_isNew === true) return $this->_insert();
        if ($struct->key !== null && isset($this->_data[$struct->key])) {
            //主键存在并且数据中有主键存在
            $condition = " WHERE $struct->key = ?";
            $params = [$this->_data[$struct->key]];
        }else {
            //没有主键或者主键没有在数据中
            $condition = " WHERE $condition";
        }
        return $this->_update($condition, $params);
    }
    
    /**
     * 删除记录
     * @param string $condition 删除时带入的WHERE条件
     * 如果对象里有主键并且主键的值存在,传入此参数和$params参数并没有什么卵用
     * 如果对象里没有主键或者主键的值不存在,请传入此参数,并且把所有的值用“?”表示
     * 如："id = ? AND sex = ?"
     * @param array $params 代替$condition里的“?”的数组
     * 此参数的长度必须跟上面?的个数一样多,并且按照顺序填入,否则出错
     * @return boolean
     */
    public function delete($condition = '', $params = []) {
        $struct = self::_struct();
        if ($struct->key !== null && isset($this->_data[$struct->key])) {
            $condition = " WHERE $struct->key = ?";
            $params = [$this->_data[$struct->key]];
        }else {
            $condition = " WHERE $condition";
        }
        return $this->_delete($condition, $params);
    }
    
    /**
     * 过滤返回需要的字段
     * @param array $fields 要查询的字段
     * @param boolean $exclude 是否排除
     * @return string
     */
    private static function _fields($fields, $exclude) {
        $struct = self::_struct();
        $fields = $exclude ? array_diff($struct->fields, $fields) : array_intersect($struct->fields, $fields);
        return $fields ? implode(", ", $fields) : implode(", ", $struct->fields);
    }
    
    /**
     * 根据主键值来查询一条记录
     * @param mixed $val 主键值
     * @param array $fields 要查询的字段,会自动过滤表中没有的字段
     * @param string $exclude 是否为排除字段
     * @return self|boolean
     */
    public static function findByPk($val, $fields = [], $exclude = false) {
        $struct = self::_struct();
        if ($struct->key === null) return false;
        $fields = self::_fields($fields, $exclude);
        $sql = "SELECT $fields FROM $struct->name WHERE $struct->key = ?";
        $sth = self::_connection()->prepare($sql);
        $res = $sth->execute([$val]);
        if ($res === false) return false;
        $res = $sth->fetchObject(get_called_class());
        $sth->closeCursor();
        return $res;
    }
    
    
    
    
    /**
     * 
     * @return $this|boolean
     */
    public function get() {
        $sql = "select * from user where id = 2";
        $class = get_class($this);
        $result = self::_connection()->query($sql)->fetchObject($class);
        if ($result instanceof $class) $result->_isNew = false;
        return $result;
    }
    
    
    
    
    
}