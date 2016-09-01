<?php
namespace core;
/**
 * 模型基类
 * @author suyq
 * @version 1.0
 */
use \App;
abstract class Model {
    
    /**
     * 此方法应该返回一个关联的表名
     * @return string
     */
    abstract static function tableName();
    
    /**
     * 用来存各个表的结构
     * @var array
     */
    private static $schema = [];
    
    /**
     * 获取各个表的结构
     * @return multitype:
     */
    private static function schema() {
        $className = get_called_class();
        if (!method_exists($className, 'tableName')) throw new \Exception("Method $className::tableName() not found");
        $tableName = $className::tableName();
        if (isset(self::$schema[$tableName])) return self::$schema[$tableName];
        $sth = App::db()->query("DESC $tableName");
        if (!$sth) throw new \Exception("Table $tableName not found");
        $res = $sth->fetchAll(\PDO::FETCH_ASSOC);
        $tmp['tableName'] = $tableName;
        $tmp['tableFields'] = array_column($res, 'Field');
        $pks = array_filter($res, function($v) {
            return $v['Key'] === 'PRI';
        });
        $tmp['primaryKey'] = $pks ? $pks[0]['Field'] : null;
        self::$schema[$tableName] = (object)$tmp;
        return self::$schema[$tableName];
    }
    
    /**
     * 要查询的本表的字段,自动去掉表中没有的字段,如果都不是表中的字段,就查询所有字段
     * @param array $columns 字段,用数组形式
     * @param boolean $exclude 是否为排除字段
     * @return string
     */
    private static function field($columns = [], $exclude = false) {
        $fields = self::schema()->tableFields;
        $columns = $exclude ? array_diff($fields, $columns) : array_intersect($fields, $columns);
        if ($columns) return implode(', ', $columns);
        return implode(', ', $fields);
    }
    
    /**
     * 更新数据
     * @param array $columns
     * @param string $condition
     * @param array $params
     * @return boolean
     */
    public static function update($columns, $condition = '', $params = []) {
        $tableName = self::schema()->tableName;
        $keys = array_keys($columns);
        $update = array_map(function($key) {
            return "$key = :$key";
        }, $keys);
        $update = implode(', ', $update);
        $param = array_map(function($k) {
            return ":$k";
        }, array_flip($columns));
        $param = array_flip($param);
        $params = array_merge($param, $params);
        $condition = $condition ? " WHERE $condition" : '';
        $sql = "UPDATE $tableName SET $update$condition";
        $sth = App::db()->prepare($sql);
        return $sth->execute($params);
    }
    
    /**
     * 执行原生SQL
     * @param string $sql
     * @param array $params
     * @return PDOStatement
     */
    public static function rawSql($sql, $params = []) {
        $sth = App::db()->prepare($sql);
        $sth->execute($params);
        return $sth;
    }
    
    /**
     * 查找数据
     * @param string $condition
     * @param array $params
     * @param array $fields
     * @param string $exclude
     * @return mixed[]
     */
    public static function find($condition, $params = [], $fields = [], $exclude = false) {
        $tableName = self::schema()->tableName;
        $sql = "SELECT ".self::field($fields, $exclude)." FROM $tableName WHERE $condition";
        $sth = App::db()->prepare($sql);
        $sth->execute($params);
        $res = [];
        while (!!$row = $sth->fetchObject()) {
            $res[] = $row;
        }
        return $res;
    }
    
    /**
     * 根据主键的值来获取一条记录
     * @param mixed $val 主键的值
     * @param array $fields 要显示的字段
     * @param boolean $exclude 知否排除上面的字段
     * @return mixed
     */
    public static function findByPk($val, $fields = [], $exclude = false) {
        $tableName = self::schema()->tableName;
        $primaryKey = self::schema()->primaryKey;
        $sql = "SELECT ".self::field($fields, $exclude)." FROM $tableName WHERE $primaryKey = :pk";
        $sth = App::db()->prepare($sql);
        $sth->execute([':pk'=>$val]);
        return $sth->fetchObject();
    }
    
    /**
     * 插入一条数据
     * @param array $columns 要插入的数据 eg:['id'=>1, 'name'=>'su', 'sex'=>1]
     * @return number 返回受影响的行数
     */
    public static function insert($columns) {
        $keys = array_keys($columns);
        $placeholders = array_fill(0, count($columns), '?');
        $tableName = self::schema()->tableName;
        $sql = "INSERT INTO $tableName (" . implode(", ", $keys) . ") VALUES (" . implode(", ", $placeholders) . ")";
        $sth = App::db()->prepare($sql);
        $sth->execute(array_values($columns));
        return $sth->rowCount();
    }
    
    /**
     * 批量插入(串成一条SQL语句执行)
     * @param array $columns 要插入的字段['username', 'password', 'sex']
     * @param array $rows 一个二维数组,要插入的数据[ ['su', '123456', 1], ['xie', '654321', 0] ]
     * @return number
     */
    public static function batchInsert($columns, $rows) {
        $values = [];
        foreach ($rows as $row) {
            $vs = [];
            foreach ($row as $v) {
                $vs[] = self::quoteValue($v);
            }
            $values[] = "(" . implode(", ", $vs) . ")";
        }
        $tableName = self::schema()->tableName;
        $sql = "INSERT INTO $tableName (" . implode(", ", $columns) . ") VALUES " . implode(", ", $values);
        return App::db()->exec($sql);
    }
    
    /**
     * 批量插入(用PDO的占位符循环执行)
     * @param array $columns 要插入的字段['username', 'password', 'sex']
     * @param array $rows 一个二维数组,要插入的数据[ ['su', '123456', 1], ['xie', '654321', 0] ]
     * @return number
     */
    public static function batchInsert1($columns, $rows) {
        $placeholders = array_fill(0, count($columns), '?');
        $tableName = self::schema()->tableName;
        $sql = "INSERT INTO $tableName (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
        $sth = App::db()->prepare($sql);
        $nums = 0;
        foreach ($rows as $row) {
            $nums += $sth->execute($row);
        }
        return $nums;
    }
    
    /**
     * 对字符串进行转义并且加单引号
     * @param string $value
     * @return string
     */
    public static function quoteValue($value) {
        if (!is_string($value)) return $value;
        $res = App::db()->quote($value);
        return $res === false ? "'$value'" : $res;
    }
}