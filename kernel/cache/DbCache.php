<?php
namespace lying\cache;

/**
 * 数据库缓存类
 *
 * @author carolkey <me@suyaqi.cn>
 * @since 2.0
 * @link https://carolkey.github.io/
 * @license MIT
 */
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
     * @var float 垃圾清除的频率，数值为0到100之间，越小回收的越频繁
     */
    protected $gc = 50;
    
    /**
     * 设置数据库连接实例
     */
    protected function init()
    {
        $this->connection = \Lying::$maker->db($this->connection);
    }
    
    /**
     * 回收垃圾
     * @param boolean $all 是否全部删除
     */
    public function gc($all = false)
    {
        if ($all || mt_rand(0, 100) > $this->gc) {
            $query = $this->connection->createQuery();
            $all ? $query->delete($this->table) : $query->delete($this->table, [
                ['>', 'expire', 0],
                ['<', 'expire', time()]
            ]);
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
        $query = $this->connection->createQuery();
        $res = $query->select('key')->from([$this->table])->where(['key' => $key])->one();
        $res = $res ? $query->update($this->table, [
            'expire' => $ttl > 0 ? time() + $ttl : 0,
            'data' => serialize($value),
        ], ['key' => $key]) : $query->insert($this->table, [
            'key' => $key,
            'expire' => $ttl > 0 ? time() + $ttl : 0,
            'data' => serialize($value),
        ]);
        return $res !== false;
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
     * 从缓存中提取一组存储的变量
     * @param array $keys 缓存的键数组
     * @return array 返回查找到的数据数组，没找到则返回空数组
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
            return [];
        }
        $result = [];
        foreach ($rows as $row) {
            $result[$row['key']] = unserialize($row['data']);
        }
        return $result;
    }

    /**
     * 检查缓存是否存在
     * @param string $key 要查找的缓存键
     * @return boolean 如果键存在，则返回true，否则返回false
     */
    public function exist($key)
    {
        return $this->connection->createQuery()
        ->select(['key'])
        ->from([$this->table])
        ->where([
            'key' => $key,
            ['or', 'expire' => 0, ['>', 'expire', time()]],
        ])
        ->one() ? true : false;
    }

    /**
     * 从缓存中删除存储的变量
     * @param string $key 从缓存中删除存储的变量
     * @return boolean 成功返回true，失败返回false
     */
    public function del($key)
    {
        $res = $this->connection->createQuery()->delete($this->table, ['key' => $key]);
        return $res === false ? false : true;
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
