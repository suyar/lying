<?php
namespace lying\cache;

use lying\service\Service;

abstract class Cache extends Service
{
    /**
     * 读取缓存
     * @param string $key 缓存的键
     * @return boolean|string 缓存的数据
     */
    abstract public function get($key);
    
    /**
     * 设置缓存
     * @param string $key 缓存的键
     * @param string $data 缓存的数据
     * @param int $exp 缓存时间,如果小等于0则默认为一年
     * @return boolean 设置是否成功
     */
    abstract public function set($key, $data, $exp);
    
    /**
     * 删除一个缓存数据
     * @param string $key 缓存的键
     * @return boolean 删除是否成功
     */
    abstract public function remove($key);
    
    /**
     * 删除过期的缓存
     */
    abstract public function gc();
}

