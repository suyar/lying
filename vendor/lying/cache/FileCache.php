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
     * 缓存清除的几率
     * @var float
     */
    protected $gc = 0.5;
    
    protected function init()
    {
        $this->dir = $this->dir ? $this->dir : DIR_RUNTIME . '/cache';
        !is_dir($this->dir) && mkdir($this->dir, 0777, true);
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
                return $cacheValue;
            }
        }
        return false;
    }
    
    /**
     * @see \lying\cache\Cache::set()
     */
    public function set($key, $data, $exp)
    {
        $this->gc();
        $cacheFile = $this->dir . '/' . md5($key) . '.bin';
        if (file_put_contents($cacheFile, $data, LOCK_EX) !== false) {
            return touch($cacheFile, time() + ($exp > 0 ? $exp : time()) );
        }
        return false;
    }
    
    /**
     * @see \lying\cache\Cache::remove()
     */
    public function remove($key)
    {
        $cacheFile = $this->dir . '/' . md5($key) . '.bin';
        return file_exists($cacheFile) && unlink($cacheFile);
    }
    
    /**
     * @see \lying\cache\Cache::gc()
     */
    public function gc()
    {
        if (mt_rand(0, 10000) < $this->gc * 10000 && $arr = glob($this->dir . '/*.bin')) {
            foreach ($arr as $f) {
                if (filemtime($f) < time()) {
                    unlink($f);
                }
            }
        }
    }
}