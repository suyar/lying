<?php
/**
 * 返回工厂类实例
 * @return \lying\service\Maker
 */
function maker()
{
    return Lying::$maker;
}

/**
 * 获取GET参数
 * @param string $key GET参数
 * @param string $default 默认值
 * @return string|null
 */
function get($key, $default = null)
{
    return isset($_GET[$key]) ? $_GET[$key] : $default;
}

/**
 * 获取POST参数
 * @param string $key POST参数
 * @param string $default 默认值
 * @return string|null
 */
function post($key, $default = null)
{
    return isset($_POST[$key]) ? $_POST[$key] : $default;
}
