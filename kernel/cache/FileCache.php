<?php
namespace lying\cache;

/**
 * 文件缓存类
 *
 * @author carolkey <me@suyaqi.cn>
 * @since 2.0
 * @link https://carolkey.github.io/
 * @license MIT
 */
class FileCache extends Cache
{
    /**
     * @var string 缓存文件存放的目录，默认为runtime/cache
     */
    protected $dir;
    
    /**
     * @var float 垃圾清除的频率，数值为0到100之间，越小回收的越频繁
     */
    protected $gc = 100;
    
    /**
     * 初始化缓存文件夹，默认为runtime/cache
     */
    protected function init()
    {
        $this->dir = $this->dir ? $this->dir : DIR_RUNTIME . '/cache';
        !is_dir($this->dir) && mkdir($this->dir, 0777, true);
    }
    
    /**
     * 生成缓存文件名
     * @param string $key 键名
     * @return string 生成的文件名
     */
    private function cacheFile($key)
    {
        return $this->dir . '/' . md5($key) . '.bin';
    }
    
    /**
     * 回收垃圾
     * @param boolean $all 是否全部删除
     */
    private function gc($all = false)
    {
        if ($all || mt_rand(0, 100) > $this->gc) {
            foreach (glob($this->dir . '/*.bin') as $file) {
                if ($all) {
                    @unlink($file);
                } elseif (@filemtime($file) < time()) {
                    @unlink($file);
                }
            }
        }
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
        $this->gc();
        return $this->exist($key) ? false : $this->set($key, $value, $ttl);
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
        $this->gc();
        $cacheFile = $this->cacheFile($key);
        if (file_put_contents($cacheFile, serialize($value), LOCK_EX) !== false) {
            return touch($cacheFile, time() + ($ttl > 0 ? $ttl : 31536000));
        }
        return false;
    }

    /**
     * 添加一组缓存，如果缓存已经存在，此次缓存会覆盖原来的值并且重新设置生存时间
     * @param array $data 一个关联数组，如['name' => 'lying']
     * @param integer $ttl 缓存生存时间，默认为0
     * @return array 返回设置失败的数组，如['name', 'sex']，否则返回空数组
     */
    public function mset($data, $ttl = 0)
    {
        $fieldKeys = [];
        foreach ($data as $key => $value) {
            if (false === $this->set($key, $value, $ttl)) {
                $fieldKeys[] = $key;
            }
        }
        return $fieldKeys;
    }

    /**
     * 从缓存中提取存储的变量
     * @param string $key 缓存的键
     * @return boolean 成功返回值，失败返回false
     */
    public function get($key)
    {
        $cacheFile = $this->cacheFile($key);
        if ($this->exist($key) && $fp = @fopen($cacheFile, 'r')) {
            flock($fp, LOCK_SH);
            $value = unserialize(stream_get_contents($fp));
            flock($fp, LOCK_UN);
            fclose($fp);
            return $value;
        }
        return false;
    }

    /**
     * 从缓存中提取一组存储的变量
     * @param array $keys 缓存的键数组
     * @return array 返回查找到的数据数组，没找到则返回空数组
     */
    public function mget($keys)
    {
        $values = [];
        foreach ($keys as $key) {
            if ($this->exist($key)) {
                $values[$key] = $this->get($key);
            }
        }
        return $values;
    }

    /**
     * 检查缓存是否存在
     * @param string $key 要查找的缓存键
     * @return boolean 如果键存在，则返回true，否则返回false
     */
    public function exist($key)
    {
        $cacheFile = $this->cacheFile($key);
        return @filemtime($cacheFile) > time();
    }

    /**
     * 从缓存中删除存储的变量
     * @param string $key 从缓存中删除存储的变量
     * @return boolean 成功返回true，失败返回false
     */
    public function del($key)
    {
        return @unlink($this->cacheFile($key));
    }

    /**
     * 清除所有缓存
     * @return boolean 成功返回true，失败返回false
     */
    public function flush()
    {
        $this->gc(true);
        return true;
    }
}
