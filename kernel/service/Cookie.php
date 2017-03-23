<?php
namespace lying\service;

/**
 * COOKIE组件
 *
 * @author carolkey <me@suyaqi.cn>
 * @since 2.0
 * @link https://carolkey.github.io/
 * @license MIT
 */
class Cookie extends Service implements \ArrayAccess
{
    /**
     * @var string COOKIE加密密钥
     */
    protected $key;
    
    /**
     * 初始化COOKIE密钥
     */
    protected function init()
    {
        $this->key = $this->key ? $this->key : 'lying';
    }
    
    /**
     * 设置COOKIE
     * @param string $name COOKIE名称
     * @param mixed $value COOKIE的值，会被序列化
     * @param integer $expire 过期时间
     * @param string $path COOKIE路径
     * @param string $domain COOKIE的域名
     * @param boolean $secure 是否用HTTPS传输COOKIE
     * @param boolean $httponly COOKIE只能通过http请求访问，JS将不能访问
     * @return boolean 成功返回true,失败返回false
     */
    public function set($name, $value, $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = false)
    {
        $value = \Lying::$maker->secure()->xorEncrypt($value, $this->key);
        return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }
    
    /**
     * 获取COOKIE
     * @param string $name COOKIE名称
     * @param string $default 默认值
     * @return string|null 返回COOKIE的值，不存在返回null
     */
    public function get($name, $default = null)
    {
        if (isset($_COOKIE[$name])) {
            $res = \Lying::$maker->secure()->xorDecrypt($_COOKIE[$name], $this->key);
            if (false !== $res) {
                return $res;
            }
        }
        return $default;
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
     * @return boolean 成功返回true，失败返回false
     */
    public function remove($name, $path = '/')
    {
        return $this->exists($name) ? setcookie($name, '', time() - 86400, $path) : false;
    }
    
    /**
     * 设置COOKIE
     * @param string $offset COOKIE名称
     * @param mixed $value COOKIE值
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }
    
    /**
     * 检查COOKIE是否存在
     * @param string $offset COOKIE名称
     * @return boolean COOKIE存在设置返回true，否则返回false
     */
    public function offsetExists($offset)
    {
        return $this->exists($offset);
    }
    
    /**
     * 删除COOKIE
     * @param string $offset COOKIE名称
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }
    
    /**
     * 获取COOKIE
     * @param string $offset COOKIE名称
     * @return mixed COOKIE值，不存在返回null
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }
}
