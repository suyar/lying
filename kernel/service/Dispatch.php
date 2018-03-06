<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\service;

use lying\event\ActionEvent;
use lying\exception\InvalidRouteException;

/**
 * Class Dispatch
 * @package lying\service
 */
class Dispatch extends Service
{
    /**
     * 程序执行入口
     * @param array|string $route 要调度的路由
     * @param array $params 传控方法的参数,若果放空则自动从GET参数获取
     * @return mixed 返回调度结果
     * @throws InvalidRouteException 路由无法解析抛出异常
     */
    public function run($route, array $params = [])
    {
        if ($route = $this->resolve($route)) {
            list($m, $c, $a) = $route;
            $moduleNamespace = PHP_SAPI === 'cli' ? 'console' : 'module';
            $class = "$moduleNamespace\\$m\\controller\\$c";
            if (method_exists($class, $a)) {
                /** @var Controller $instance */
                $instance = new $class;
                $instance->hook($instance::EVENT_BEFORE_ACTION, [$instance, 'beforeAction']);
                $instance->hook($instance::EVENT_AFTER_ACTION, [$instance, 'afterAction']);
                $method = new \ReflectionMethod($instance, $a);
                if ($method->isPublic() && $this->checkAccess($instance->deny, $a)) {
                    $instance->trigger($instance::EVENT_BEFORE_ACTION, new ActionEvent(['action'=>$a]));
                    $response = call_user_func_array([$instance, $a], $this->parseArgs($method->getParameters(), $params));
                    $instance->trigger($instance::EVENT_AFTER_ACTION, new ActionEvent(['action'=>$a, 'response'=>$response]));
                    return $response;
                }
            }
        }

        throw new InvalidRouteException('Unable to resolve the request');
    }

    /**
     * 检查要执行的路由
     * @param array|string $route 路由
     * @return array|false
     */
    private function resolve($route)
    {
        if (is_array($route) && count($route) === 3) {
            $route = array_values($route);
            return [
                $this->str2hump($route[0]),
                $this->str2hump($route[1], true) . 'Ctrl',
                $this->str2hump($route[2]),
            ];
        } elseif (is_string($route) && strpos($route, '/')) {
            return $this->resolve(explode('/', $route));
        }
        return false;
    }
    
    /**
     * 匹配到的方法将不能被访问,默认init|beforeAction|afterAction不能被访问
     * @param array $pregs 一个存放正则表达式的数组
     * @param string $action 方法名称
     * @return bool 返回true允许访问,false禁止访问
     */
    private function checkAccess($pregs, $action)
    {
        $pregs = array_merge(['/^(init|beforeAction|afterAction)$/i'], $pregs);
        foreach ($pregs as $pattern) {
            if (preg_match($pattern, $action)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 返回方法所带的GET参数数组
     * @param \ReflectionParameter[] $params 一个\ReflectionParameter的数组
     * @param array $ext 外部带入的方法参数
     * @return array 返回要带入执行方法的GET参数
     */
    private function parseArgs($params, $ext)
    {
        $args = [];
        foreach ($params as $param) {
            $arg = isset($ext[$param->name]) ? $ext[$param->name] : \Lying::$maker->request()->get($param->name);
            if ($arg !== null) {
                $args[] = $arg;
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            }
        }
        return $args;
    }

    /**
     * 把横线分割的小写字母转换为驼峰
     * @param string $str 要转换的字符串
     * @param bool $ucfirst 首字母是否大写
     * @return string 返回转换后的字符串
     */
    private function str2hump($str, $ucfirst = false)
    {
        $str = str_replace(' ', '', ucwords(str_replace('-', ' ', $str)));
        return $ucfirst ? $str : lcfirst($str);
    }
}
