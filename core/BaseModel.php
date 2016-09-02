<?php
namespace core;
/**
 * 模型基类
 * @author suyq
 * @version 1.0
 * 如果Model要关联一个表,就必须设置静态函数Model::tableName()返回一个表名,并且继承此类
 * 如果Model设置了__construct,就必须在此函数的第一行调用父类parent::__construct();
 * 如果有多个数据库配置,选用不同的数据库需要在对应的Model写静态函数Model::db()并且返回数据库配置名,如果不写,默认为db
 */
class BaseModel {
    
    /**
     * 用来存放各个表的表名、字段、主键
     * @var array
     */
    private static $_struct = [];
    
    public function __construct($arg = []) {
        var_dump($arg);
    }
    
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
        self::$_struct[$tableName] = $tmp;
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
    
    public function get() {
        $sql = "select * from user where id = 1";
        return self::_connection()->query($sql)->fetchObject(get_called_class(), ['pdo'=>true]);
    }
    
    public function save() {
        
    }
    
    
    
}