<?php
namespace lying\service;

class Maker
{
    /**
     * @var array 服务类实例容器
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
            return self::$instances[$id];
        } else {
            throw new \Exception("Unkonw service ID: $id", 500);
        }
    }
}
