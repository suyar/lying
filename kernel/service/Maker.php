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
 * @method Hook hook()
 * @method Request request()
 * @method Router router()
 * @method Cookie cookie()
 * @method Encrypter encrypter()
 * @method Dispatch dispatch()
 * @method Response response()
 * @method View view()
 * @method Session session()
 * @method Helper helper()
 * @method Logger logger(string $id = 'logger')
 * @method Cache cache(string $id = 'cache')
 * @method Redis redis(string $id = 'redis')
 * @method Connection db(string $id = 'db')
 * 
 * @property Hook $hook
 * @property Request $request
 * @property Router $router
 * @property Cookie $cookie
 * @property Encrypter $encrypter
 * @property Dispatch $dispatch
 * @property Response $response
 * @property View $view
 * @property Session $session
 * @property Helper $helper
 * @property Logger $logger
 * @property Cache $cache
 * @property Redis $redis
 * @property Connection $db;
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
        'hook' => ['class' => 'lying\service\Hook'],
        'request' => ['class' => 'lying\service\Request'],
        'router' => ['class' => 'lying\service\Router'],
        'cookie' => ['class' => 'lying\service\Cookie'],
        'encrypter' => ['class' => 'lying\service\Encrypter'],
        'dispatch' => ['class' => 'lying\service\Dispatch'],
        'response' => ['class' => 'lying\service\Response'],
        'view' => ['class' => 'lying\service\View'],
        'session' => ['class' => 'lying\service\Session'],
        'helper' => ['class' => 'lying\service\Helper'],
    ];

    /**
     * @var array 默认扩展组件,可被重新定义的服务
     */
    private static $_extends = [
        'logger' => ['class' => 'lying\service\Logger'],
        'cache' => ['class' => 'lying\cache\FileCache'],
        'redis' => ['class' => 'lying\service\Redis'],
        'db' => ['class' => 'lying\db\Connection'],
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

    /**
     * 设置未知属性的值报错
     * @param string $name 属性名
     * @param mixed $value 属性值
     * @throws \Exception
     */
    public function __set($name, $value)
    {
        throw new \Exception("Unable to reset property value: {$name}.");
    }
}
