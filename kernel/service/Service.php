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
     * @param array $params 参数,一个关联数组
     */
    final public function __construct($params = [])
    {
        foreach ($params as $key=>$param) {
            $this->$key = $param;
        }
        $this->init();
    }
    
    /**
     * 子类可继承此方法进行初始化操作
     */
    protected function init() {}

    /**
     * 绑定一个函数到某个事件
     * @param string $id 事件的ID
     * @param callable $callback 绑定的函数
     * @param mixed $data 传递到事件触发器的data
     */
    final public function hook($id, callable $callback, $data = null)
    {
        $this->_events[$id][] = [$callback, $data];
    }

    /**
     * 触发一个事件,如果事件返回false的话就不再继续执行后面绑定的事件了
     * @param string $id 事件的ID
     * @param Event|null $event
     */
    final public function trigger($id, Event $event = null)
    {
        if (isset($this->_events[$id])) {
            $event === null && ($event = new Event());
            $event->_name = $id;
            $event->_sender = $this;
            foreach ($this->_events[$id] as $call) {
                $event->_data = $call[1];
                if (false === call_user_func($call[0], $event)) {
                    break;
                }
            }
        }
    }

    /**
     * 移除事件
     * @param string $id 事件的ID
     * @param callable|null $callback 要移除的事件函数
     * @return bool 成功返回true,失败返回false
     */
    final public function unhook($id, callable $callback = null)
    {
        if (isset($this->_events[$id])) {
            if ($callback === null) {
                unset($this->_events[$id]);
                return true;
            } else {
                foreach ($this->_events[$id] as $key => $event) {
                    if ($event[0] === $callback) {
                        unset($this->_events[$id][$key]);
                        $this->_events[$id] = array_values($this->_events[$id]);
                        return true;
                    }
                }
            }
        }
        return false;
    }
}
