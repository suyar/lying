<?php

/**
 * 框架基类，用来做各种初始化和自动加载
 *
 * @author carolkey <me@suyaqi.cn>
 * @since 2.0
 * @link https://carolkey.github.io/
 * @license MIT
 */
class Lying
{
    /**
     * @var array 核心类文件映射
     */
    private static $classMap = [];
    
    /**
     * @var array 加载方式配置
     */
    private static $extend = [];
    
    /**
     * @var \lying\service\Maker 工厂实例
     */
    public static $maker;
    
    /**
     * 初始化启动参数
     */
    public static function boot()
    {
        self::$classMap = require DIR_LYING . '/classes.php';

        spl_autoload_register([self::class, 'autoload']);

        \lying\base\Exception::register();

        self::$maker = new \lying\service\Maker(require DIR_CONF . '/service.php');

        self::$extend = self::$maker->config()->read('loader');
    }
    
    /**
     * 自动加载
     * @param string $className 完整类名
     */
    private static function autoload($className)
    {
        if (isset(self::$classMap[$className])) {
            $file = self::$classMap[$className];
        } else {
            ($file = self::classMapLoader($className)) ||
            ($file = self::psr4Loader($className)) ||
            ($file = self::psr0Loader($className));
        }
        if ($file) {
            require $file;
        }
    }
    
    /**
     * classMap加载
     * @param string $className 类名
     * @return string|boolean 成功返回文件绝对路径，失败返回false
     */
    private static function classMapLoader($className)
    {
        if (isset(self::$extend['classMap'][$className]) && file_exists(self::$extend['classMap'][$className])) {
            return self::$extend['classMap'][$className];
        }
        return false;
    }
    
    /**
     * PSR-4自动加载，参考 http://www.php-fig.org/psr/psr-4/
     * @param string $className 类名
     * @return string|boolean 成功返回文件绝对路径，失败返回false
     */
    private static function psr4Loader($className)
    {
        if (isset(self::$extend['psr-4'])) {
            $prefix = $className;
            while (false !== $pos = strrpos($prefix, '\\')) {
                $prefix = substr($prefix, 0, $pos);
                if (isset(self::$extend['psr-4'][$prefix])) {
                    $relativeClass = str_replace('\\', '/', substr($className, $pos));
                    if (is_array(self::$extend['psr-4'][$prefix])) {
                        foreach (self::$extend['psr-4'][$prefix] as $path) {
                            $file = $path . $relativeClass . '.php';
                            if (file_exists($file)) {
                                return $file;
                            }
                        }
                    } else {
                        $file = self::$extend['psr-4'][$prefix] . $relativeClass . '.php';
                        if (file_exists($file)) {
                            return $file;
                        }
                    }
                }
            }
        }
        return false;
    }
    
    /**
     * PSR-0自动加载，参考 http://www.php-fig.org/psr/psr-0/
     * @param string $className 类名
     * @return string|boolean 成功返回文件绝对路径，失败返回false
     */
    private static function psr0Loader($className)
    {
        if (isset(self::$extend['psr-0'])) {
            if (false === $pos = strrpos($className, '\\')) {
                $file = str_replace('_', '/', $className) . '.php';
            } else {
                $namespace = str_replace('\\', '/', substr($className, 0, $pos));
                $class = str_replace(['_', '\\'], '/', substr($className, $pos));
                $file = $namespace . $class . '.php';
            }
            foreach (self::$extend['psr-0'] as $baseDir) {
                $absoluteFile = $baseDir . '/' . $file;
                if (file_exists($absoluteFile)) {
                    return $absoluteFile;
                }
            }
        }
        return false;
    }
}
