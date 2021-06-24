<?php
/**
 * URL生成
 * ```php
 * 路径[/index/blog/info],生成[/index/blog/info],使用此形式的时候请注意参数匹配,并且不会路由反解析
 * 路径[post],生成[当前模块/当前控制器/post]
 * 路径[post/index],生成[当前模块/post/index]
 * 路径[admin/post/index],生成[admin/post/index],注意:此用法在模块绑定中并不适用,模块绑定模式下,只需要最多到控制器就行
 * ```
 * @param string $path 要生成的相对路径
 * @param array $params 要生成的参数,一个关联数组,如果有路由规则,参数中必须包含rule中的参数才能反解析
 * @param bool $host 是否携带完整域名,包括协议头
 * @param bool $normal 是否把参数设置成?a=1&b=2
 * @return string 返回生成的URL
 */
function url($path, $params = [], $host = true, $normal = false)
{
    return \Lying::$maker->router->createUrl($path, $params, $host, $normal);
}

/**
 * 获取GET参数
 * @param string $name 参数名
 * @param mixed $defaultValue 默认值
 * @return mixed
 */
function G($name = null, $defaultValue = null)
{
    return Lying::$maker->request->get($name, $defaultValue);
}

/**
 * 获取POST参数
 * @param string $name 参数名
 * @param null $defaultValue 默认值
 * @return mixed
 */
function P($name = null, $defaultValue = null)
{
    return Lying::$maker->request->post($name, $defaultValue);
}
