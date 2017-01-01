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
 * @param string $key GET参数,放空为获取所有GET参数
 * @param string $default 默认值
 * @return string|null|array
 */
function get($key = null, $default = null)
{
    return $key === null ? $_GET : (isset($_GET[$key]) ? $_GET[$key] : $default);
}

/**
 * 获取POST参数
 * @param string $key POST参数,放空为获取所有POST参数
 * @param string $default 默认值
 * @return string|null|array
 */
function post($key = null, $default = null)
{
    return $key === null ? (isset($_POST[$key]) ? $_POST[$key] : $default) : $_POST;
}
