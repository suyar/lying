<?php
/**
 * 获取GET/POST参数
 * @param string $key 要获取的键，值为empty时为返回整个GET/POST数组
 * @param boolean $post 是否为POST
 * @param boolean $raw 是否获取原生POST数据
 * @return mixed 获取的数据，失败返回false
 */
function req($key = null, $post = false, $raw = false)
{
    if ($post) {
        return $raw ? file_get_contents('php://input') : (
            empty($key) ? $_POST : (isset($_POST[$key]) ? $_POST[$key] : false)
        );
    } else {
        return empty($key) ? $_GET : (isset($_GET[$key]) ? $_GET[$key] : false);
    }
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
    if (is_dir(DIR_ROOT . '/runtime/' . 'lock') ||
        mkdir(DIR_ROOT . '/runtime/' . 'lock', 0777, true)) {
        if (false !== $fp = fopen(DIR_ROOT . "/runtime//lock/$name", 'w')) {
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
