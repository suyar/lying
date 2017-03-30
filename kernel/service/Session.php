<?php
namespace lying\service;

/**
 * SESSION组件
 *
 * @author carolkey <me@suyaqi.cn>
 * @since 2.0
 * @link https://carolkey.github.io/
 * @license MIT
 */
class Session extends Service implements \ArrayAccess
{
    /**
     * 组件初始化的时候启用SESSION
     */
    protected function init()
    {
        $this->start();
    }

    /**
     * SESSION是否启用
     * @return bool
     */
    public function isActive()
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    /**
     * 启用SESSION
     * @return bool
     */
    public function start()
    {
        return $this->isActive() ? true : session_start();
    }

    /**
     * 设置SESSION
     * @param string $name
     * @param mixed $value
     */
    public function set($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    /**
     * 获取SESSION
     * @param string $name
     * @return mixed
     */
    public function get($name)
    {
        return $this->exists($name) ? $_SESSION[$name] : null;
    }

    /**
     * SESSION是否存在
     * @param string $name
     * @return bool
     */
    public function exists($name)
    {
        return isset($_SESSION[$name]);
    }

    /**
     * 移除SESSION
     * @param string $name 放空移除整个SESSION数组
     * @return bool
     */
    public function remove($name = null)
    {
        if (empty($name)) {
            $_SESSION = [];
            return true;
        } elseif ($this->exists($name)) {
            unset($_SESSION[$name]);
            return true;
        }
        return false;
    }

    /**
     * 销毁SESSION,销毁后要重新start
     * @return bool
     */
    public function destroy()
    {
        setcookie(session_name(), '', time() - 86400);
        $this->remove();
        return session_destroy();
    }
    
    /**
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }
    
    /**
     * @param string $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return $this->exists($offset);
    }
    
    /**
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }
    
    /**
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }
}
