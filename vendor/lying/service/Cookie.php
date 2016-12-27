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
    public function set($name, $value, $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = false) {
        $value = maker()->secure()->encrypt($value, $this->key);
        return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }
    
    /**
     * 获取cookie
     * @param string $name cookie名称
     * @param string $defaultValue 默认值
     * @return string
     */
    public function get($name, $defaultValue = null) {
        return isset($_COOKIE[$name]) ? maker()->secure()->decrypt($_COOKIE[$name], $this->key) : $defaultValue;
    }
    
    /**
     * 删除一个cookie
     * @param string $name
     * @param string $path
     * @return boolean
     */
    public function remove($name, $path = '/') {
        return isset($_COOKIE[$name]) ? $this->set($name, '', time() - 1, $path) : false;
    }
}