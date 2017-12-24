<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\cache;

/**
 * Interface Cache
 * @package lying\cache
 */
interface Cache
{
    /**
     * 添加一个缓存,如果缓存已经存在,此次设置的值不会覆盖原来的值,并返回false
     * @param string $key 缓存的键
     * @param mixed $value 缓存的数据
     * @param int $ttl 缓存生存时间,默认为0
     * @return bool 成功返回true,失败返回false
     */
    public function add($key, $value, $ttl = 0);
    
    /**
     * 添加一组缓存,如果缓存已经存在,此次设置的值不会覆盖原来的值
     * @param array $data 一个关联数组,如['name'=>'lying']
     * @param int $ttl 缓存生存时间,默认为0
     * @return array 返回设置失败的数组,如['name', 'sex'],否则返回空数组
     */
    public function madd($data, $ttl = 0);
    
    /**
     * 添加一个缓存,如果缓存已经存在,此次缓存会覆盖原来的值并且重新设置生存时间
     * @param string $key 缓存的键
     * @param mixed $value 缓存的数据
     * @param int $ttl 缓存生存时间,默认为0
     * @return bool 成功返回true,失败返回false
     */
    public function set($key, $value, $ttl = 0);
    
    /**
     * 添加一组缓存,如果缓存已经存在,此次缓存会覆盖原来的值并且重新设置生存时间
     * @param array $data 一个关联数组,如['name' => 'lying']
     * @param int $ttl 缓存生存时间,默认为0
     * @return array 返回设置失败的数组,如['name', 'sex'],否则返回空数组
     */
    public function mset($data, $ttl = 0);
    
    /**
     * 从缓存中提取存储的变量
     * @param string $key 缓存的键
     * @return mixed 成功返回值,失败返回false
     */
    public function get($key);
    
    /**
     * 从缓存中提取一组存储的变量
     * @param array $keys 缓存的键数组
     * @return array 返回查找到的数据数组,没找到则返回空数组
     */
    public function mget($keys);
    
    /**
     * 检查缓存是否存在
     * @param string $key 要查找的缓存键
     * @return bool 如果键存在,则返回true,否则返回false
     */
    public function exists($key);
    
    /**
     * 从缓存中删除存储的变量
     * @param string $key 从缓存中删除存储的变量
     * @return bool 成功返回true,失败返回false
     */
    public function del($key);
    
    /**
     * 清除所有缓存 
     * @return bool 成功返回true,失败返回false
     */
    public function flush();
}
