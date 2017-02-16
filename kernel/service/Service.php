<?php
namespace lying\service;

class Service
{
    /**
     * @var array 实例事件绑定数组
     */
    private $events = [];
    
    /**
     * 初始化子类的成员变量
     * @param array $params 参数,key=>value形式的数组
     */
    final public function __construct($params = [])
    {
        foreach ($params as $key=>$param) {
            $this->$key = $param;
        }
        $this->init($params);
    }
    
    /**
     * 子类可继承并接收一个参数,参数为构造函数接受的参数
     */
    protected function init() {}
    
    /**
     * 绑定一个函数到某个事件
     * @param string $id 事件的ID
     * @param callable $callback 绑定的事件
     */
    final public function bindEvent($id, callable $callback)
    {
        $this->events[$id][] = $callback;
    }
    
    /**
     * 触发一个事件
     * @param string $id 事件的ID
     * @param array $data 传到绑定的方法的参数,按照参数的顺序提供一个索引数组
     */
    final public function trigger($id, $data = [])
    {
        if (isset($this->events[$id])) {
            foreach ($this->events[$id] as $call) {
                call_user_func_array($call, $data);
            }
        }
    }
}
