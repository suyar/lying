<?php
/**
 * @author carolkey <me@suyaqi.cn>
 * @link https://github.com/carolkey/lying
 * @copyright 2017 Lying
 * @license MIT
 */

namespace lying\db;

/**
 * Class ColumnSchema
 * @package lying\db
 *
 * @property string $name
 * @property boolean $allowNull
 * @property boolean $isPrimaryKey
 * @property boolean $autoIncrement
 * @property string $comment
 */
class ColumnSchema
{
    private $name;

    private $allowNull;

    private $isPrimaryKey;

    private $autoIncrement;

    private $comment;

    public function __construct($info)
    {
        $this->name = $info['Field'];
        $this->allowNull = $info['Null'] === 'YES';
        $this->isPrimaryKey = $info['Key'] === 'PRI';
        $this->autoIncrement = $info['Extra'] === 'auto_increment';
        $this->comment = $info['Comment'];
    }

    public function __get($name)
    {
        return $this->$name;
    }
}
