<?php
namespace lying\service;

class Service
{
    /**
     * 初始化成员变量
     * @param array $params 参数,key/value形式的数组
     */
    final public function __construct($params = [])
    {
        if ($params) {
            foreach ($params as $key=>$param) {
                $this->$key = $param;
            }
        }
        if (method_exists($this, 'init')) {
            $this->init($params);
        }
    }
    
    /**
     * 返回Lying实例
     * @return \Lying
     */
    final public function make()
    {
        return \Lying::instance();
    }
}