<?php
namespace lying\service;

class Maker
{
    /**
     * @var Service[] 服务类实例容器
     */
    private static $instances = [];
    
    /**
     * @var array 所有注册的服务
     */
    private static $service = [
        'config' => 'lying\service\Config',
        'request' => 'lying\service\Request',
        'router' => 'lying\service\Router',
        'secure' => 'lying\service\Secure',
        'session' => 'lying\service\Session',
        'cookie' => 'lying\service\Cookie',
        'dispatch' => 'lying\service\Dispatch',
    ];
    
    /**
     * 按需注册服务,服务类可以一样,服务id不能重复
     * @param array $service 配置文件配置的服务
     */
    public function __construct($service)
    {
        self::$service = array_merge(self::$service, $service);
    }
    
    /**
     * 根据id返回注册的服务
     * @param string $id 服务id
     * @throws \Exception
     * @return Service
     */
    public function createService($id)
    {
        if (isset(self::$instances[$id])) {
            return self::$instances[$id];
        } elseif (isset(self::$service[$id])) {
            if (is_array(self::$service[$id])) {
                $class = array_shift(self::$service[$id]);
                self::$instances[$id] = new $class(self::$service[$id]);
            } else {
                self::$instances[$id] = new self::$service[$id]();
            }
            unset(self::$service[$id]);
            return self::$instances[$id];
        } else {
            throw new \Exception("Unkonw service ID: $id", 500);
        }
    }
    
    /**
     * 配置服务
     * @return Config
     */
    public function config()
    {
        return $this->createService('config');
    }
    
    /**
     * 请求服务
     * @return Request
     */
    public function request()
    {
        return $this->createService('request');
    }
    
    /**
     * 路由服务
     * @return Router
     */
    public function router()
    {
        return $this->createService('router');
    }
    
    /**
     * 加密服务
     * @return Secure
     */
    public function secure()
    {
        return $this->createService('secure');
    }
    
    /**
     * Session服务
     * @return Session
     */
    public function session()
    {
        return $this->createService('session');
    }
    
    /**
     * Cookie服务
     * @return Cookie
     */
    public function cookie()
    {
        return $this->createService('cookie');
    }
    
    /**
     * 返回调度器
     * @return Dispatch
     */
    public function dispatch()
    {
        return $this->createService('dispatch');
    }
    
    /**
     * 数据库服务
     * @param string $id 数据库连接的ID
     * @return \lying\db\Connection
     */
    public function db($id = 'db')
    {
        return $this->createService($id);
    }
    
    /**
     * 日志服务
     * @param string $id 日志服务的ID
     * @return \lying\logger\Logger
     */
    public function logger($id = 'logger')
    {
        return $this->createService($id);
    }
    
    /**
     * 缓存服务
     * @param string $id 缓存服务的ID
     * @return \lying\cache\Cache
     */
    public function cache($id = 'cache')
    {
        return $this->createService($id);
    }
}
