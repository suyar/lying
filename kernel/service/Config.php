<?php
namespace lying\service;

/**
 * 配置读取组件
 *
 * @author carolkey <me@suyaqi.cn>
 * @since 2.0
 * @link https://carolkey.github.io/
 * @license MIT
 */
class Config
{
    /**
     * @var array 缓存全局配置
     */
    private $config = [];
    
    /**
     * 返回某个配置的内容
     * @param string $name 配置文件名，如果文件不存在，抛出异常
     * @param string $key 要获取的键，如果键不存在，抛出异常
     * @return mixed 返回配置数组或者某个键的值
     * @throws \Exception 当配置文件不存在或者配置键不存在抛出异常
     */
    public function read($name, $key = null)
    {
        if (isset($this->config[$name])) {
            return $key === null ? $this->config[$name] : $this->config[$name][$key];
        } else {
            $this->config[$name] = require DIR_CONF . "/$name.php";
            return $key === null ? $this->config[$name] : $this->config[$name][$key];
        }
    }
    
    /**
     * 设置某个配置，配置的改变并不会改变配置文件，只会改变运行时的配置
     * @param string $name 配置文件名或者临时配置键名
     * @param array $params 配置数组，默认为空数组
     */
    public function write($name, $params = [])
    {
        $this->config[$name] = $params;
    }
}
