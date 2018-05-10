<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\service;

/**
 * Class Cookie
 * @package lying\service
 */
class Cookie extends Service
{
    /**
     * @var string COOKIE加密密钥
     */
    protected $key;

    /**
     * 初始化密钥
     */
    public function init()
    {
        $this->key .= self::class;
    }

    /**
     * 设置COOKIE
     * @param string $name COOKIE名称
     * @param mixed $value COOKIE的值
     * @param int $expire 过期时间戳,默认0,浏览器关闭时清除
     * @param string $path COOKIE路径,默认'/'
     * @param string $domain COOKIE域名,默认当前域名
     * @param bool $secure 是否设置仅用HTTPS传输COOKIE,默认false
     * @param bool $httpOnly 是否设置COOKIE只能通过http请求访问,JS将不能访问,默认false
     * @return bool 成功返回true,失败返回false
     */
    public function set($name, $value, $expire = 0, $path = '/', $domain = '', $secure = false, $httpOnly = false)
    {
        if (!headers_sent()) {
            $value = \Lying::$maker->encrypter->xorEncrypt($value, $this->key, $expire);
            return setcookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
        }
        return false;
    }

    /**
     * 检查COOKIE是否设置
     * @param string $name COOKIE名称
     * @return bool COOKIE存在返回true,否则返回false
     */
    public function exists($name)
    {
        return isset($_COOKIE[$name]);
    }
    
    /**
     * 获取COOKIE
     * @param string $name COOKIE名称
     * @return mixed 返回COOKIE的值,失败或不存在返回false
     */
    public function get($name)
    {
        return $this->exists($name) ? \Lying::$maker->encrypter->xorDecrypt($_COOKIE[$name], $this->key) : false;
    }

    /**
     * 删除COOKIE
     * @param string $name COOKIE名称
     * @param string $path COOKIE路径,默认'/'
     * @param string $domain COOKIE域名,默认当前域名
     * @param bool $secure 是否设置仅用HTTPS传输COOKIE,默认false
     * @param bool $httpOnly 是否设置COOKIE只能通过http请求访问,JS将不能访问,默认false
     * @return bool 成功返回true,失败返回false
     */
    public function remove($name, $path = '/', $domain = '', $secure = false, $httpOnly = false)
    {
        return $this->set($name, '', time() - 31536000, $path, $domain, $secure, $httpOnly);
    }
}
