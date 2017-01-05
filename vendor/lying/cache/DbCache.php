<?php
namespace lying\cache;

class DbCache extends Cache
{
    /**
     * 数据库连接
     * @var \lying\db\Connection
     */
    protected $connection;
    
    /**
     * 缓存的表名
     * @var string
     */
    protected $table = 'cache';
    
    /**
     * 设置数据库连接实例
     */
    protected function init()
    {
        $this->connection = maker()->db($this->connection);
    }
    
    /**
     * @see \lying\cache\Cache::set()
     */
    public function set($key, $data, $expiration = 0)
    {
        
    }
    
    /**
     * @see \lying\cache\Cache::get()
     */
    public function get($key)
    {
        
    }
    
    /**
     * @see \lying\cache\Cache::mset()
     * @return array 返回设置失败的键值数组
     */
    public function mset($data, $expiration = 0)
    {
        
    }
    
    /**
     * @see \lying\cache\Cache::mget()
     */
    public function mget($keys)
    {
        
    }
    
    /**
     * @see \lying\cache\Cache::exist()
     */
    public function exist($key)
    {
        
    }
    
    /**
     * @see \lying\cache\Cache::del()
     */
    public function del($key)
    {
        
    }
    
    /**
     * @see \lying\cache\Cache::touch()
     */
    public function touch($key, $expiration = 0)
    {
        
    }
    
    /**
     * @see \lying\cache\Cache::flush()
     */
    public function flush()
    {
        
    }
}