<?php
namespace lying\cache;

/**
 * Memcached缓存类
 *
 * @author carolkey <me@suyaqi.cn>
 * @since 2.0
 * @link https://carolkey.github.io/
 * @license MIT
 */
class MemCached extends Cache
{
    /**
     * Memcached服务器连接列表
     * 'servers'=>[
     *     ['127.0.0.1', 11211, 50],
     * ]
     * @var array
     */
    protected $servers = [];
    
    /**
     * @var string Memcached sasl用户名
     */
    protected $username;
    
    /**
     * @var string Memcached sasl密码
     */
    protected $password;
    
    /**
     * @var \Memcached Memcache的实例
     */
    private $instance;
    
    /**
     * 初始化缓存服务器
     */
    protected function init()
    {
        $this->addServers($this->instance());
    }
    
    /**
     * 添加缓存服务器
     * @param \Memcached $instance
     * @return boolean 成功时返回true，或者在失败时返回false
     */
    private function addServers(\Memcached $instance)
    {
        $exist = [];
        foreach ($instance->getServerList() as $ex) {
            $exist[] = $ex['host'] . ':' . $ex['port'];
        }
        $servers = [];
        foreach ($this->servers as $server) {
            if (!in_array($server[0] . ':' . $server[1], $exist)) {
                $servers[] = $server;
            }
        }
        return $instance->addServers($servers);
    }
    
    /**
     * 返回Memcached的实例
     * @return \Memcached
     */
    private function instance()
    {
        if ($this->instance === null) {
            $this->instance = new \Memcached();
            if ($this->username || $this->password) {
                $this->instance->setSaslAuthData($this->username, $this->password);
            }
            $this->instance->setOptions([
                \Memcached::OPT_DISTRIBUTION => \Memcached::DISTRIBUTION_CONSISTENT,
                \Memcached::OPT_LIBKETAMA_COMPATIBLE => true,
            ]);
        }
        return $this->instance;
    }

    /**
     * 添加一个缓存，如果缓存已经存在，此次设置的值不会覆盖原来的值，并返回false
     * @param string $key 缓存的键
     * @param mixed $value 缓存的数据
     * @param integer $ttl 缓存生存时间，默认为0
     * @return boolean 成功返回true，失败返回false
     */
    public function add($key, $value, $ttl = 0)
    {
        return $this->instance->add($key, $value, $ttl > 0 ? time() + $ttl : 0);
    }

    /**
     * 添加一组缓存，如果缓存已经存在，此次设置的值不会覆盖原来的值
     * @param array $data 一个关联数组，如['name'=>'lying']
     * @param integer $ttl 缓存生存时间，默认为0
     * @return array 返回设置失败的数组，如['name', 'sex']，否则返回空数组
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
     * 添加一个缓存，如果缓存已经存在，此次缓存会覆盖原来的值并且重新设置生存时间
     * @param string $key 缓存的键
     * @param mixed $value 缓存的数据
     * @param integer $ttl 缓存生存时间，默认为0
     * @return boolean 成功返回true，失败返回false
     */
    public function set($key, $value, $ttl = 0)
    {
        return $this->instance->set($key, $value, $ttl > 0 ? time() + $ttl : 0);
    }

    /**
     * 添加一组缓存，如果缓存已经存在，此次缓存会覆盖原来的值并且重新设置生存时间
     * @param array $data 一个关联数组，如['name' => 'lying']
     * @param integer $ttl 缓存生存时间，默认为0
     * @return array 返回设置失败的数组，如['name', 'sex']，否则返回空数组
     */
    public function mset($data, $ttl = 0)
    {
        $res = $this->instance->setMulti($data, $ttl > 0 ? time() + $ttl : 0);
        return $res === false ? array_keys($data) : [];
    }

    /**
     * 从缓存中提取存储的变量
     * @param string $key 缓存的键
     * @return boolean 成功返回值，失败返回false
     */
    public function get($key)
    {
        return $this->instance->get($key);
    }

    /**
     * 从缓存中提取一组存储的变量
     * @param array $keys 缓存的键数组
     * @return array 返回查找到的数据数组，没找到则返回空数组
     */
    public function mget($keys)
    {
        $res = $this->instance->getMulti($keys);
        return $res === false ? [] : $res;
    }

    /**
     * 检查缓存是否存在
     * @param string $key 要查找的缓存键
     * @return boolean 如果键存在，则返回true，否则返回false
     */
    public function exist($key)
    {
        $this->get($key);
        return $this->instance->getResultCode() === \Memcached::RES_SUCCESS;
    }

    /**
     * 从缓存中删除存储的变量
     * @param string $key 从缓存中删除存储的变量
     * @return boolean 成功返回true，失败返回false
     */
    public function del($key)
    {
        return $this->instance->delete($key);
    }

    /**
     * 清除所有缓存
     * @return boolean 成功返回true，失败返回false
     */
    public function flush()
    {
        return $this->instance->flush();
    }
}
