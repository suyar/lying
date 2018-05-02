<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\service;

/**
 * Class Service
 * @package lying\service
 */
class Service
{
    /**
     * @var array 实例事件绑定容器
     */
    private $_events = [];
    
    /**
     * 初始化子类的公有&受保护成员变量
     * @param array $config 参数,一个关联数组
     */
    final public function __construct(array $config = [])
    {
        foreach ($config as $name => $value) {
            property_exists($this, $name) && ($this->$name = $value);
        }
        $this->init();
    }
    
    /**
     * 子类可继承此方法进行初始化操作
     */
    protected function init() {}

    /**
     * 绑定事件
     * @param string $name 事件名
     * @param callable $handler 事件处理程序
     * @param mixed $data 当事件被触发时要传递给事件处理程序的数据
     * @param bool $append 是否插入在事件队列的末尾,默认true,若果为false则插入到事件处理队列的首位
     */
    final public function on($name, callable $handler, $data = null, $append = true)
    {
        if ($append || empty($this->_events[$name])) {
            $this->_events[$name][] = [$handler, $data];
        } else {
            array_unshift($this->_events[$name], [$handler, $data]);
        }
    }

    /**
     * 移除事件
     * @param string $name 事件名
     * @param callable $handler 要移除的事件处理程序,如果放空或者设置null,则删除该事件下的所有处理程序
     * @return bool 成功返回true,失败返回false
     */
    final public function off($name, callable $handler = null)
    {
        if (isset($this->_events[$name])) {
            if ($handler === null) {
                unset($this->_events[$name]);
                return true;
            } else {
                $removed = false;
                foreach ($this->_events[$name] as $i => $event) {
                    if ($event[0] === $handler) {
                        unset($this->_events[$name][$i]);
                        $removed = true;
                    }
                }
                if ($removed) {
                    $this->_events[$name] = array_values($this->_events[$name]);
                    return $removed;
                }
            }
        }
        return false;
    }

    /**
     * 触发事件,如果事件返回false或者$event->stop被设置为true就不再继续执行后面绑定的事件了
     * @param string $name 事件名
     * @param Event $event 事件实例
     */
    final public function trigger($name, Event $event = null)
    {
        if (isset($this->_events[$name])) {
            $event === null && ($event = new Event());
            $event->sender = $this;
            $event->name = $name;
            $event->stop = false;
            foreach ($this->_events[$name] as $handler) {
                $event->data = $handler[1];
                $return = call_user_func($handler[0], $event);
                if (false === $return || $event->stop) {
                    return;
                }
            }
        }
        Event::trigger($this, $name, $event);
    }
}
