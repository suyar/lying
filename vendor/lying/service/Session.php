<?php
namespace lying\service;

class Session extends Service implements \ArrayAccess
{
    /**
     * 实例化的时候启用session
     */
    protected function init()
    {
        $this->start();
    }
    
    /**
     * 启用session
     * @return boolean 成功开始会话返回true,反之返回false
     */
    public function start()
    {
        return $this->isActive() ? true : session_start();
    }
    
    /**
     * 检测session是否已经启用
     * @return boolean
     */
    public function isActive()
    {
        return session_status() == PHP_SESSION_ACTIVE;
    }
    
    /**
     * 设置session
     * @param string $key 键
     * @param mixed $value 值
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }
    
    /**
     * 获取session
     * @param $key 键
     * @return mixed
     */
    public function get($key)
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }
    
    /**
     * 移除session
     * @param string $key 键
     * @return mixed|NULL 返回移除的值
     */
    public function remove($key)
    {
        if (isset($_SESSION[$key])) {
            $value = $_SESSION[$key];
            unset($_SESSION[$key]);
            return $value;
        } else {
            return null;
        }
    }
    
    /**
     * 清空session数组
     */
    public function removeAll()
    {
        $_SESSION = [];
    }
    
    /**
     * 销毁session
     */
    public function destroy()
    {
        if ($this->isActive()) {
            setcookie(session_name(), '', time() - 1);
            $this->removeAll();
            session_destroy();
        }
    }
    
    /**
     * 设置一个偏移位置的值
     * {@inheritDoc}
     * @see ArrayAccess::offsetSet()
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }
    
    /**
     * 检查一个偏移位置是否存在
     * {@inheritDoc}
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($offset)
    {
        return isset($_SESSION[$key]);
    }
    
    /**
     * 复位一个偏移位置的值
     * {@inheritDoc}
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }
    
    /**
     * 获取一个偏移位置的值
     * {@inheritDoc}
     * @see ArrayAccess::offsetGet()
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }
}
