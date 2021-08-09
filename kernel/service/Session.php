<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\service;

/**
 * Class Session
 * @package lying\service
 */
class Session extends Service
{
    /**
     * 判断SESSION是否启用
     * @return bool 返回SESSION是否已经启用
     */
    public function isActive()
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    /**
     * 启用SESSION
     * @return bool 返回SESSION是否成功启用
     */
    public function open()
    {
        return $this->isActive() ?: session_start();
    }

    /**
     * 写入SESSION并关闭SESSION
     * 此操作并不会清空$_SESSION数组,也不会重置会话cookie,如果需要再次使用会话变量,必须重新调用open函数
     * @return bool 返回会话是否成功关闭
     */
    public function close()
    {
        return $this->isActive() && session_write_close();
    }

    /**
     * 销毁会话,不会重置会话cookie,但会清空$_SESSION数组并且关闭会话,如果需要再次使用会话变量,必须重新调用open函数
     * @return bool 返回会话是否成功关闭,如果会话未启用则返回false
     */
    public function destroy()
    {
        if ($this->isActive()) {
            $this->close();
            $this->open();
            session_unset();
            session_destroy();
            return true;
        }
        return false;
    }

    /**
     * 获取SESSION的值
     * @param string $key 键名
     * @param mixed $default 默认值,默认为null
     * @return mixed 返回SESSION的值
     */
    public function get($key, $default = null)
    {
        return $this->exists($key) ? $_SESSION[$key] : $default;
    }

    /**
     * 设置SESSION的值
     * @param string $key 键名
     * @param mixed $value 值
     */
    public function set($key, $value)
    {
        $this->open() && ($_SESSION[$key] = $value);
    }

    /**
     * SESSION是否存在
     * @param string $key 键名
     * @return bool 返回SESSION是否存在
     */
    public function exists($key)
    {
        return $this->open() && isset($_SESSION[$key]);
    }

    /**
     * 移除SESSION
     * @param string $key 要移除的键名,如果key为null,则清空整个$_SESSION数组
     * @return bool 返回SESSION是否成功移除
     */
    public function remove($key = null)
    {
        $this->open();
        if ($key === null) {
            $_SESSION = [];
            return true;
        } elseif (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
            return true;
        }
        return false;
    }

    /**
     * 判断是否在session_start之前
     * @return bool 返回是否可在session_start之前调用
     */
    protected function isBeforeStart()
    {
        return !$this->isActive() && !headers_sent();
    }

    /**
     * 获取/设置新的会话ID
     * @param string $newId 新的会话ID
     * @return bool|string 返回当前会话ID,如果设置会话ID失败,则返回false
     */
    public function id($newId = null)
    {
        if ($newId === null) {
            return session_id();
        } else if ($this->isBeforeStart()) {
            return session_id($newId);
        }
        return false;
    }

    /**
     * 获取/设置新的会话名称
     * @param string $newName 新的会话名称
     * @return bool|string 返回当前会话名称,如果设置会话名称失败,则返回false
     */
    public function name($newName = null)
    {
        if ($newName === null) {
            return session_name();
        } else if ($this->isBeforeStart()) {
            return session_name($newName);
        }
        return false;
    }

    /**
     * 获取/设置当前会话的保存路径
     * @param string $newPath 指定会话数据保存的路径
     * @return bool|string 返回前会话的保存路径,如果设置会话路径失败,则返回false
     */
    public function savePath($newPath = null)
    {
        if ($newPath === null) {
            return session_save_path();
        } else if ($this->isBeforeStart()) {
            return session_save_path($newPath);
        }
        return false;
    }

    /**
     * 获取/设置会话cookie参数
     * @param array $params 会话cookie参数
     * @return array|bool 成功返回会话cookie参数,失败返回false
     */
    public function cookieParams(array $params = null)
    {
        if ($params === null) {
            return session_get_cookie_params();
        } elseif ($this->isBeforeStart()) {
            $data = array_merge($this->cookieParams(), array_change_key_case($params));
            if (isset($data['lifetime'], $data['path'], $data['domain'], $data['secure'], $data['httponly'])) {
                session_set_cookie_params($data['lifetime'], $data['path'], $data['domain'], $data['secure'], $data['httponly']);
                return $data;
            }
        }
        return false;
    }
}
