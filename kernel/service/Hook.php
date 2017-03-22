<?php
namespace lying\service;

/**
 * 钩子组件
 *
 * @author carolkey <me@suyaqi.cn>
 * @since 2.0
 * @link https://carolkey.github.io/
 * @license MIT
 */
class Hook
{
    /**
     * 框架加载完成事件
     */
    const APP_READY = 'appReady';

    /**
     * 请求结束事件
     */
    const APP_END = 'appEnd';

    /**
     * 框架出错事件
     */
    const APP_ERROR = 'appError';

    /**
     * @var bool 是否初始化过，防止用户重新载入
     */
    private static $isInit = false;

    /**
     * @var array 钩子函数容器
     */
    private static $events = [];

    /**
     * 载入预定义钩子函数
     */
    public static function init()
    {
        if (!self::$isInit) {
            self::$events = \Lying::$maker->config()->read('hook');
            self::$isInit = true;
        }
    }

    /**
     * 绑定触发函数到钩子
     * @param string $id 钩子ID
     * @param callable $callback 钩子触发函数
     */
    public static function hook($id, callable $callback)
    {
        self::$events[$id][] = $callback;
    }

    /**
     * 触发钩子事件
     * @param string $id 钩子ID
     * @param array $data 要传入触发事件的参数，按照参数的顺序提供一个索引数组
     */
    public static function trigger($id, $data = [])
    {
        if (isset(self::$events[$id])) {
            foreach (self::$events[$id] as $call) {
                if (is_callable($call) && false === call_user_func_array($call, $data)) {
                    break;
                }
            }
        }
    }

    /**
     * 移除钩子函数
     * @param string $id 钩子ID
     * @param callable|null $callback 要移除钩子触发函数
     * @return boolean 成功返回true,失败返回false
     */
    public static function unhook($id, callable $callback = null)
    {
        if (isset(self::$events[$id])) {
            if ($callback === null) {
                self::$events[$id] = [];
                return true;
            } else {
                foreach (self::$events[$id] as $key => $event) {
                    if ($event === $callback) {
                        unset(self::$events[$id][$key]);
                        return true;
                    }
                }
            }
        }
        return false;
    }
}
