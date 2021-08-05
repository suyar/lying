<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

use lying\exception\InvalidRouteException;
use lying\exception\HttpException;
use lying\service\Response;

/**
 * Class Lying
 */
class Lying
{
    /**
     * 框架开始执行事件
     */
    const EVENT_FRAMEWORK_BEGIN = 'frameworkBegin';

    /**
     * 框架执行结束事件
     */
    const EVENT_FRAMEWORK_END = 'frameworkEnd';

    /**
     * TICK事件
     */
    const EVENT_FRAMEWORK_TICK = 'frameworkTick';

    /**
     * 错误处理事件
     */
    const EVENT_FRAMEWORK_ERROR = 'frameworkError';

    /**
     * @var array 全局配置数组
     */
    private static $_config;

    /**
     * @var \lying\service\Maker 工厂实例
     */
    public static $maker;

    /**
     * Lying constructor.
     * @param array $config 全局配置数组
     */
    public function __construct(array $config)
    {
        self::$_config = $config;

        spl_autoload_register([self::class, 'autoload']);

        self::$maker = new \lying\service\Maker(self::config('service'));

        (new \lying\service\Exception())->register();

        date_default_timezone_set(self::config('timezone', 'Asia/Shanghai'));
    }

    /**
     * 获取Lying的版本号
     * @return string 返回当前版本号
     */
    public static function version()
    {
        return '2.1';
    }

    /**
     * 启动框架
     * @throws \Exception 其他错误抛出异常
     * @throws \lying\exception\HttpException 路由解析错误抛出404
     */
    public function run()
    {
        self::$maker->hook->trigger(self::EVENT_FRAMEWORK_BEGIN);
        self::$maker->hook->trigger(self::EVENT_FRAMEWORK_TICK);

        $route = self::$maker->request->resolve();

        try {
            $result = self::$maker->dispatch->run($route, self::$maker->request->get());
        } catch (InvalidRouteException $exception) {
            throw new HttpException('Page not found.', 404);
        }

        if ($result instanceof Response) {
            $response = $result;
        } else {
            $response = self::$maker->response;
            $response->setContent($result);
        }

        $response->send();

        self::$maker->hook->trigger(self::EVENT_FRAMEWORK_END);
        self::$maker->hook->trigger(self::EVENT_FRAMEWORK_TICK);
    }

    /**
     * 获取配置项
     * @param string $key 配置键名,支持'user.name'形式的读取方式
     * @param mixed $default 配置不存在时的默认值,默认为null
     * @return mixed 成功返回配置值,配置不存在返回默认值
     */
    public static function config($key, $default = null)
    {
        $config = self::$_config;
        foreach (explode('.', $key) as $k) {
            if (isset($config[$k])) {
                $config = $config[$k];
            } else {
                return $default;
            }
        }
        return $config;
    }
    
    /**
     * 自动加载
     * @param string $className 完整类名
     */
    private static function autoload($className)
    {
        if (($classFile = self::classMapLoader($className)) ||
            ($classFile = self::psr4Loader($className)) ||
            ($classFile = self::psr0Loader($className))) {
            require $classFile;
        }
    }
    
    /**
     * classMap加载
     * @param string $className 类名
     * @return string|bool 成功返回文件绝对路径,失败返回false
     */
    private static function classMapLoader($className)
    {
        return file_exists($file = self::config("loader.classMap.$className")) ? $file : false;
    }
    
    /**
     * PSR-4自动加载,参考 http://www.php-fig.org/psr/psr-4/
     * @param string $className 类名
     * @return string|bool 成功返回文件绝对路径,失败返回false
     */
    private static function psr4Loader($className)
    {
        $prefix = $className;
        while (false !== $pos = strrpos($prefix, '\\')) {
            $prefix = substr($prefix, 0, $pos);
            if ($filePre = self::config("loader.psr-4.$prefix")) {
                $relativeClass = str_replace('\\', DS, substr($className, $pos));
                if (is_array($filePre)) {
                    foreach ($filePre as $path) {
                        $file = $path . $relativeClass . '.php';
                        if (file_exists($file)) {
                            return $file;
                        }
                    }
                } else {
                    $file = $filePre . $relativeClass . '.php';
                    if (file_exists($file)) {
                        return $file;
                    }
                }
            }
        }
        return false;
    }
    
    /**
     * PSR-0自动加载,参考 http://www.php-fig.org/psr/psr-0/
     * @param string $className 类名
     * @return string|bool 成功返回文件绝对路径,失败返回false
     */
    private static function psr0Loader($className)
    {
        if (false === $pos = strrpos($className, '\\')) {
            $file = str_replace('_', DS, $className) . '.php';
        } else {
            $namespace = str_replace('\\', DS, substr($className, 0, $pos));
            $class = str_replace(['_', '\\'], DS, substr($className, $pos));
            $file = $namespace . $class . '.php';
        }
        foreach (self::config('loader.psr-0') as $baseDir) {
            $absoluteFile = $baseDir . DS . $file;
            if (file_exists($absoluteFile)) {
                return $absoluteFile;
            }
        }
        return false;
    }
}
