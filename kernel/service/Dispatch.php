<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\service;

use lying\event\ActionEvent;
use lying\exception\HttpException;
use lying\exception\InvalidRouteException;

/**
 * Class Dispatch
 * @package lying\service
 */
class Dispatch extends Service
{
    /**
     * @var Controller[] 实例化的控制器数组
     */
    private $_controllers = [];

    /**
     * 程序执行入口
     * @param array|string $route 要调度的路由
     * @param array $params 传控方法的参数,若果放空则自动从GET参数获取
     * @return mixed 返回执行结果
     * @throws InvalidRouteException 路由无法解析抛出异常
     * @throws HttpException 缺少参数抛出异常
     * @throws \ReflectionException 反射类异常
     * @throws \Exception 控制器未继承基础控制器抛出异常
     */
    public function run($route, array $params = [])
    {
        if ($route = $this->resolve($route,$raw)) {
            list($m, $c, $a) = $route;
            $moduleNamespace = PHP_SAPI === 'cli' ? 'console' : 'module';
            $class = "$moduleNamespace\\$m\\controller\\$c";
            if (isset($this->_controllers[$class]) && method_exists($this->_controllers[$class], $a)) {
                $instance = $this->_controllers[$class];
            } elseif (class_exists($class)) {
                if (is_subclass_of($class, 'lying\service\Controller')) {
                    if (method_exists($class, $a)) {
                        /** @var Controller $instance */
                        $instance = $this->_controllers[$class] = new $class(['module'=>$raw[0], 'id'=>$raw[1]]);
                        $instance->on($instance::EVENT_BEFORE_ACTION, [$instance, 'beforeAction']);
                        $instance->on($instance::EVENT_AFTER_ACTION, [$instance, 'afterAction']);
                    }
                } else {
                    throw new \Exception('Controller class must extend from \\lying\\service\\Controller.');
                }
            }
            if (isset($instance)) {
                $method = new \ReflectionMethod($instance, $a);
                if ($method->isPublic() && $this->checkAccess($instance->deny, $a)) {
                    $instance->trigger($instance::EVENT_BEFORE_ACTION, new ActionEvent(['action'=>$raw[2]]));
                    $response = call_user_func_array([$instance, $a], $this->parseArgs($method->getParameters(), $params));
                    $instance->trigger($instance::EVENT_AFTER_ACTION, new ActionEvent(['action'=>$raw[2], 'response'=>$response]));
                    return $response;
                }
            }
        }

        throw new InvalidRouteException('Unable to resolve the request');
    }

    /**
     * 检查要执行的路由
     * @param array|string $route 路由
     * @param array $raw 没有经过处理的模块/控制器/方法
     * @return array|bool 成功返回数组,失败返回false
     */
    private function resolve($route, &$raw = [])
    {
        if (is_array($route) && count($route) === 3) {
            $route = $raw = array_values($route);
            return [
                $this->str2hump($route[0]),
                $this->str2hump($route[1], true) . 'Ctrl',
                $this->str2hump($route[2]),
            ];
        } elseif (is_string($route) && strpos($route, '/')) {
            return $this->resolve(explode('/', $route), $raw);
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
     * @return array 返回要带入执行方法的参数
     * @throws HttpException 缺少参数的时候抛出异常
     */
    private function parseArgs($params, $ext)
    {
        $args = [];
        foreach ($params as $param) {
            if (array_key_exists($param->name, $ext)) {
                $args[] = $ext[$param->name];
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                throw new HttpException("Missing required parameter: {$param->name}", 400);
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
