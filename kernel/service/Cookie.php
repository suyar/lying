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
    protected $key = '';

    /**
     * @var int 密钥长度
     */
    private $keyLen;

    /**
     * 初始化密钥
     */
    public function init()
    {
        $this->key = strtoupper(sha1($this->key . 'Lying'));
        $this->keyLen = strlen($this->key);
    }

    /**
     * 设置COOKIE
     * @param string $name COOKIE名称
     * @param mixed $value COOKIE的值
     * @param int $expire 过期时间戳,默认0,浏览器关闭时清除
     * @param string $path COOKIE路径,默认'/'
     * @param string $domain COOKIE域名,默认空
     * @param bool $secure 是否设置仅用HTTPS传输COOKIE,默认false
     * @param bool $httpOnly 是否设置COOKIE只能通过http请求访问,JS将不能访问,默认false
     * @return bool 成功返回true,失败返回false
     */
    public function set($name, $value, $expire = 0, $path = '/', $domain = '', $secure = false, $httpOnly = false)
    {
        $value = $this->encrypt(serialize($value), $expire);
        return setcookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
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
        return $this->exists($name) ? unserialize($this->decrypt($_COOKIE[$name])) : false;
    }

    /**
     * 删除COOKIE
     * @param string $name COOKIE名称
     * @param string $path COOKIE路径,默认'/'
     * @param string $domain COOKIE域名,默认空
     * @return bool 成功返回true,失败返回false
     */
    public function remove($name, $path = '/', $domain = '')
    {
        return $this->set($name, '', time() - 31536000, $path, $domain);
    }

    /**
     * 加密
     * @param string $str 要加密的字符串
     * @param int $expire 有效时间戳
     * @return string 加密后的字符串
     */
    private function encrypt($str, $expire = 0)
    {
        $str .= hash_hmac('sha256', $str, $this->key, true) . sprintf('%010d', $expire);
        $result = '';
        for ($i = 0, $strLen = strlen($str); $i < $strLen; $i++) {
            $result .= chr(ord($str[$i]) ^ ord($this->key[$i % $this->keyLen]));
        }
        return base64_encode($result);
    }

    /**
     * 解密
     * @param string $str 要解密的字符串
     * @return string|bool 成功返回解密后的字符串
     */
    private function decrypt($str)
    {
        $str = base64_decode($str);
        $result = '';
        for ($i = 0, $strLen = strlen($str); $i < $strLen; $i++) {
            $result .= chr(ord($str[$i]) ^ ord($this->key[$i % $this->keyLen]));
        }
        $expire = substr($result, -10);
        if ($expire === '0000000000' || $expire >= time()) {
            $result = substr($result, 0, -10);
            $content = substr($result, 0, -32);
            $hash = substr($result, -32);
            if (strcmp(hash_hmac('sha256', $content, $this->key, true), $hash) === 0) {
                return $content;
            }
        }
        return false;
    }
}
