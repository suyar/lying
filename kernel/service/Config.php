<?php
namespace lying\service;

class Config extends Service
{
    /**
     * @var array 缓存全局配置
     */
    private $config = [];
    
    /**
     * 返回某个配置的内容
     * @param string $config 配置文件名,如果文件不存在，抛出异常
     * @return array
     */
    public function get($config)
    {
        if (isset($this->config[$config])) {
            return $this->config[$config];
        } else {
            $this->config[$config] = require DIR_CONF . "/$config.php";
            return $this->config[$config];
        }
    }
    
    /**
     * 设置某个配置,配置的改变并不会改变配置文件,只会改变运行时的配置
     * @param string $key 配置文件名或者临时配置键名
     * @param string $params 参数数组,默认为空
     */
    public function set($key, $params = [])
    {
        $this->config[$key] = $params;
    }
}
