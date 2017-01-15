<?php
namespace lying\cache;

class FileCache extends Cache
{
    /**
     * @var string 缓存文件存放的目录
     */
    protected $dir;
    
    /**
     * @var float 垃圾清除的频率,数值为0到100之间,越小回收的越频繁
     */
    protected $gc = 100;
    
    /**
     * 初始化缓存文件夹,默认为runtime/cache
     */
    protected function init()
    {
        $this->dir = $this->dir ? $this->dir : DIR_RUNTIME . '/cache';
        !is_dir($this->dir) && mkdir($this->dir, 0777, true);
    }
    
    /**
     * 生成缓存文件名
     * @param string $key 键值
     * @return string 生成的文件名
     */
    private function cacheFile($key)
    {
        return $this->dir . '/' . md5($key) . '.bin';
    }
    
    /**
     * 概率回收垃圾
     * @return boolean 回收成功返回true,失败或者未回收返回false
     */
    public function gc()
    {
        if (mt_rand(0, 100) > $this->gc) {
            return $this->flush(true);
        }
        return false;
    }
    
    /**
     * @see \lying\cache\Cache::set()
     */
    public function set($key, $data, $expiration = 0)
    {
        $this->gc();
        $cacheFile = $this->cacheFile($key);
        if (file_put_contents($cacheFile, serialize($data), LOCK_EX) !== false) {
            return touch($cacheFile, time() + ($expiration > 0 ? $expiration : 31536000));
        }
        return false;
    }
    
    /**
     * @see \lying\cache\Cache::get()
     */
    public function get($key)
    {
        $cacheFile = $this->dir . '/' . md5($key) . '.bin';
        if (file_exists($cacheFile) && filemtime($cacheFile) > time()) {
            return unserialize(file_get_contents($cacheFile));
        }
        return false;
    }
    
    /**
     * @see \lying\cache\Cache::mset()
     */
    public function mset($data, $expiration = 0)
    {
        $res = true;
        foreach ($data as $k => $d) {
            $failed = $failed && $this->set($k, $d);
        }
        return $failed;
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
     * @param boolean $gc 是否只回收过期垃圾
     * @see \lying\cache\Cache::flush()
     */
    public function flush($gc = false)
    {
        foreach (glob($this->dir . '/*.bin') as $file) {
            if ($gc) {
                if (filemtime($file) < time()) {
                    unlink($file);
                }
            } else {
                unlink($file);
            }
        }
        return true;
    }
}
