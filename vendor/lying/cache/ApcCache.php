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
     */
    public function mset($data, $expiration = 0)
    {
        $res = $this->apcu ? apcu_store($data, null, $expiration) : apc_store($data, null, $expiration);
        
    }
    
    /**
     * @see \lying\cache\Cache::mget()
     */
    public function mget($keys)
    {
        $values = [];
        foreach ($keys as $k) {
            $values[] = $this->get($k);
        }
        return $values ? $values : false;
    }
    
    /**
     * @see \lying\cache\Cache::exist()
     */
    public function exist($key)
    {
        $cacheFile = $this->cacheFile($key);
        return file_exists($cacheFile) && filemtime($cacheFile) > time();
    }
    
    /**
     * @see \lying\cache\Cache::del()
     */
    public function del($key)
    {
        $cacheFile = $this->cacheFile($key);
        return file_exists($cacheFile) && unlink($cacheFile);
    }
    
    /**
     * @see \lying\cache\Cache::touch()
     */
    public function touch($key, $expiration = 0)
    {
        $cacheFile = $this->cacheFile($key);
        return file_exists($cacheFile) && touch($cacheFile, time() + ($expiration > 0 ? $expiration : 31536000));
    }
    
    /**
     * @see \lying\cache\Cache::flush()
     */
    public function flush()
    {
        foreach (glob($this->dir . '/*.bin') as $file) {
            if (filemtime($file) < time()) {
                unlink($file);
            }
        }
        return true;
    }
}