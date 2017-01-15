<?php
namespace lying\service;

class Session
{
    /**
     * 开启session
     */
    public function start()
    {
        if (!$this->isActive()) {
            session_start();
        }
    }
    
    /**
     * session是否已经启用
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
        $this->start();
        $_SESSION[$key] = $value;
    }
    
    /**
     * 获取session
     * @param $key 键
     * @return mixed
     */
    public function get($key)
    {
        $this->start();
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
        session_unset();
    }
    
    /**
     * 销毁session
     */
    public function destroy()
    {
        if ($this->isActive()) {
            session_unset();
            session_destroy();
        }
    }
}
