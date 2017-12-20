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
     * @var integer 密钥长度
     */
    private $keyLen;

    /**
     * @var integer COOKIE过期时间
     */
    private $expire = 0;

    /**
     * @var string COOKIE路径
     */
    private $path = '/';

    /**
     * @var string COOKIE域名
     */
    private $domain = '';

    /**
     * @var boolean 是否仅用HTTPS传输COOKIE
     */
    private $secure = false;

    /**
     * @var boolean COOKIE只能通过HTTP请求访问,JS将不能访问
     */
    private $httpOnly = false;

    /**
     * 初始化密钥
     */
    public function init()
    {
        $this->key = strtoupper(sha1($this->key . 'Lying'));
        $this->keyLen = strlen($this->key);
    }

    /**
     * 设置过期时间
     * @param integer $expire 过期时间戳
     * @return $this
     */
    public function setExpire($expire)
    {
        $this->expire = $expire;
        return $this;
    }

    /**
     * 设置COOKIE路径
     * @param string $path COOKIE路径
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * 设置COOKIE域名
     * @param string $domain COOKIE域名
     * @return $this
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * 设置仅用HTTPS传输COOKIE
     * @return $this
     */
    public function setSecure()
    {
        $this->secure = true;
        return $this;
    }

    /**
     * 设置COOKIE只能通过http请求访问,JS将不能访问
     * @return $this
     */
    public function setHttpOnly()
    {
        $this->httpOnly = true;
        return $this;
    }

    /**
     * 重置COOKIE设置条件
     */
    private function reset()
    {
        $this->expire = 0;
        $this->path = '/';
        $this->domain = '';
        $this->secure = false;
        $this->httpOnly = false;
    }

    /**
     * 设置COOKIE
     * @param string $name COOKIE名称
     * @param mixed $value COOKIE的值
     * @return boolean 成功返回true,失败返回false
     */
    public function set($name, $value)
    {
        $value = $this->encrypt(serialize($value), $this->expire);
        $result = setcookie($name, $value, $this->expire, $this->path, $this->domain, $this->secure, $this->httpOnly);
        $this->reset();
        return $result;
    }

    /**
     * 检查COOKIE是否设置
     * @param string $name COOKIE名称
     * @return boolean COOKIE存在返回true,否则返回false
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
     * @return boolean 成功返回true,失败返回false
     */
    public function remove($name)
    {
        return $this->setExpire(time() - (365 * 24 * 60 * 60))->set($name, '');
    }

    /**
     * 加密
     * @param string $str 要加密的字符串
     * @param integer $expire 有效时间戳
     * @return string 加密后的字符串
     */
    private function encrypt($str, $expire = 0)
    {
        $str .= hash_hmac('sha256', $str, $this->key, true) . sprintf('%010d',$expire);
        for ($i = 0, $result = '', $strLen = strlen($str); $i < $strLen; $i++) {
            $result .= chr(ord($str[$i]) ^ ord($this->key[$i % $this->keyLen]));
        }
        return base64_encode($result);
    }

    /**
     * 解密
     * @param string $str 要解密的字符串
     * @return string|boolean 成功返回解密后的字符串
     */
    private function decrypt($str)
    {
        $str = base64_decode($str);
        for ($i = 0, $result = '', $strLen = strlen($str); $i < $strLen; $i++) {
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
