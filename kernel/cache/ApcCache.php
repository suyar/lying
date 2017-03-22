<?php
namespace lying\cache;

/**
 * Apc缓存类
 *
 * @author carolkey <me@suyaqi.cn>
 * @since 2.0
 * @link https://carolkey.github.io/
 * @license MIT
 */
class ApcCache extends Cache
{
    /**
     * @var boolean 是否使用Apcu
     */
    protected $apcu = false;

    /**
     * 添加一个缓存，如果缓存已经存在，此次设置的值不会覆盖原来的值，并返回false
     * @param string $key 缓存的键
     * @param mixed $value 缓存的数据
     * @param integer $ttl 缓存生存时间，默认为0
     * @return boolean 成功返回true，失败返回false
     */
    public function add($key, $value, $ttl = 0)
    {
        return $this->apcu ? apcu_add($key, $value, $ttl) : apc_add($key, $value, $ttl);
    }

    /**
     * 添加一组缓存，如果缓存已经存在，此次设置的值不会覆盖原来的值
     * @param array $data 一个关联数组，如['name'=>'lying']
     * @param integer $ttl 缓存生存时间，默认为0
     * @return array 返回设置失败的数组，如['name', 'sex']，否则返回空数组
     */
    public function madd($data, $ttl = 0)
    {
        $res = $this->apcu ? apcu_add($data, null, $ttl) : apc_add($data, null, $ttl);
        return is_array($res) ? array_keys($res) : [];
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
        return $this->apcu ? apcu_store($key, $value, $ttl) : apc_store($key, $value, $ttl);
    }

    /**
     * 添加一组缓存，如果缓存已经存在，此次缓存会覆盖原来的值并且重新设置生存时间
     * @param array $data 一个关联数组，如['name' => 'lying']
     * @param integer $ttl 缓存生存时间，默认为0
     * @return array 返回设置失败的数组，如['name', 'sex']，否则返回空数组
     */
    public function mset($data, $ttl = 0)
    {
        $res = $this->apcu ? apcu_store($data, null, $ttl) : apc_store($data, null, $ttl);
        return is_array($res) ? array_keys($res) : [];
    }

    /**
     * 从缓存中提取存储的变量
     * @param string $key 缓存的键
     * @return boolean 成功返回值，失败返回false
     */
    public function get($key)
    {
        return $this->apcu ? apcu_fetch($key) : apc_fetch($key);
    }

    /**
     * 从缓存中提取一组存储的变量
     * @param array $keys 缓存的键数组
     * @return array 返回查找到的数据数组，没找到则返回空数组
     */
    public function mget($keys)
    {
        return $this->apcu ? apcu_fetch($keys) : apc_fetch($keys);
    }

    /**
     * 检查缓存是否存在
     * @param string $key 要查找的缓存键
     * @return boolean 如果键存在，则返回true，否则返回false
     */
    public function exist($key)
    {
        return $this->apcu ? apcu_exists($key) : apc_exists($key);
    }

    /**
     * 从缓存中删除存储的变量
     * @param string $key 从缓存中删除存储的变量
     * @return boolean 成功返回true，失败返回false
     */
    public function del($key)
    {
        return $this->apcu ? apcu_delete($key) : apc_delete($key);
    }

    /**
     * 清除所有缓存
     * @return boolean 成功返回true，失败返回false
     */
    public function flush()
    {
        return $this->apcu ? apcu_clear_cache() : apc_clear_cache('user');
    }
}
