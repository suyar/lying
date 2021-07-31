<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\cache;

use lying\service\Service;

/**
 * Class MemCached
 * @package lying\cache
 */
class Memcached extends Service implements Cache
{
    /**
     * @var string 通过persistent_id为每个实例指定唯一的ID,在请求间共享实例
     */
    protected $persistentId;

    /**
     * Memcached服务器连接列表,如:
     * ```php
     * 'servers'=>[
     *     ['127.0.0.1', 11211, 50],
     * ]
     * ```
     * @var array
     */
    protected $servers = [
        ['127.0.0.1', 11211, 1],
    ];
    
    /**
     * @var string Memcached sasl用户名
     */
    protected $username;
    
    /**
     * @var string Memcached sasl密码
     */
    protected $password;

    /**
     * 额外的Memcached选项,如:
     * ```php
     * 'options'=>[
     *     Memcached::OPT_HASH => Memcached::HASH_MURMUR,
     * ]
     * ```
     * @var array
     */
    protected $options = [];
    
    /**
     * @var \Memcached Memcached的实例
     */
    private $_instance;
    
    /**
     * 初始化实例
     */
    protected function init()
    {
        //实例化Memcached
        $this->_instance = $this->persistentId ? new \Memcached($this->persistentId) : new \Memcached();
        $this->_instance->setOption(\Memcached::OPT_TCP_NODELAY, true);
        $this->options && $this->_instance->setOptions($this->options);
        if ($this->username || $this->password) {
            $this->_instance->setOption(\Memcached::OPT_BINARY_PROTOCOL, true);
            $this->_instance->setSaslAuthData($this->username, $this->password);
        }

        //排除Memcached服务器(长连接时)
        $exist = [];
        if ($this->persistentId) {
            foreach ($this->_instance->getServerList() as $s) {
                $exist[$s['host'] . ':' . $s['port']] = true;
            }
        }

        //向服务器池中增加服务器
        foreach ($this->servers as $server) {
            isset($server[1]) || ($server[1] = 11211);
            if (empty($exist) || !isset($exist[$server[0] . ':' . $server[1]])) {
                $this->_instance->addServer($server[0], $server[1], isset($server[2]) ? $server[2] : 1);
            }
        }
    }

    /**
     * 添加一个缓存,如果缓存已经存在,此次设置的值不会覆盖原来的值,并返回false
     * @param string $key 缓存的键
     * @param mixed $value 缓存的数据
     * @param int $ttl 缓存生存时间,默认为0
     * @return bool 成功返回true,失败返回false
     */
    public function add($key, $value, $ttl = 0)
    {
        return $this->_instance->add($key, $value, $ttl > 0 ? (time() + $ttl) : 0);
    }

    /**
     * 添加一组缓存,如果缓存已经存在,此次设置的值不会覆盖原来的值
     * @param array $data 一个关联数组,如['name'=>'lying']
     * @param int $ttl 缓存生存时间,默认为0
     * @return array 返回设置失败的数组,如['name', 'sex'],否则返回空数组
     */
    public function madd($data, $ttl = 0)
    {
        $fieldKeys = [];
        foreach ($data as $key => $value) {
            if (false === $this->add($key, $value, $ttl)) {
                $fieldKeys[] = $key;
            }
        }
        return $fieldKeys;
    }

    /**
     * 添加一个缓存,如果缓存已经存在,此次缓存会覆盖原来的值并且重新设置生存时间
     * @param string $key 缓存的键
     * @param mixed $value 缓存的数据
     * @param int $ttl 缓存生存时间,默认为0
     * @return bool 成功返回true,失败返回false
     */
    public function set($key, $value, $ttl = 0)
    {
        return $this->_instance->set($key, $value, $ttl > 0 ? (time() + $ttl) : 0);
    }

    /**
     * 添加一组缓存,如果缓存已经存在,此次缓存会覆盖原来的值并且重新设置生存时间
     * @param array $data 一个关联数组,如['name' => 'lying']
     * @param int $ttl 缓存生存时间,默认为0
     * @return array 返回设置失败的数组,如['name', 'sex'],否则返回空数组
     */
    public function mset($data, $ttl = 0)
    {
        return $this->_instance->setMulti($data, $ttl > 0 ? (time() + $ttl) : 0) ? [] : array_keys($data);
    }

    /**
     * 从缓存中提取存储的变量
     * @param string $key 缓存的键
     * @return mixed 成功返回值,失败返回false
     */
    public function get($key)
    {
        return $this->_instance->get($key);
    }

    /**
     * 从缓存中提取一组存储的变量
     * @param array $keys 缓存的键数组
     * @return array 返回查找到的数据数组,没找到则返回空数组
     */
    public function mget($keys)
    {
        return $this->_instance->getMulti($keys);
    }

    /**
     * 检查缓存是否存在
     * @param string $key 要查找的缓存键
     * @return bool 如果键存在,则返回true,否则返回false
     */
    public function exists($key)
    {
        $this->get($key);
        return $this->_instance->getResultCode() !== \Memcached::RES_NOTFOUND;
    }

    /**
     * 从缓存中删除存储的变量
     * @param string $key 从缓存中删除存储的变量
     * @return bool 成功返回true,失败返回false
     */
    public function del($key)
    {
        return $this->_instance->delete($key);
    }

    /**
     * 清除所有缓存
     * @return bool 成功返回true,失败返回false
     */
    public function flush()
    {
        return $this->_instance->flush();
    }
}
