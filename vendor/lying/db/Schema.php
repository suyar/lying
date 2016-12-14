<?php
namespace lying\db;

class Schema
{
    public $pk = [];
    
    public $autoIncrement;
    
    public $fields = [];
    
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