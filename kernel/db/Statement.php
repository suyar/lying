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
     * @var bool 是否强制使用主库
     */
    private $useMaster = false;

    /**
     * @var array 还没有绑定的参数
     */
    private $_toBind = [];

    /**
     * 初始化的时候处理SQL语句
     */
    protected function init()
    {
        parent::init();
        $this->sql = $this->db->schema()->quoteSql($this->sql);
    }

    /**
     * 使用主库
     * @param bool $useMaster 是否使用主库,默认true
     * @return $this
     */
    public function useMaster($useMaster = true)
    {
        $this->useMaster = $useMaster;
        return $this;
    }

    /**
     * 预处理语句
     * @param bool $isRead 是否为从库,默认true
     */
    protected function prepare($isRead = true)
    {
        if ($this->_statement === null) {
            if ($this->useMaster || $isRead == false) {
                $this->_statement = $this->db->masterPdo()->prepare($this->sql);
            } else {
                $this->_statement = $this->db->slavePdo()->prepare($this->sql);
            }
        }

    }

    public function bindValue($name, $value, $dataType = null)
    {

    }

    public function bindParam()
    {

    }





}
