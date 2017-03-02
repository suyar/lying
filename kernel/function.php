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

/**
 * url生成,支持反解析
 * @param string $path 要生成的相对路径
 * 如果路径post,则生成当前module,当前ctrl下的post方法;
 * 如果路径post/index,则生成当前module,ctrl为PostCtrl下的index方法;
 * 如果路径admin/post/index,则生成当前module为admin,ctrl为PostCtrl下的index方法;
 * @param array $params 要生成的参数,一个关联数组,如果有路由规则,参数中必须包含rule中的参数才能反解析
 * @return string
 */
function url($path, $params = [])
{
    return maker()->router()->createUrl($path, $params);
}

/**
 * 锁函数
 * @param strings $name 锁名称
 * @param integer $type 锁类型
 * LOCK_SH 共享锁
 * LOCK_EX 独占锁
 * LOCK_NB 非阻塞(Windows 上不支持),用法LOCK_EX | LOCK_NB
 * @return resource|boolean 成功返回锁文件句柄,失败返回false
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
 * @return boolean 成功返回true,失败返回false
 */
function unlock($handle)
{
    return is_resource($handle) ? flock($handle, LOCK_UN) && fclose($handle) : false;
}
