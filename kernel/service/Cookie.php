<?php
namespace lying\service;

/**
 * COOKIE组件
 *
 * @author carolkey <me@suyaqi.cn>
 * @since 2.0
 * @link https://github.com/carolkey/lying
 * @license MIT
 */
class Cookie extends Service
{
    /**
     * @var string COOKIE加密密钥
     */
    protected $key = 'lying';

    /**
     * 设置COOKIE
     * @param string $name COOKIE名称
     * @param mixed $value COOKIE的值
     * @param integer $expire 过期时间戳
     * @param string $path COOKIE路径
     * @param string $domain COOKIE域名
     * @param boolean $secure 是否用HTTPS传输COOKIE
     * @param boolean $httponly COOKIE只能通过http请求访问，JS将不能访问
     * @return boolean 成功返回true，失败返回false
     */
    public function set($name, $value, $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = false)
    {
        $value = $this->encrypt($value, $this->key);
        return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }
    
    /**
     * 获取COOKIE
     * @param string $name COOKIE名称
     * @return string|boolean 返回COOKIE的值，失败或不存在返回false
     */
    public function get($name)
    {
        if ($this->exists($name)) {
            return $this->decrypt($_COOKIE[$name], $this->key);
        }
        return false;
    }
    
    /**
     * 检查COOKIE是否设置
     * @param string $name COOKIE名称
     * @return boolean COOKIE存在设置返回true，否则返回false
     */
    public function exists($name)
    {
        return isset($_COOKIE[$name]);
    }
    
    /**
     * 删除COOKIE
     * @param string $name COOKIE名称
     * @param string $path COOKIE路径
     * @param string $domain COOKIE域名
     * @return boolean 成功返回true，失败返回false
     */
    public function remove($name, $path = '/', $domain = '')
    {
        return $this->exists($name) ? setcookie($name, '', time() - 31536000, $path, $domain) : false;
    }

    /**
     * 加密
     * @param string $str 要加密的字符串
     * @param string $key 密钥
     * @return string 加密后的字符串
     */
    private function encrypt($str, $key)
    {
        $key = strtoupper(sha1($key));
        $str .= hash_hmac('sha256', $str, $key, true);
        $strLen = strlen($str);
        $result = '';
        for ($i = 0; $i < $strLen; $i++) {
            $result .= chr(ord($str[$i]) ^ ord($key[$i % 40]));
        }
        return base64_encode($result);
    }

    /**
     * 解密；注意，这里可能返回空的字符串，请用false === $result判断返回值
     * @param string $str 要解密的字符串
     * @param string $key 密钥
     * @return string|boolean 成功返回解密后的字符串，失败返回false
     */
    private function decrypt($str, $key)
    {
        $key = strtoupper(sha1($key));
        $str = base64_decode($str);
        $strLen = strlen($str);
        $result = '';
        for ($i = 0; $i < $strLen; $i++) {
            $result .= chr(ord($str[$i]) ^ ord($key[$i % 40]));
        }
        $str = substr($result, 0, -32);
        return strcmp(substr($result, -32), hash_hmac('sha256', $str, $key, true)) === 0 ? $str : false;
    }
}
