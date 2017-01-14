<?php
namespace lying\cache;

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
     * @var array 额外的Memcached的选项
     */
    protected $options = [];
    
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
     * @return boolean 成功时返回true,或者在失败时返回false
     */
    private function addServers(\Memcached $instance)
    {
        $exist = [];
        foreach ($this->instance->getServerList() as $ex) {
            $exist[] = $ex['host'].':'.$ex['port'];
        }
        $servers = [];
        foreach ($this->servers as $server) {
            if (!in_array($server[0].':'.$server[1], $exist)) {
                $servers[] = $server;
            }
        }
        return $this->instance->addServers($servers);
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
                \Memcached::OPT_DISTRIBUTION=>\Memcached::DISTRIBUTION_CONSISTENT,
                \Memcached::OPT_LIBKETAMA_COMPATIBLE=>true,
            ]);
            $this->instance->setOptions($this->options);
        }
        return $this->instance;
    }
    
    /**
     * @see \lying\cache\Cache::set()
     */
    public function set($key, $data, $expiration = 0)
    {
        return $this->instance->set($key, $data, $expiration > 0 ? time() + $expiration : 0);
    }
    
    /**
     * @see \lying\cache\Cache::get()
     */
    public function get($key)
    {
        return $this->instance->get($key);
    }
    
    /**
     * @see \lying\cache\Cache::mset()
     */
    public function mset($data, $expiration = 0)
    {
        return $this->instance->setMulti($data, $expiration > 0 ? time() + $expiration : 0);
    }
    
    /**
     * @see \lying\cache\Cache::mget()
     */
    public function mget($keys)
    {
        return $this->instance->getMulti($keys);
    }
    
    /**
     * @see \lying\cache\Cache::exist()
     */
    public function exist($key)
    {
        $this->get($key);
        return $this->instance->getResultCode() === \Memcached::RES_SUCCESS;
    }
    
    /**
     * @see \lying\cache\Cache::del()
     */
    public function del($key)
    {
        return $this->instance->delete($key);
    }
    
    /**
     * @see \lying\cache\Cache::touch()
     */
    public function touch($key, $expiration = 0)
    {
        return $this->instance->touch($key, $expiration > 0 ? time() + $expiration : 0);
    }
    
    /**
     * @see \lying\cache\Cache::flush()
     */
    public function flush()
    {
        return $this->instance->flush();
    }
}
