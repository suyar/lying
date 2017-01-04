<?php
namespace lying\cache;

class FileCache extends Cache
{
    /**
     * 缓存文件存放的路径
     * @var string
     */
    protected $dir;
    
    /**
     * 垃圾清除的频率,数值为0到1之间,越小回收的越频繁
     * @var float
     */
    protected $gc = 0.5;
    
    /**
     * 初始化缓存文件夹
     * 默认为runtime/cache
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
        if (mt_rand(0, 10000) > $this->gc * 10000) {
            return $this->flush();
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
            $fp = fopen($cacheFile, 'r');
            if ($fp !== false) {
                flock($fp, LOCK_SH);
                $cacheValue = stream_get_contents($fp);
                flock($fp, LOCK_UN);
                fclose($fp);
                return unserialize($cacheValue);
            }
        }
        return false;
    }
    
    /**
     * @see \lying\cache\Cache::mset()
     * @return boolean 如果有一个失败就返回false,这不能作为是否全部失败的标志
     */
    public function mset($data, $expiration = 0)
    {
        $stat = true;
        foreach ($data as $k => $d) {
            $stat = $stat && $this->set($k, $d);
        }
        return $stat;
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