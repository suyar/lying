<?php
namespace lying\service;

class Dispatch extends Service
{
    /**
     * 程序执行入口
     * @throws \Exception
     */
    public function runAction()
    {
        list($m, $c, $a) = maker()->router()->parse();
        $class = "module\\$m\\ctrl\\$c";
        if (class_exists($class) && method_exists($class, $a)) {
            $instance = new $class();
            $method = new \ReflectionMethod($instance, $a);
            if ($method->isPublic() && $this->checkAccess($instance->deny, $a)) {
                $this->trigger(\lying\base\Ctrl::EVENT_BEFORE_ACTION, [$a]);
                $responce = call_user_func_array([$instance, $a], $this->parseArgs($method->getParameters()));
                $this->trigger(\lying\base\Ctrl::EVENT_AFTER_ACTION, [$a]);
                exit($responce instanceof $class ? 0 : $responce);
            } else {
                throw new \Exception('Page not found.', 404);
            }
        } else {
            throw new \Exception('Page not found.', 404);
        }
    }
    
    /**
     * 匹配到的方法将不能被访问,默认init|beforeAction|afterAction不能被访问
     * @param array $pregs 一个存放正则表达式的数组
     * @param string $action 方法名称
     * @return boolean true允许访问,false禁止访问
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
     * @return array
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
