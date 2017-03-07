<?php
namespace lying\service;

class Session extends Service implements \ArrayAccess
{
    /**
     * 实例化的时候启用SESSION
     */
    protected function init()
    {
        $this->active();
    }
    
    /**
     * 启用SESSION
     */
    public function active()
    {
        session_status() == PHP_SESSION_ACTIVE ? true : session_start();
    }
    
    /**
     * 设置SESSION
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }
    
    /**
     * SESSION是否存在
     * @param string $key
     */
    public function exists($key)
    {
        return isset($_SESSION[$key]);
    }
    
    /**
     * 获取SESSION
     * @param $key
     * @return mixed 不存在返回null
     */
    public function get($key)
    {
        return $this->exists($key) ? $_SESSION[$key] : null;
    }
    
    /**
     * 移除某个SESSION
     * @param string $key
     */
    public function remove($key)
    {
        if ($this->exists($key)) {
            unset($_SESSION[$key]);
        }
    }
    
    /**
     * 清空SESSION数组
     */
    public function removeAll()
    {
        $_SESSION = [];
    }
    
    /**
     * 销毁SESSION
     */
    public function destroy()
    {
        setcookie(session_name(), '', time() - 1);
        $this->removeAll();
        session_destroy();
    }
    
    /**
     * 设置一个SESSION的值
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }
    
    /**
     * 检查一个SESSION是否存在
     * @param string $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return $this->exists($offset);
    }
    
    /**
     * 删除一个SESSION的值
     * @param string $offset
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }
    
    /**
     * 获取一个SESSION的值
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }
}
