<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\service;

/**
 * Class Event
 * @package lying\service
 */
class Event
{
    /**
     * @var string 事件名称
     */
    public $_name;

    /**
     * @var object 出发事件的对象
     */
    public $_sender;

    /**
     * @var mixed 绑定时候传输的数据
     */
    public $_data;

    /**
     * @var array 类事件容器
     */
    private static $_events = [];

    /**
     * Event constructor.
     * @param array $attr
     */
    public function __construct(array $attr = [])
    {
        foreach ($attr as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * 绑定一个函数到某个事件
     * @param string $class 绑定的完整类名
     * @param string $id 事件ID
     * @param callable $callback 事件触发函数
     * @param mixed $data 传递到事件触发器的数据
     */
    final public static function hook($class, $id, callable $callback, $data = null)
    {
        self::$_events[$id][$class][] = [$callback, $data];
    }

    /**
     * 触发一个事件,如果事件返回false的话就不再继续执行后面绑定的事件了
     * @param string|object $class 绑定的完整类名
     * @param string $id 事件ID
     * @param Event|null $event 事件实例
     */
    final public static function trigger($class, $id, Event $event = null)
    {
        $event === null && ($event = new Event());
        if (is_object($class)) {
            $event->_sender = $class;
            $class = get_class($class);
        }
        if (isset(self::$_events[$id][$class])) {
            foreach (self::$_events[$id][$class] as $call) {
                $event->_data = $call[1];
                if (false === call_user_func($call[0], $event)) {
                    break;
                }
            }
        }
    }

    /**
     * 移除事件
     * @param string $class 绑定的完整类名
     * @param string $id 事件ID
     * @param callable|null $callback 事件触发函数
     * @return bool 成功返回true,失败返回false
     */
    final public static function unhook($class, $id, callable $callback = null)
    {
        if (isset(self::$_events[$id][$class])) {
            if ($callback === null) {
                unset(self::$_events[$id][$class]);
                return true;
            } else {
                foreach (self::$_events[$id][$class] as $key => $event) {
                    if ($event[0] === $callback) {
                        unset(self::$_events[$id][$class][$key]);
                        self::$_events[$id][$class] = array_values(self::$_events[$id][$class]);
                        return true;
                    }
                }
            }
        }
        return false;
    }
}
