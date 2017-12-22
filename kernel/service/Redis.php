<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\service;

/**
 * Class Redis
 * @package lying\service
 */
class Redis extends \Redis
{
    /**
     * @var string resid主机
     */
    protected $host = '127.0.0.1';

    /**
     * @var int redis端口
     */
    protected $port = 6379;

    /**
     * @var int 连接超时时间
     */
    protected $timeout = 0;

    /**
     * @var string 长连接ID
     */
    protected $persistentId;

    /**
     * @var string 密码
     */
    protected $password;

    /**
     * @var int 设置当前连接的库
     */
    protected $select = 0;

    /**
     * @var string 键前缀
     */
    protected $prefix;

    /**
     * Redis constructor.
     * @param array $params
     */
    final public function __construct($params = [])
    {
        foreach ($params as $key=>$param) {
            $this->$key = $param;
        }
        if ($this->persistentId) {
            $this->pconnect($this->host, $this->port, $this->timeout, $this->persistentId);
        } else {
            $this->connect($this->host, $this->port, $this->timeout);
        }
        $this->password && $this->auth($this->password);
        $this->select && $this->select($this->select);
        $this->prefix && $this->setOption(self::OPT_PREFIX, $this->prefix);
    }
}
