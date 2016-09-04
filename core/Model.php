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
class Model {
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
     * @param string $condition 更新条件
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
     * @param string $condition 删除条件
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
     * 如果对象里有主键并且主键的值存在,传入此参数和$params参数并没有什么卵用;
     * 如果对象里没有主键或者主键的值不存在,请传入此参数;
     * 如果传入关联数组,数组的键为字段名;
     * 如果传入字符串并且字段的值用“?”表示,请传入$params参数,数组的长度应和“?”的个数一样,否则就不需要$params参数或者传入空数组;
     * 如："id = ? AND sex = ?"
     * @param array $params 代替$condition里的“?”的数组;
     * 此参数的长度必须跟上面?的个数一样多,并且按照顺序填入,否则出错
     * @return boolean
     */
    public function save($condition = '', $params = []) {
        $struct = self::_struct();
        if ($this->_isNew === true) return $this->_insert();
        if ($struct->key !== null && isset($this->_data[$struct->key])) {
            $condition = " WHERE $struct->key = ?";
            $params = [$this->_data[$struct->key]];
        }else {
            self::_buildCondition($condition, $params);
        }
        return $this->_update($condition, $params);
    }
    
    /**
     * 删除记录
     * @param string $condition 删除时带入的WHERE条件;
     * 如果对象里有主键并且主键的值存在,传入此参数和$params参数并没有什么卵用;
     * 如果对象里没有主键或者主键的值不存在,请传入此参数;
     * 如果传入关联数组,数组的键为字段名;
     * 如果传入字符串并且字段的值用“?”表示,请传入$params参数,数组的长度应和“?”的个数一样,否则就不需要$params参数或者传入空数组;
     * 如："id = ? AND sex = ?"
     * @param array $params 代替$condition里的“?”的数组;
     * 此参数的长度必须跟上面?的个数一样多,并且按照顺序填入,否则出错
     * @return boolean
     */
    public function remove($condition = '', $params = []) {
        $struct = self::_struct();
        if ($struct->key !== null && isset($this->_data[$struct->key])) {
            $condition = " WHERE $struct->key = ?";
            $params = [$this->_data[$struct->key]];
        }else {
            self::_buildCondition($condition, $params);
        }
        return $this->_delete($condition, $params);
    }
    
    /**
     * 过滤返回需要的字段
     * @param array $fields 要查询的字段
     * @param boolean $exclude 是否排除
     * @return string 返回拼接好的字段
     */
    private static function _fields($fields, $exclude) {
        $struct = self::_struct();
        $fields = $exclude ? array_diff($struct->fields, $fields) : array_intersect($struct->fields, $fields);
        return $fields ? implode(', ', $fields) : implode(', ', $struct->fields);
    }
    
    /**
     * 根据主键值来查询一条记录
     * @param mixed $val 主键值
     * @param array $fields 要查询的字段,会自动过滤表中没有的字段,默认查询所有字段
     * @param string $exclude 是否为排除字段
     * @return self|boolean 成功返回结果集对象,失败返回false
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
     * 返回条件语句
     * @param string|array &$condition
     * @param array &$params
     */
    private static function _buildCondition(&$condition, &$params) {
        if (is_array($condition)) {
            $keys = array_map(function($v) { return "$v = ?"; }, array_keys($condition));
            $params = array_values($condition);
            $condition = ' WHERE ' . implode(' AND ', $keys);
        }elseif (is_string($condition)) {
            $condition = ' WHERE ' . $condition;
        }else {
            $condition = '';
        }
    }
    
    /**
     * 数据查询
     * @param string|array $condition 要查询的条件;
     * 如果传入关联数组,数组的键为字段名;
     * 如果传入字符串并且字段的值用“?”表示,请传入$params参数,数组的长度应和“?”的个数一样,否则就不需要$params参数或者传入空数组;
     * 如果不写或者为空数组,默认为查询全部数据
     * @param array $params 此参数为代替“?”的数据,并且按照顺序排列
     * @param array $fields 要查询的字段列表,默认为查询所有字段,函数会过滤非标中的字段
     * @param string $exclude 是否为排除字段
     * @return boolean|array 返回结果集对象的数组
     */
    public static function find($condition = null, $params = [], $fields = [], $exclude = false) {
        $struct = self::_struct();
        self::_buildCondition($condition, $params);
        $fields = self::_fields($fields, $exclude);
        $sql = "SELECT $fields FROM $struct->name$condition";
        $sth = self::_connection()->prepare($sql);
        $res = $sth->execute($params);
        if ($res === false) return false;
        $res = $sth->fetchAll(\PDO::FETCH_CLASS, get_called_class());
        $sth->closeCursor();
        return $res;
    }

    /**
     * 不管查询条件查询多少条记录,只返回一条记录
     * @param string|array $condition 要查询的条件;
     * 如果传入关联数组,数组的键为字段名;
     * 如果传入字符串并且字段的值用“?”表示,请传入$params参数,数组的长度应和“?”的个数一样,否则就不需要$params参数或者传入空数组;
     * 如果不写或者为空数组,默认为查询全部数据
     * @param array $params 此参数为代替“?”的数据,并且按照顺序排列
     * @param array $fields 要查询的字段列表,默认为查询所有字段,函数会过滤非标中的字段
     * @param string $exclude 是否为排除字段
     * @return boolean|self 返回结果集对象
     */
    public static function findOne($condition = null, $params = [], $fields = [], $exclude = false) {
        $struct = self::_struct();
        self::_buildCondition($condition, $params);
        $fields = self::_fields($fields, $exclude);
        $sql = "SELECT $fields FROM $struct->name$condition";
        $sth = self::_connection()->prepare($sql);
        $res = $sth->execute($params);
        if ($res === false) return false;
        $res = $sth->fetchObject(get_called_class());
        $sth->closeCursor();
        return $res;
    }
    
    /**
     * 过滤要插入的数据
     * @param array $data 如果传入的不是数组,$data赋值为null
     */
    private static function _filterData(&$data) {
        if (!is_array($data)) $data = false;
        $struct = self::_struct();
        foreach ($data as $k=>$v) {
            if (!in_array($k, $struct->fields)) unset($data[$k]);
        }
    }
    
    /**
     * 插入一条数据
     * @param array $data 请输入关联数组,数组的键名为字段名,系统会自动过滤非表中的字段
     * @return boolean 成功返回true,失败返回false
     */
    public static function insert($data) {
        self::_filterData($data);
        if (!$data) return false;
        $tableName = self::_struct()->name;
        $keys = array_keys($data);
        $placeholders = array_fill(0, count($keys), '?');
        $sql = "INSERT INTO $tableName (" . implode(', ', $keys) . ') VALUES (' . implode(', ', $placeholders) . ')';
        return self::_connection()->prepare($sql)->execute(array_values($data));
    }
    
    /**
     * 转义字符并且加引号
     * @param string|int $value
     */
    public static function quoteValue($value) {
        return self::_connection()->quote($value);
    }
    
    /**
     * 批量插入(串成一条SQL语句执行)
     * @param array $columns 要插入的字段:['username', 'password', 'sex'];
     * 注意：此函数并不会对要插入的字段进行过滤(是否为表的字段),所以请自行确认$columns为表中的字段;
     * @param array $data 要插入的数据,一个二维数组:[ ['su', '123456', 1], ['xie', '654321', 0] ];
     * 注意：请确认每条数据的长度和$columns一样,否则出错
     * @return int 返回受影响的行数
     */
    public static function batchInsert($columns, $data) {
        $vals = [];
        foreach ($data as $row) {
            $v = [];
            foreach ($row as $r) {
                $v[] = self::quoteValue($r);
            }
            $vals[] = '(' . implode(', ', $v) . ')';
        }
        $tableName = self::_struct()->name;
        $sql = "INSERT INTO $tableName (" . implode(', ', $columns) . ') VALUES ' . implode(', ', $vals);
        return self::_connection()->exec($sql);        
    }
    
    /**
     * 批量插入(用PDO的占位符循环执行)
     * @param array $columns 要插入的字段:['username', 'password', 'sex'];
     * 注意：此函数并不会对要插入的字段进行过滤(是否为表的字段),所以请自行确认$columns为表中的字段
     * @param array $data 要插入的数据,一个二维数组:[ ['su', '123456', 1], ['xie', '654321', 0] ];
     * 注意：请确认每条数据的长度和$columns一样,否则出错
     * @return int 返回受影响的行数
     */
    public static function batchInsert1($columns, $data) {
        $placeholders = array_fill(0, count($columns), '?');
        $tableName = self::_struct()->name;
        $sql = "INSERT INTO $tableName (" . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')';
        $sth = self::_connection()->prepare($sql);
        $num = 0;
        foreach ($data as $row) {
            $num += $sth->execute($row);
        }
        return $num;
    }
    
    /**
     * 更新数据
     * @param array $data 一个关联数组,数组的键为字段名,函数会过滤表中没有的字段
     * @param string|array $condition 更新的条件;
     * 如果传入关联数组,数组的键为字段名;
     * 如果传入字符串并且字段的值用“?”表示,请传入$params参数,数组的长度应和“?”的个数一样,否则就不需要$params参数
     * @param array $params 用来代替“?”的数组,请按顺序填写
     * @return boolean 成功返回true,失败返回false
     */
    public static function update($data, $condition, $params = []) {
        self::_filterData($data);
        if (!$data) return false;
        self::_buildCondition($condition, $params);
        if (!$condition) return false;
        $struct = self::_struct();
        $keys = array_map(function($v) { return "$v = ?"; }, array_keys($data));
        $vals = array_merge(array_values($data), $params);
        $sql = "UPDATE $struct->name SET " . implode(', ', $keys) . $condition;
        return self::_connection()->prepare($sql)->execute($vals);
    }
    
    /**
     * 删除数据
     * @param array|string $condition 删除的条件;
     * 如果此参数是关联数组,则键名为字段名;
     * 如果此字段为字符串且字段值用“?”代替,请传入$params参数,否则不用传;
     * 如果不传,则删除表里的所有数据
     * @param array $params 参数,用来替换“?”的参数列表,请按顺序写入
     */
    public static function delete($condition = null, $params = []) {
        self::_buildCondition($condition, $params);
        $tableName = self::_struct()->name;
        $sql = "DELETE FROM $tableName" . $condition;
        return self::_connection()->prepare($sql)->execute($params);
    }
}