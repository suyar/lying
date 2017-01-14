<?php
namespace lying\cache;

use lying\service\Service;

abstract class Cache extends Service
{
    /**
     * 存储一个元素
     * @param string $key 键名
     * @param mixed $data 数据
     * @param integer $expiration 有效时间,默认为0
     * @return boolean 成功返回tuue,失败返回false
     */
    abstract public function set($key, $data, $expiration = 0);
    
    /**
     * 检索一个元素
     * @param string $key 键名
     * @return mixed 返回存储的值,失败返回false
     */
    abstract public function get($key);
    
    /**
     * 批量设置缓存
     * @param array $data 一个key=>value形式的数组
     * @param integer $expiration 有效时间,默认为0
     * @return boolean 成功返回true,失败返回false或者未成功的键的数组,具体请参考各个缓存类的注释
     */
    abstract public function mset($data, $expiration = 0);
    
    /**
     * 批量读取缓存
     * @param array $keys 检索key的数组
     * @return array|boolean 成功返回元素的数组,失败返回false
     */
    abstract public function mget($keys);
    
    /**
     * 检索一个键名是否存在(过期为不存在),与值无关;
     * 此函数可以区分值为false和取值失败,值为false也可能返回true
     * @param string $key 键名
     * @return boolean 存在返回true,不存在或者失败返回false
     */
    abstract public function exist($key);
    
    /**
     * 删除一个缓存
     * @param string $key 键名
     * @return boolean 成功返回true,失败或者不存在返回false
     */
    abstract public function del($key);
    
    /**
     * 重新设置有效时间
     * @param string $key 键名
     * @param integer $expiration 有效时间,默认为0
     * @return boolean 成功返回true,失败返回false
     */
    abstract public function touch($key, $expiration = 0);
    
    /**
     * 删除所有缓存
     * @return boolean 成功返回true,失败返回false
     */
    abstract public function flush();
}
