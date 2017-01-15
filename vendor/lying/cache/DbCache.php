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
     * @var float 垃圾清除的频率,数值为0到100之间,越小回收的越频繁
     */
    protected $gc = 50;
    
    /**
     * 设置数据库连接实例
     * 可以用以下语句来创建表
     */
    protected function init()
    {
        $this->connection = maker()->db($this->connection);
    }
    
    /**
     * 概率回收垃圾
     * @return boolean 回收成功返回true,失败或者未回收返回false
     */
    public function gc()
    {
        if (mt_rand(0, 100) > $this->gc) {
            return $this->flush(true);
        }
        return false;
    }
    
    /**
     * @see \lying\cache\Cache::set()
     */
    public function set($key, $data, $expiration = 0)
    {
        $this->gc();
        $query = $this->connection->createQuery();
        $exist = $query->select('key')->from([$this->table])->where(['key' => $key])->one();
        $exist = $exist ? $query->update($this->table, [
            'expire' => $expiration > 0 ? time() + $expiration : 0,
            'data' => serialize($data),
        ], ['key' => $key]) : $query->insert($this->table, [
            'key' => $key,
            'expire' => $expiration > 0 ? time() + $expiration : 0,
            'data' => serialize($data),
        ]);
        return $exist === false ? false : true;
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
     */
    public function mset($data, $expiration = 0)
    {
        $res = true;
        foreach ($data as $key => $d) {
            $res = $res && $this->set($key, $d, $expiration);
        }
        return $res;
    }
    
    /**
     * @see \lying\cache\Cache::mget()
     */
    public function mget($keys)
    {
        $rows = $this->connection->createQuery()
        ->select(['key', 'data'])
        ->from([$this->table])
        ->where([
            'key' => $keys,
            ['or', 'expire' => 0, ['>', 'expire', time()]],
        ])
        ->all();
        if ($rows === false) {
            return false;
        }
        foreach ($keys as $key) {
            $result[$key] = false;
        }
        foreach ($rows as $row) {
            $result[$row['key']] = unserialize($row['data']);
        }
        return $result;
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
        $res = $this->connection->createQuery()
        ->delete($this->table, ['key' => $key]);
        return $res === false ? false : true;
    }
    
    /**
     * @see \lying\cache\Cache::touch()
     */
    public function touch($key, $expiration = 0)
    {
        $res = $this->connection->createQuery()
        ->update($this->table, ['expire' => $expiration > 0 ? time() + $expiration : 0], [
            'key' => $key
        ]);
        return $res === false ? false : true;
    }
    
    /**
     * @param boolean $gc 是否只回收过期垃圾
     * @see \lying\cache\Cache::flush()
     */
    public function flush($gc = false)
    {
        $query = $this->connection->createQuery();
        $res = $gc ? $query->delete($this->table, [
            ['>', 'expire', 0],
            ['<', 'expire', time()]
        ]) : $query->delete($this->table);
        return $res === false ? false : true;
    }
}
