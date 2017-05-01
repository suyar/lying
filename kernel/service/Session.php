<?php
namespace lying\service;

/**
 * SESSION组件
 *
 * @author carolkey <me@suyaqi.cn>
 * @since 2.0
 * @link https://github.com/carolkey/lying
 * @license MIT
 */
class Session
{
    /**
     * 组件初始化的时候启用SESSION
     */
    public function __construct()
    {
        $this->start();
    }

    /**
     * SESSION是否启用
     * @return boolean 已启用返回true，未启用返回false
     */
    public function isActive()
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    /**
     * 启用SESSION
     * @return boolean 成功返回true，失败返回false
     */
    public function start()
    {
        return $this->isActive() ? true : session_start();
    }

    /**
     * 设置SESSION
     * @param string $name SESSION键名
     * @param mixed $value SESSION值
     */
    public function set($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    /**
     * 获取SESSION
     * @param string $name SESSION键名
     * @return mixed SESSION值，失败返回null
     */
    public function get($name)
    {
        return $this->exists($name) ? $_SESSION[$name] : null;
    }

    /**
     * SESSION是否存在
     * @param string $name SESSION键名
     * @return boolean 存在返回true，失败返回false
     */
    public function exists($name)
    {
        return isset($_SESSION[$name]);
    }

    /**
     * 移除SESSION
     * @param string $name 放空移除整个SESSION数组
     * @return boolean 成功返回true，失败返回false
     */
    public function remove($name = null)
    {
        if ($name === null) {
            $_SESSION = [];
            return true;
        } elseif ($this->exists($name)) {
            unset($_SESSION[$name]);
            return true;
        }
        return false;
    }

    /**
     * 销毁SESSION，销毁后要重新start
     * @return boolean 成功返回true，失败返回false
     */
    public function destroy()
    {
        setcookie(session_name(), '', time() - 31536000);
        $this->remove();
        return session_destroy();
    }
}
