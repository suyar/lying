<?php
namespace lying\service;

class Cookie extends Service implements \ArrayAccess
{
    /**
     * @var string cookie加密密钥
     */
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
        $value = maker()->secure()->xorEncrypt($value, $this->key);
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
            $res = maker()->secure()->xorDecrypt($_COOKIE[$name], $this->key);
            if (false !== $res) {
                return $res;
            }
        }
        return $default;
    }
    
    /**
     * 检查一个cookie是否设置
     * @param string $name cookie名称
     * @return boolean cookie设置返回true,否则返回false
     */
    public function exists($name)
    {
        return isset($_COOKIE[$name]);
    }
    
    /**
     * 删除一个cookie
     * @param string $name cookie名称
     * @param string $path cookie路径
     * @return boolean 成功返回true,失败返回false
     */
    public function remove($name, $path = '/') {
        return $this->exists($name) ? setcookie($name, '', time() - 1, $path) : false;
    }
    
    /**
     * 设置一个偏移位置的值
     * {@inheritDoc}
     * @see ArrayAccess::offsetSet()
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }
    
    /**
     * 检查一个偏移位置是否存在
     * {@inheritDoc}
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($offset)
    {
        return $this->exists($offset);
    }
    
    /**
     * 复位一个偏移位置的值
     * {@inheritDoc}
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }
    
    /**
     * 获取一个偏移位置的值
     * {@inheritDoc}
     * @see ArrayAccess::offsetGet()
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }
}
