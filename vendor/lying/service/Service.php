<?php
namespace lying\service;

class Service
{
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
}