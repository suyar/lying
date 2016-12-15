<?php
namespace lying\db;

class QueryBuilder
{
    /**
     * @var Connection
     */
    private $connection;
    
    
    private $from;
    
    private $select = "";
    
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
     * 设置要查询的字段
     * @param array $fields
     */
    public function select($fields)
    {
        $this->select = array_intersect($fields, $this->connection->getSchema($this->from));
        if (!$this->select) {
            $this->select = "*";
        }
    }
    
    
    /**
     * 插入一条数据
     * @param array $data 接收一个关联数组,其中键为字段名,值为字段值.注:键名不在表的字段的数据将被过滤掉.
     * @return boolean 成功返回true,失败返回false
     */
    public function insert($data)
    {
        $this->filterData($data);
        $keys = array_keys($data);
        $placeholder = array_fill(0, count($data), '?');
        $statement = "INSERT INTO `$this->from` (`" . implode('`, `', $keys) . "`) VALUES (" . implode(', ', $placeholder) . ")";
        return $this->connection->PDO()->prepare($statement)->execute(array_values($data));
    }
    
    /**
     * 批量插入数据
     * ```
     * $db->createQuery()->from('user')->batchInsert(['username', 'password'], [
     *     ['q', 'qqq'],
     *     ['w', 'www'],
     *     ['e', 'eee'],
     * ]);
     * ```
     * @param array $fields 要插入的字段,非表中的字段会被过滤掉.
     * @param array $data 要插入的数据,一个二维数组.数组的键值是什么病没有关系,但是第二维的数组的数量应该和字段的数量一致.
     * @return boolean
     */
    public function batchInsert($fields, $data)
    {
        $fields = array_intersect($fields, $this->connection->getSchema($this->from)->fields);
        $statement = "INSERT INTO `$this->from` (`" . implode('`, `', $fields) . "`) VALUES ";
        $fieldNum = count($fields);
        $placeholder = "(" . implode(', ', array_fill(0, $fieldNum, '?')) . ")";
        $params = $placeholders = [];
        foreach ($data as $d) {
            $placeholders[] = $placeholder;
            foreach ($d as $v) {
                $params[] = $v;
            }
        }
        return $this->connection->PDO()->prepare($statement . implode(', ', $placeholders))->execute($params);
    }
    
    /**
     * 过滤键值非标中字段的数据
     * @param array $data
     */
    public function filterData(&$data)
    {
        $fields = $this->connection->getSchema($this->from)->fields;
        foreach ($data as $k=>$v) {
            if (!in_array($k, $fields)) {
                unset($data[$k]);
            }
        }
    }
    
    
}