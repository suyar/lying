<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

/**
 * 获取配置项
 * @param string $key 配置键名,支持'user.name'形式的读取方式
 * @param mixed $default 配置不存在时的默认值,默认为null
 * @return mixed 成功返回配置值,配置不存在返回默认值
 */
function config($key, $default = null)
{
    return \Lying::config($key, $default);
}

/**
 * 返回Maker实例
 * @return \lying\service\Maker
 */
function maker()
{
    return \Lying::$maker;
}

/**
 * 返回Request实例
 * @return \lying\service\Request
 */
function request()
{
    return \Lying::$maker->request;
}

/**
 * 返回Response实例
 * @return \lying\service\Response
 */
function response()
{
    return \Lying::$maker->response;
}

/**
 * 返回Router实例
 * @return \lying\service\Router
 */
function router()
{
    return \Lying::$maker->router;
}

/**
 * 返回Helper实例
 * @return \lying\service\Helper
 */
function helper()
{
    return \Lying::$maker->helper;
}

/**
 * 返回Session实例
 * @return \lying\service\Session
 */
function session()
{
    return \Lying::$maker->session;
}

/**
 * 返回Cookie实例
 * @return \lying\service\Cookie
 */
function cookie()
{
    return \Lying::$maker->cookie;
}

/**
 * 返回Hook实例
 * @return \lying\service\Hook
 */
function hook()
{
    return \Lying::$maker->hook;
}

/**
 * 返回Encrypter实例
 * @return \lying\service\Encrypter
 */
function encrypter()
{
    return \Lying::$maker->encrypter;
}

/**
 * 返回Dispatch实例
 * @return \lying\service\Dispatch
 */
function dispatch()
{
    return \Lying::$maker->dispatch;
}

/**
 * 返回View实例
 * @return \lying\view\View
 */
function view()
{
    return \Lying::$maker->view;
}

/**
 * 返回Redis实例
 * @param string $id 实例ID
 * @return \lying\service\Redis
 */
function redis($id = 'redis')
{
    return \Lying::$maker->redis($id);
}

/**
 * 返回Cache实例
 * @param string $id 实例ID
 * @return \lying\cache\Cache
 */
function cache($id = 'cache')
{
    return \Lying::$maker->cache($id);
}

/**
 * 返回Connection实例
 * @param string $id 实例ID
 * @return \lying\db\Connection
 */
function db($id = 'db')
{
    return \Lying::$maker->db($id);
}

/**
 * 返回Captcha实例
 * @param string $id 实例ID
 * @return \lying\captcha\Captcha
 */
function captcha($id = 'captcha')
{
    return \Lying::$maker->captcha($id);
}
