<?php
/**
 * URL生成
 * ```php
 * 路径[/index/blog/info],生成[/index/blog/info],使用此形式的时候请注意参数匹配,并且不会路由反解析
 * 路径[post],生成[当前模块/当前控制器/post]
 * 路径[post/index],生成[当前模块/post/index]
 * 路径[admin/post/index],生成[admin/post/index],注意:此用法在模块绑定中并不适用
 * 携带在PATH中的GET参数类型只能是['string', 'integer', 'double', 'boolean'],否则被忽略
 * 注意:boolean会被转换成0和1,值为空字符串也会被忽略
 * 如果需要携带其他类型的参数,请设置为normal形式的查询字符串
 * ```
 * @param string $path 要生成的相对路径
 * @param array $params 要生成的参数,一个关联数组,如果有路由规则,参数中必须包含rule中的参数才能反解析
 * @param boolean $normal 是否把参数设置成?a=1&b=2
 * @return string 返回生成的URL
 */
function url($path, $params = [], $normal = false)
{
    return \Lying::$maker->router()->createUrl($path, $params, $normal);
}
