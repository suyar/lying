<?php
namespace lying\service;

class Hook
{
    /**
     * @var bool 是否初始化过,防止用户重新载入
     */
    private static $isInit = false;

    /**
     * @var array 全局钩子事件
     */
    private static $events = [];

    /**
     * 载入预定义钩子
     */
    public static function init()
    {
        if (!self::$isInit) {
            self::$events = maker()->config()->get('hook');
            self::$isInit = true;
        }
    }

    /**
     * 绑定触发函数到钩子
     * @param string $id 钩子ID
     * @param callable $callback 要执行的函数
     */
    public static function hook($id, callable $callback)
    {
        self::$events[$id][] = $callback;
    }

    /**
     * 触发钩子事件
     * @param string $id 钩子ID
     * @param array $data 要传入触发事件的参数,按照参数的顺序提供一个索引数组
     */
    public static function trigger($id, $data = [])
    {
        if (isset(self::$events[$id])) {
            foreach (self::$events[$id] as $call) {
                if (is_callable($call) && call_user_func_array($call, $data)) {
                    break;
                }
            }
        }
    }
}
