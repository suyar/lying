<?php
namespace lying\service;

class Config
{
    /**
     * 缓存全局配置
     * @var array
     */
    private $config = [];
    
    /**
     * 返回某个配置文件的内容
     * @param string $config 配置文件名
     * @return array
     */
    public function get($config)
    {
        if (isset($this->config[$config])) {
            return $this->config[$config];
        }else {
            $this->config[$config] = require DIR_CONF . "/$config.php";
            return $this->config[$config];
        }
    }
    
    /**
     * 重置某个配置
     * @param string $key 配置文件名
     * @param string $params 参数数组，默认为空
     */
    public function set($key, $params = [])
    {
        if (isset($this->config[$key])) {
            $this->config[$key] = $params;
        }
    }
}