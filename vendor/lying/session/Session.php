<?php
namespace lying\session;

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
     * 是否启用
     * @return boolean
     */
    public function isActive()
    {
        return session_status() == PHP_SESSION_ACTIVE;
    }
    
    /**
     * 设置session
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->start();
        $_SESSION[$key] = $value;
    }
    
    /**
     * 获取session
     * @param $key
     * @return Ambigous <NULL, unknown>
     */
    public function get($key)
    {
        $this->start();
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }
    
    /**
     * 移除session
     * @param string $key
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
     * 销毁session
     */
    public function destroy()
    {
        if ($this->isActive()) {
            session_destroy();
        }
    }
}