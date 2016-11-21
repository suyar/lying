<?php
namespace lying\service;

class Cookie extends Service
{
    protected $key;
    
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
    public function send($name, $value, $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = false) {
        $value = base64_encode($this->get('secure')->AES_encrypt($value, $this->key, md5($name, true)));
        return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }
    
    /**
     * 获取cookie
     * @param string $name
     * @param string $defaultValue
     * @return string
     */
    public function find($name, $defaultValue = null) {
        var_dump($_COOKIE[$name]);
        return isset($_COOKIE[$name]) ? $this->get('secure')->AES_decrypt(base64_decode($_COOKIE[$name]), $this->key, md5($name, true)) : $defaultValue;
    }
    
    /**
     * 删除一个cookie
     * @param string $name
     * @param string $path
     * @return boolean
     */
    public function remove($name, $path = '/') {
        return isset($_COOKIE[$name]) ? $this->send($name, '', time() - 1, $path) : false;
    }
}