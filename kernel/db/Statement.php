<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\db;

use lying\service\Service;

/**
 * Class Statement
 * @package lying\db
 */
class Statement extends Service
{
    /**
     * @var Connection 使用的数据库链接
     */
    protected $db;

    /**
     * @var \PDOStatement 当前语句关联的\PDOStatement对象
     */
    private $_statement;

    /**
     * @var array 当前语句绑定的参数
     */
    protected $params = [];

    /**
     * @var string 当前处理的sql语句
     */
    protected $sql;

    /**
     * 初始化的时候处理SQL语句
     */
    protected function init()
    {
        parent::init();
        $this->sql = $this->db->quoteSql($this->sql);
    }


}
