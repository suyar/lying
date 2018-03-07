<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\service;

use lying\cache\Cache;
use lying\db\Connection;

/**
 * Class Maker
 * @package lying\service
 *
 * @method Cache cache(string $id = 'cache')
 * @method Cookie cookie()
 * @method Connection db(string $id = 'db')
 * @method Dispatch dispatch()
 * @method Exception exception()
 * @method Helper helper()
 * @method Hook hook()
 * @method Logger logger(string $id = 'logger')
 * @method Request request()
 * @method Redis redis(string $id = 'redis')
 * @method Router router()
 * @method Session session()
 */
class Maker
{
    /**
     * @var array 服务类实例容器
     */
    private static $instances = [];
    
    /**
     * @var array 注册的服务
     */
    private static $service = [];
    
    /**
     * 按需注册服务,服务类可以一样,服务ID不能重复
     * @param array $service 服务配置数组
     */
    public function __construct($service)
    {
        $core = [
            'cookie' => 'lying\service\Cookie',
            'dispatch' => 'lying\service\Dispatch',
            'exception' => 'lying\service\Exception',
            'hook' => 'lying\service\Hook',
            'request' => 'lying\service\Request',
            'router' => 'lying\service\Router',
            'session' => 'lying\service\Session',
        ];
        self::$service = array_merge($core, $service);
        (php_sapi_name() === 'cli') && (self::$service['router'] = 'lying\service\Router');
    }

    /**
     * 根据ID返回注册的服务
     * @param string $id 服务ID
     * @return Service 返回所实例化的服务类
     * @throws \Exception 服务不存在抛出异常
     */
    public function createService($id)
    {
        if (isset(self::$instances[$id])) {
            return self::$instances[$id];
        } elseif (isset(self::$service[$id])) {
            if (is_array(self::$service[$id])) {
                $class = self::$service[$id]['class'];
                unset(self::$service[$id]['class']);
                self::$instances[$id] = new $class(self::$service[$id]);
            } else {
                self::$instances[$id] = new self::$service[$id]();
            }
            return self::$instances[$id];
        } else {
            throw new \Exception("Unkonw service ID: $id", 500);
        }
    }

    /**
     * 魔术方法,返回方法对应的服务类
     * @param string $name 方法名
     * @param array $arguments 方法参数
     * @return Service 返回方法对应的服务类
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        return $this->createService(isset($arguments[0]) ? $arguments[0] : $name);
    }
}
