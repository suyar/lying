<?php
namespace lying\cache;

use lying\service\Service;

abstract class Cache extends Service
{
    /**
     * 添加一个缓存,如果缓存已经存在,此次设置的值不会覆盖原来的值,并返回false
     * @param string $key 缓存ID
     * @param mixed $value 缓存的数据
     * @param integer $ttl 缓存生存时间,默认为0
     * @return boolean 成功返回true,失败返回false
     */
    abstract public function add($key, $value, $ttl = 0);
    
    /**
     * 添加一组缓存,如果缓存已经存在,此次设置的值不会覆盖原来的值
     * @param array $data 一个关联数组,e.g. ['name' => 'lying']
     * @param integer $ttl 缓存生存时间,默认为0
     * @return array 返回设置失败的数组,e.g. ['name', 'sex'],否则返回空数组
     */
    abstract public function madd($data, $ttl = 0);
    
    /**
     * 添加一个缓存,如果缓存已经存在,此次缓存会覆盖原来的值并且重新设置生存时间
     * @param string $key 缓存ID
     * @param mixed $value 缓存的数据
     * @param integer $ttl 缓存生存时间,默认为0
     * @return boolean 成功返回true,失败返回false
     */
    abstract public function set($key, $value, $ttl = 0);
    
    /**
     * 添加一组缓存,如果缓存已经存在,此次缓存会覆盖原来的值并且重新设置生存时间
     * @param array $data 一个关联数组,e.g. ['name' => 'lying']
     * @param integer $ttl 缓存生存时间,默认为0
     * @return array 返回设置失败的数组,e.g. ['name', 'sex'],否则返回空数组
     */
    abstract public function mset($data, $ttl = 0);
    
    /**
     * 从缓存中提取存储的变量
     * @param string $key 缓存ID
     * @return boolean 成功返回true,失败返回false
     */
    abstract public function get($key);
    
    /**
     * 从缓存中提取一组存储的变量
     * @param array $key 缓存ID数组
     * @return array 返回查找到的数据数组,没找到则返回空数组
     */
    abstract public function mget($keys);
    
    /**
     * 检查缓存是否存在
     * @param string $key 要查找的缓存ID
     * @return boolean 如果键存在,则返回true,否则返回false
     */
    abstract public function exist($key);
    
    /**
     * 从缓存中删除存储的变量
     * @param string $key 从缓存中删除存储的变量
     * @return boolean 成功返回true,失败返回false
     */
    abstract public function del($key);
    
    /**
     * 清除所有缓存 
     * @return boolean 成功返回true,失败返回false
     */
    abstract public function flush();
}
