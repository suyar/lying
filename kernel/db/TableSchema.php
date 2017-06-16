<?php
/**
 * @author carolkey <me@suyaqi.cn>
 * @link https://github.com/carolkey/lying
 * @copyright 2017 Lying
 * @license MIT
 */

namespace lying\db;

/**
 * Class TableSchema
 * @package lying\db
 *
 * @property string $name
 * @property array $columns
 * @property array $primaryKeys
 */
class TableSchema
{
    /**
     * @var string 表名
     */
    private $name;

    /**
     * @var array 表中所有字段
     */
    private $columns = [];

    /**
     * @var array 所有的主键
     */
    private $primaryKeys = [];

    /**
     * TableSchema constructor.
     * @param string $name 表名
     * @param array $columnsInfo 表结构查询结果
     */
    public function __construct($name, $columnsInfo)
    {
        $this->name = $name;
        foreach ($columnsInfo as $info) {
            $this->columns[] = $info['Field'];
            if ($info['Key'] === 'PRI') {
                $this->primaryKeys[] = $info['Field'];
            }
        }
    }

    /**
     * 获取私有属性的值
     * @param string $name 属性
     * @return string
     */
    public function __get($name)
    {
        return $this->name;
    }
}
