<?php
/**
 * @author carolkey <me@suyaqi.cn>
 * @link https://github.com/carolkey/lying
 * @copyright 2017 Lying
 * @license MIT
 */

/**
 * Class Lying
 * @since 2.0
 */
class Lying
{
    /**
     * @var array 全局配置数组
     */
    private static $config;

    /**
     * @var \lying\service\Maker 工厂实例
     */
    public static $maker;

    /**
     * 启动框架
     * @param array $config 配置数组
     */
    public static function run($config)
    {
        if (self::$maker === null) {
            self::boot($config);
            self::$maker->dispatch()->run();
        }
    }

    /**
     * 初始化启动参数
     * @param array $config 全局配置数组
     */
    private static function boot($config)
    {
        self::$config = $config;

        spl_autoload_register([self::class, 'autoload']);

        self::$maker = new \lying\service\Maker(self::config('service'));

        self::$maker->exception()->register();

        date_default_timezone_set(self::config('timezone', 'Asia/Shanghai'));
    }

    /**
     * 获取配置
     * @param string $key 配置键名,支持'user.name'形式的键名
     * @param mixed $default 配置不存在时的默认值
     * @return mixed 成功返回配置值,配置不存在返回null
     */
    public static function config($key, $default = null)
    {
        $config = self::$config;
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
     * @return string|boolean 成功返回文件绝对路径,失败返回false
     */
    private static function classMapLoader($className)
    {
        return file_exists($file = self::config("loader.classMap.$className")) ? $file : false;
    }
    
    /**
     * PSR-4自动加载,参考 http://www.php-fig.org/psr/psr-4/
     * @param string $className 类名
     * @return string|boolean 成功返回文件绝对路径,失败返回false
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
     * @return string|boolean 成功返回文件绝对路径,失败返回false
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
