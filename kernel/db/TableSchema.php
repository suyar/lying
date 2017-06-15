<?php
/**
 * @author carolkey <me@suyaqi.cn>
 * @link https://github.com/carolkey/lying
 * @copyright 2017 Lying
 * @license MIT
 */

namespace lying\db;

class TableSchema
{
    private $columns = [];

    public function __construct($columnsInfo)
    {
        foreach ($columnsInfo as $info) {
            $columnSchema = new ColumnSchema();
        }
    }


}
