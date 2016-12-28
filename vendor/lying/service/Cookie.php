<?php
namespace lying\service;

class Cookie extends Service
{
    protected $key;
    
    /**
     * 没有设置key的时候,用域名当key
     */
    protected function init()
    {
        if (!$this->key) {
            $this->key = maker()->request()->host();
        }
    }
    
    /**
     * 设置cookie
     * @param string $name cookie名称
     * @param mixed $value cookie的值,会被序列化
     * @param number $expire 过期时间
     * @param string $path cookie路径
     * @param string $domain cookie的域名
     * @param boolean $secure 是否用https传输cookie
     * @param boolean $httponly cookie只能通过http请求访问,javascript将不能访问
     * @return boolean 成功返回true,失败返回false
     */
    public function set($name, $value, $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = false) {
        $value = serialize($value);
        $value = hash_hmac('sha256', $value, $this->key) . $value;
        return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }
    
    /**
     * 获取cookie
     * @param string $name cookie名称
     * @param string $default 默认值
     * @return string|null
     */
    public function get($name, $default = null) {
        if (isset($_COOKIE[$name])) {
            $data = $this->validate($_COOKIE[$name]);
            if ($data !== false) {
                return $data;
            }
        }
        return $default;
    }
    
    /**
     * 校验cookie值
     * @param string $cookie
     * @return string|boolean 成功返回反序列化后的字符串,失败返回false
     */
    private function validate($cookie)
    {
        $hash = substr($cookie, 0, 64);
        $data = substr($cookie, 64);
        $validate = hash_hmac('sha256', $data, $this->key);
        return substr_compare($validate, $hash, 0) === 0 ? unserialize($data) : false;
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