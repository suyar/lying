<?php
namespace lying\db;

class QueryBuilder
{
    /**
     * @var Connection
     */
    private $connection;
    
    
    private $from;
    
    private $params = [];
    
    
    
    /**
     * @param Connection $connection
     */
    public function __construct($connection)
    {
        $this->connection = $connection;
    }
    
    /**
     * 设置要操作的表
     * @param string $table
     * @return \lying\db\QueryBuilder
     */
    public function from($table)
    {
        $this->from = $table;
        return $this;
    }
    
    /**
     * 插入一条数据
     * @param array $data 接收一个关联数组,其中键为字段名,值为字段值.注:键名不在表的字段的数据将被过滤掉.
     * @return boolean 成功返回true,失败返回false
     */
    public function insert($data)
    {
        if (is_array($data)) {
            $this->filterFields($data);
            $keys = array_keys($data);
            $placeholder = array_fill(0, count($data), '?');
            $statement = "INSERT INTO `$this->from` (`" . implode('`, `', $keys) . "`) VALUES (" . implode(', ', $placeholder) . ")";
            return $this->connection->PDO()->prepare($statement)->execute(array_values($data));
        }
        return false;
    }
    
    public function microtime()
    {
        list($msec, $sec) = explode(' ', microtime());
        return $sec . ceil($msec * 1000);
    }
    
    
    
    public function batchInsert($fields, $data = [])
    {
        if ($data) {
            $fields = array_intersect($fields, $this->connection->getSchema($this->from)->fields);
            $statement = "INSERT INTO `$this->from` (`" . implode('`, `', $fields) . "`) VALUES ";
            //$counts = count($data);
            $vals = [];
            $params = [];
            foreach ($data as $d) {
                $counts = count($d);
                $vals[] = "(" . implode(', ', array_fill(0, $counts, '?')) . ")";
                foreach ($d as $v) {
                    $params[] = $v;
                }
            }
            $sth = $this->connection->PDO()->prepare($statement);
            
            var_dump($params);
            $statement .= implode(', ', $vals);
            var_dump($statement);
        }
    }
    
    /**
     * 过滤表中没有的字段
     * @param array $data
     */
    public function filterFields(&$data)
    {
        $fields = $this->connection->getSchema($this->from)->fields;
        foreach ($data as $k=>$v) {
            if (!in_array($k, $fields)) {
                unset($data[$k]);
            }
        }
    }
    
    
}