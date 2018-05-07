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
use lying\exception\InvalidConfigException;

/**
 * Class Maker
 * @package lying\service
 *
 * @method Cache cache(string $id = 'cache')
 * @method Cookie cookie()
 * @method Connection db(string $id = 'db')
 * @method Dispatch dispatch()
 * @method Helper helper()
 * @method Hook hook()
 * @method Logger logger(string $id = 'logger')
 * @method Request request()
 * @method Redis redis(string $id = 'redis')
 * @method Router router()
 * @method Session session()
 * 
 * @property Hook $hook
 */
class Maker
{
    /**
     * @var array 服务类实例容器
     */
    private static $_instances = [];

    /**
     * @var array 核心组件,类名不可被覆盖,但是配置可以被覆盖
     */
    private static $_cores = [
        'cookie' => ['class' => 'lying\service\Cookie'],
        'dispatch' => ['class' => 'lying\service\Dispatch'],
        'hook' => ['class' => 'lying\service\Hook'],
        'request' => ['class' => 'lying\service\Request'],
        'router' => ['class' => 'lying\service\Router'],
        'session' => ['class' => 'lying\service\Session'],
    ];

    /**
     * @var array 默认扩展组件,可被重新定义的服务
     */
    private static $_extends = [
        'cache' => ['class' => 'lying\cache\FileCache'],
    ];
    
    /**
     * @var array 注册的服务
     */
    private static $_service = [];
    
    /**
     * 注册服务
     * @param array $services 服务配置数组
     */
    public function __construct(array $services)
    {
        self::$_service = array_merge(self::$_extends, self::$_cores);
        foreach ($services as $id => $service) {
            if (isset(self::$_cores[$id])) {
                if (is_array($service)) {
                    $service['class'] = self::$_cores[$id]['class'];
                    self::$_service[$id] = $service;
                }
            } elseif (isset(self::$_extends[$id]) && is_array($service) && !isset($service['class'])) {
                $service['class'] = self::$_extends[$id]['class'];
                self::$_service[$id] = $service;
            } elseif (is_array($service) && isset($service['class'])) {
                self::$_service[$id] = $service;
            } elseif (is_string($service)) {
                self::$_service[$id] = ['class' => $service];
            }
        }
    }

    /**
     * 根据ID返回注册的服务
     * @param string $id 服务ID
     * @return Service 返回所实例化的服务类
     * @throws InvalidConfigException 当服务ID不存在的时候抛出异常
     */
    public function get($id)
    {
        if (isset(self::$_instances[$id])) {
            return self::$_instances[$id];
        } elseif (isset(self::$_service[$id])) {
            $class = self::$_service[$id]['class'];
            unset(self::$_service[$id]['class']);
            self::$_instances[$id] = new $class(self::$_service[$id]);
            return self::$_instances[$id];
        } else {
            throw new InvalidConfigException("Unknown component ID: $id");
        }
    }

    /**
     * 魔术方法,返回方法对应的服务类
     * @param string $name 方法名
     * @param array $arguments 方法参数
     * @return Service 返回方法对应的服务类
     * @throws InvalidConfigException 当服务ID不存在的时候抛出异常
     */
    public function __call($name, $arguments)
    {
        return $this->get(isset($arguments[0]) ? $arguments[0] : $name);
    }

    /**
     * 获取对应服务组件
     * @param string $name 服务ID
     * @return Service 返回所实例化的服务类
     * @throws InvalidConfigException 当服务ID不存在的时候抛出异常
     */
    public function __get($name)
    {
        return $this->get($name);
    }
}
