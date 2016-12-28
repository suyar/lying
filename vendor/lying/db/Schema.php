<?php
namespace lying\db;

class Schema
{
    /**
     * 主键
     * @var array
     */
    public $pk = [];
    
    /**
     * 自增的字段
     * @var string
     */
    public $autoIncrement;
    
    /**
     * 所有字段
     * @var array
     */
    public $fields = [];
    
    /**
     * 初始化表结构
     * @param array $fieldSchema
     */
    public function __construct($fieldSchema)
    {
        foreach ($fieldSchema as $schema) {
            if ($schema['Extra'] === 'auto_increment') {
                $this->autoIncrement = $schema['Field'];
            }
            if ($schema['Key'] === 'PRI') {
                $this->pk[] = $schema['Field'];
            }
            $this->fields[] = $schema['Field'];
        }
    }
}