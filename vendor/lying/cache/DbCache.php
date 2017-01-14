<?php
namespace lying\cache;

class DbCache extends Cache
{
    /**
     * @var \lying\db\Connection 数据库连接实例
     */
    protected $connection = 'db';
    
    /**
     * @var string 缓存的表名
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
        $query = $this->connection->createQuery();
        return $this->exist($key) ? $query->update($this->table, [
            'expire' => $expiration > 0 ? time() + $expiration : 0,
            'data' => serialize($data),
        ], ['key' => $key]) : $query->insert($this->table, [
            'key' => $key,
            'expire' => $expiration > 0 ? time() + $expiration : 0,
            'data' => serialize($data),
        ]);
    }
    
    /**
     * @see \lying\cache\Cache::get()
     */
    public function get($key)
    {
        return unserialize($this->connection->createQuery()
        ->select(['data'])
        ->from([$this->table])
        ->where([
            'key' => $key,
            ['or', 'expire' => 0, ['>', 'expire', time()]],
        ])
        ->column());
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
        return $this->connection->createQuery()->select(['key'])
        ->from([$this->table])
        ->where([
            'key' => $key,
            ['or', 'expire' => 0, ['>', 'expire', time()]],
        ])
        ->one() ? true : false;
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
