<?php
namespace lying\service;

/**
 * 调度器组件
 *
 * @author carolkey <me@suyaqi.cn>
 * @since 2.0
 * @link https://carolkey.github.io/
 * @license MIT
 */
class Dispatch
{
    /**
     * 程序执行入口
     * @throws \Exception 页面不存在抛出404错误
     */
    public function runAction()
    {
        list($m, $c, $a) = \Lying::$maker->router()->parse();
        $class = "module\\$m\\controller\\$c";
        if (class_exists($class) && method_exists($class, $a)) {
            $instance = new $class();
            $method = new \ReflectionMethod($instance, $a);
            if ($method->isPublic() && $this->checkAccess($instance->deny, $a)) {
                $instance->trigger($instance::EVENT_BEFORE_ACTION, [$a]);
                Hook::trigger($instance::EVENT_BEFORE_ACTION, [$a]);
                $response = call_user_func_array([$instance, $a], $this->parseArgs($method->getParameters()));
                $instance->trigger($instance::EVENT_AFTER_ACTION, [$a, $response]);
                Hook::trigger($instance::EVENT_AFTER_ACTION, [$a, $response]);
                echo($response instanceof $class ? '' : $response);
            } else {
                throw new \Exception('Page not found.', 404);
            }
        } else {
            throw new \Exception('Page not found.', 404);
        }
    }
    
    /**
     * 匹配到的方法将不能被访问，默认init|beforeAction|afterAction不能被访问
     * @param array $pregs 一个存放正则表达式的数组
     * @param string $action 方法名称
     * @return boolean 返回true允许访问，false禁止访问
     */
    private function checkAccess($pregs, $action)
    {
        $pregs = array_merge([
            '/^(init|beforeAction|afterAction)$/i',
        ], $pregs);
        foreach ($pregs as $pattern) {
            if (preg_match($pattern, $action)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * 返回方法所带的GET参数
     * @param array $params 一个\ReflectionParameter的数组
     * @return array 返回要带入执行方法的GET参数
     */
    private function parseArgs($params)
    {
        foreach ($params as $param) {
            if (($arg = get($param->name)) !== null) {
                $args[] = $arg;
            }
        }
        return isset($args) ? $args : [];
    }
}
