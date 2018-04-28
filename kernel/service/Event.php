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
    public $name;

    /**
     * @var object 触发事件的对象
     */
    public $sender;

    /**
     * @var mixed 绑定时的数据
     */
    public $data;

    /**
     * @var bool 是否此事件执行完后不再执行后面其他事件,此参数仅在事件处理函数设置有效
     */
    public $stop = false;

    /**
     * @var array 类事件容器
     */
    private static $_events = [];

    /**
     * Event constructor.
     * @param array $attr
     */
    final public function __construct(array $attr = [])
    {
        foreach ($attr as $name => $value) {
            $this->$name = $value;
        }
    }

    /**
     * 绑定事件
     * @param string $class 绑定的完整类名
     * @param string $name 事件名
     * @param callable $handler 事件触发处理程序
     * @param mixed $data 当事件被触发时要传递给事件处理程序的数据
     * @param bool $append 是否插入在事件队列的末尾,默认true,若果为false则插入到事件处理队列的首位
     */
    final public static function on($class, $name, callable $handler, $data = null, $append = true)
    {
        $class = ltrim($class, '\\');
        if ($append || empty(self::$_events[$name][$class])) {
            self::$_events[$name][$class][] = [$handler, $data];
        } else {
            array_unshift(self::$_events[$name][$class], [$handler, $data]);
        }
    }

    /**
     * 移除事件
     * @param string $class 绑定的完整类名
     * @param string $name 事件名
     * @param callable $handler 要移除的事件处理程序,如果放空或者设置null,则删除该事件下的所有处理程序
     * @return bool 成功返回true,失败返回false
     */
    final public static function off($class, $name, callable $handler = null)
    {
        $class = ltrim($class, '\\');
        if (isset(self::$_events[$name][$class])) {
            if ($handler === null) {
                unset(self::$_events[$name][$class]);
                return true;
            } else {
                $removed = false;
                foreach (self::$_events[$name][$class] as $i => $event) {
                    if ($event[0] === $handler) {
                        unset(self::$_events[$name][$class][$i]);
                        $removed = true;
                    }
                }
                if ($removed) {
                    self::$_events[$name][$class] = array_values(self::$_events[$name][$class]);
                    return $removed;
                }
            }
        }
        return false;
    }

    /**
     * 触发事件,如果事件返回false或者$event->stop被设置为true就不再继续执行后面绑定的事件了
     * @param string|object $class 绑定的完整类名
     * @param string $name 事件名
     * @param Event $event 事件实例
     */
    final public static function trigger($class, $name, Event $event = null)
    {
        $event === null && ($event = new static());
        $event->name = $name;
        $event->stop = false;
        if (is_object($class)) {
            $event->sender = $class;
            $class = get_class($class);
        } else {
            $class = ltrim($class, '\\');
        }
        if (isset(self::$_events[$name][$class])) {
            foreach (self::$_events[$name][$class] as $handler) {
                $event->data = $handler[1];
                $return = call_user_func($handler[0], $event);
                if (false === $return || $event->stop) {
                    return;
                }
            }
        }
    }
}
