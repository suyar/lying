<?php
namespace lying\service;

class Container
{
    /**
     * 已经实例化的服务
     * @var array
     */
    private $instance = [];
    
    /**
     * 注册的服务
     * @var array
     */
    private $register = [];
    
    /**
     * 实例化的时候注册进去
     * @param array $params 配置
     */
    public function __construct($params)
    {
        $this->register = $params;
    }
    
    /**
     * 获取服务
     * @param string $id 服务id
     * @throws \Exception
     * @return mixed 返回对应服务的实例
     */
    public function make($id)
    {
        if (isset($this->instance[$id])) {
            return $this->instance[$id];
        }elseif (isset($this->register[$id])) {
            if (is_array($this->register[$id]) && isset($this->register[$id]['class'])) {
                $class = array_shift($this->register[$id]);
                $this->instance[$id] = new $class($this->register[$id]);
            }elseif (is_string($this->register[$id])) {
                $this->instance[$id] = new $this->register[$id]();
            }else {
                throw new \Exception("Service configuration error.");
            }
            unset($this->register[$id]);
            return $this->instance[$id];
        }else {
            throw new \Exception("Service $id not found.");
        }
    }
}