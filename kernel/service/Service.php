<?php
namespace lying\service;

/**
 * 服务组件基类
 *
 * @author carolkey <me@suyaqi.cn>
 * @since 2.0
 * @link https://carolkey.github.io/
 * @license MIT
 */
class Service
{
    /**
     * @var array 实例事件绑定容器
     */
    private $events = [];
    
    /**
     * 初始化子类的公有、受保护成员变量
     * @param array $params 参数，一个关联数组
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
     */
    final public function hook($id, callable $callback)
    {
        $this->events[$id][] = $callback;
    }
    
    /**
     * 触发一个事件，如果事件返回false的话就不再继续执行后面绑定的事件了
     * @param string $id 事件的ID
     * @param array $data 传到绑定的方法的参数，按照参数的顺序提供一个索引数组
     */
    final public function trigger($id, $data = [])
    {
        if (isset($this->events[$id])) {
            foreach ($this->events[$id] as $call) {
                if (is_callable($call) && false === call_user_func_array($call, $data)) {
                    break;
                }
            }
        }
    }

    /**
     * 移除事件
     * @param string $id 事件的ID
     * @param callable|null $callback 要移除的事件函数
     * @return boolean 成功返回true，失败返回false
     */
    final public function unhook($id, callable $callback = null)
    {
        if (isset($this->events[$id])) {
            if ($callback === null) {
                $this->events[$id] = [];
                return true;
            } else {
                foreach ($this->events[$id] as $key => $event) {
                    if ($event === $callback) {
                        unset($this->events[$id][$key]);
                        return true;
                    }
                }
            }
        }
        return false;
    }
}
