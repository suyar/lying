<?php
namespace lying\cache;

class ApcCache extends Cache
{
    /**
     * 缓存类型,apc或者apcu
     * @var boolean
     */
    protected $apcu = false;
    
    /**
     * @see \lying\cache\Cache::set()
     */
    public function set($key, $data, $expiration = 0)
    {
        return $this->apcu ? apcu_store($key, $data, $expiration) : apc_store($key, $data, $expiration);
    }
    
    /**
     * @see \lying\cache\Cache::get()
     */
    public function get($key)
    {
        return $this->apcu ? apcu_fetch($key) : apc_fetch($key);
    }
    
    /**
     * @see \lying\cache\Cache::mset()
     * @return array 返回设置失败的键值数组
     */
    public function mset($data, $expiration = 0)
    {
        $res = $this->apcu ? apcu_store($data, null, $expiration) : apc_store($data, null, $expiration);
        return is_array($res) ? array_keys($res) : [];
    }
    
    /**
     * @see \lying\cache\Cache::mget()
     */
    public function mget($keys)
    {
        return $this->apcu ? apcu_fetch($keys) : apc_fetch($keys);
    }
    
    /**
     * @see \lying\cache\Cache::exist()
     */
    public function exist($key)
    {
        return $this->apcu ? apcu_exists($key) : apc_exists($key);
    }
    
    /**
     * @see \lying\cache\Cache::del()
     */
    public function del($key)
    {
        return $this->apcu ? apcu_delete($key) : apc_delete($key);
    }
    
    /**
     * @see \lying\cache\Cache::touch()
     */
    public function touch($key, $expiration = 0)
    {
        return $this->exist($key) !== false ? $this->set($key, $this->get($key), $expiration) : false;
    }
    
    /**
     * @see \lying\cache\Cache::flush()
     */
    public function flush()
    {
        return $this->apcu ? apcu_clear_cache() : apc_clear_cache('user');
    }
}