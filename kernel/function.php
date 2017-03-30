<?php
/**
 * 获取GET参数
 * @param string $key GET参数，放空为获取所有GET参数
 * @param string $default 默认值
 * @return string|null|array 成功返回键值，键不存在返回null，没有传入key返回GET数组
 */
function get($key = null, $default = null)
{
    return $key === null ? $_GET : (isset($_GET[$key]) ? $_GET[$key] : $default);
}

/**
 * 获取POST参数
 * @param string $key POST参数,放空为获取所有POST参数
 * @param string $default 默认值
 * @return string|null|array 成功返回键值，键不存在返回null，没有传入key返回POST数组
 */
function post($key = null, $default = null)
{
    return $key === null ? $_POST : (isset($_POST[$key]) ? $_POST[$key] : $default);
}

/**
 * URL生成
 * @see \lying\service\Router::createUrl()
 * @param string $path 要生成的相对路径
 * @param array $params URL带的参数，为一个关联数组
 * @param boolean $normal 是否把参数设置成?a=1&b=2
 * @return string 返回生成的URL
 */
function url($path, $params = [], $normal = false)
{
    return \Lying::$maker->router()->createUrl($path, $params, $normal);
}

/**
 * 锁函数
 * @param string $name 锁名称
 * @param integer $type 锁类型
 * LOCK_SH 共享锁
 * LOCK_EX 独占锁
 * LOCK_NB 非阻塞(Windows上不支持)，用法LOCK_EX | LOCK_NB
 * @return resource|boolean 成功返回锁文件句柄，失败返回false
 */
function lock($name, $type)
{
    if (is_dir(ROOT . '/runtime/lock') || mkdir(ROOT . '/runtime/lock', 0777, true)) {
        if (false !== $fp = fopen(ROOT . '/runtime/lock/' . $name, 'w')) {
            if (flock($fp, $type)) {
                return $fp;
            } else {
                fclose($fp);
                return false;
            }
        }
    }
    return false;
}

/**
 * 解锁
 * @param resource $handle 锁句柄
 * @return boolean 成功返回true，失败返回false
 */
function unlock($handle)
{
    return is_resource($handle) ? flock($handle, LOCK_UN) && fclose($handle) : false;
}
