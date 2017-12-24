<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\service;

/**
 * Class Dispatch
 * @package lying\service
 */
class Dispatch extends Service
{
    /**
     * 程序执行入口
     * @throws \Exception 页面不存在抛出404错误
     */
    public function run()
    {
        list($m, $c, $a) = \Lying::$maker->router()->resolve();
        $moduleNamespace = php_sapi_name() === 'cli' ? 'console' : 'module';
        $class = "$moduleNamespace\\$m\\controller\\$c";
        if (class_exists($class)) {
            $instance = new $class();
            if (method_exists($instance, $a)) {
                $method = new \ReflectionMethod($instance, $a);
                if ($method->isPublic() && $this->checkAccess($instance->deny, $a)) {
                    $instance->trigger($instance::EVENT_BEFORE_ACTION, [$a]);
                    $response = call_user_func_array([$instance, $a], $this->parseArgs($method->getParameters()));
                    $instance->trigger($instance::EVENT_AFTER_ACTION, [$a, $response]);
                    echo (is_string($response) || is_numeric($response) || is_null($response) || is_bool($response)) ? $response : json_encode($response);
                    exit(0);
                }
            }
        }
        throw new \Exception('Not Found (#404)', 404);
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
     * @return array 返回要带入执行方法的GET参数
     */
    private function parseArgs($params)
    {
        $args = [];
        foreach ($params as $param) {
            $arg = \Lying::$maker->request()->get($param->name);
            if ($arg !== null) {
                $args[] = $arg;
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            }
        }
        return $args;
    }
}
