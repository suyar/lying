<?php
namespace core;
/**
 * cookie设置,所有cookie都有加密
 * @author suyq
 */
class Cookie {
    
    private static $cookie;
    
    /**
     * cookie加密解密key
     * @var string
     */
    private $key;
    
    private function __construct() {
        $this->key = isset(\App::$config['cookie']['key']) ? \App::$config['cookie']['key'] : $_SERVER['SERVER_NAME'];
    }
    
    private function __clone() {}
    
    public static function getInstance() {
        if (!self::$cookie instanceof self) {
            self::$cookie = new self;
        }
        return self::$cookie;
    }
    
    /**
     * 设置cookie
     * @param string $name cookie名称
     * @param string $value cookie的值
     * @param number $expire 过期时间
     * @param string $path cookie路径
     * @param string $domain cookie的域名
     * @param boolean $secure 是否用https传输cookie
     * @param boolean $httponly cookie只能通过http请求访问,javascript将不能访问
     * @return boolean
     */
    public function set($name, $value, $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = false) {
        $name = sha1($name);
        $value = base64_encode(Encrypt::getInstance()->RC4($value, $this->key));
        return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }
    
    /**
     * 获取cookie
     * @param string $name
     * @param string $defaultValue
     * @return string
     */
    public function get($name, $defaultValue = null) {
        $name = sha1($name);
        return isset($_COOKIE[$name]) ? Encrypt::getInstance()->RC4(base64_decode($_COOKIE[$name]), $this->key) : $defaultValue;
    }
    
    /**
     * 删除一个cookie
     * @param string $name
     * @param string $path
     * @return boolean
     */
    public function delete($name, $path = '/') {
        $name_sha1 = sha1($name);
        return isset($_COOKIE[$name_sha1]) ? $this->set($name, '', time() - 1, $path) : false;
    }
    
}