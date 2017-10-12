<?php
/**
 * @author carolkey <me@suyaqi.cn>
 * @link https://github.com/carolkey/lying
 * @copyright 2017 Lying
 * @license MIT
 */

namespace lying\service;

/**
 * Class Router
 * @package lying\service
 * @since 2.0
 */
class Router extends Service
{
    /**
     * @var boolean 是否绑定模块
     */
    protected $binding;

    /**
     * @var array 存当前路由[module, controller, action]
     */
    private $router;

    /**
     * @var string 默认模块
     */
    protected $module = 'index';

    /**
     * @var string 默认控制器
     */
    protected $controller = 'index';

    /**
     * @var string 默认方法
     */
    protected $action = 'index';

    /**
     * @var boolean 是否PATHINFO
     */
    protected $pathinfo = false;

    /**
     * @var string 后缀
     */
    protected $suffix = '';

    /**
     * @var array 路由规则
     */
    protected $rule = [];

    /**
     * @var array 域名绑定模块
     */
    protected $host = [];

    /**
     * 判断域名是否绑定模块
     */
    protected function init()
    {
        $host = \Lying::$maker->request()->host();
        if (isset($this->host[$host])) {
            $this->binding = true;
            foreach ($this->host[$host] as $name => $value) {
                $this->$name = $value;
            }
        }
    }

    /**
     * 路由解析
     * @return array 返回路由数组[module, controller, action]
     * @throws \Exception 当后缀名不匹配的时候抛出404异常
     */
    public function resolve()
    {
        $request = \Lying::$maker->request();
        $uri = $request->isCli() ? $request->getArgv(1, '/') : $request->uri();
        //解析URI
        $parse = parse_url($uri);
        //解析原生GET,这里是为了去除转发规则中$_GET本身中无用的参数
        parse_str(isset($parse['query']) ? $parse['query'] : '', $_GET);
        //去掉index.php
        $path = trim(preg_replace('/^\/index\.php/i', '', $parse['path'], 1), '/');
        //匹配后缀名
        if ($path && $this->suffix) {
            $path = rtrim(preg_replace('/\\' . $this->suffix . '$/i', '', $path, 1), '/');
        }
        //分割后对每个元素进行URL解码
        $pathArray = empty($path) ? [] : array_map(function ($val) {
            return urldecode($val);
        }, explode('/', $path));
        //路由匹配
        $this->resolveRule($pathArray);
        //获取模块/控制器/方法
        $m = $this->binding ? $this->module : (($module = array_shift($pathArray)) ? $module : $this->module);
        $c = ($controller = array_shift($pathArray)) ? $controller : $this->controller;
        $a = ($action = array_shift($pathArray)) ? $action : $this->action;
        //存下当前的路由,全部小写,没有转换成驼峰
        $this->router = [strtolower($m), strtolower($c), strtolower($a)];
        //解析多余的参数到GET
        while ($pathArray) {
            $key = array_shift($pathArray);
            $value = array_shift($pathArray);
            $_GET[$key] = $value === null ? '' : $value;
        }
        //把GET参数载入REQUEST
        \Lying::$maker->request()->load($_GET);
        //转换为驼峰,返回当前请求的模块/控制器/方法
        return [
            $this->str2hump($this->router[0]),
            $this->str2hump($this->router[1], true).'Ctrl',
            $this->str2hump($this->router[2])
        ];
    }

    /**
     * 路由规则解析
     * @param array $pathArray 路由数组
     */
    private function resolveRule(&$pathArray)
    {
        if ($pathArray) {
            $pathNum = count($pathArray);
            foreach ($this->rule as $pattern => $rule) {
                //是完全匹配
                $pattern = preg_replace('/\$$/', '', $pattern, 1, $absolute);
                $patternArr = explode('/', $pattern);
                $patternNum = count($patternArr);
                //个数不匹配,匹配下一个
                if ($absolute && $pathNum !== $patternNum || !$absolute && $pathNum < $patternNum) {
                    continue;
                }
                //参数映射
                $params = [];
                foreach ($patternArr as $i => $r) {
                    if (strncmp($r, ':', 1) === 0 && ($key = ltrim($r, ':'))) {
                        if (isset($rule[$key]) && !preg_match($rule[$key], $pathArray[$i])) {
                            continue 2;
                        } else {
                            array_push($params, $key, $pathArray[$i]);
                        }
                    } elseif ($pathArray[$i] !== $r) {
                        continue 2;
                    }
                }
                //匹配到就不进行下一条匹配
                $pathArray = array_merge(explode('/', $rule[0]), $params, array_splice($pathArray, $patternNum));
                break;
            }
        }
    }

    /**
     * 把横线分割的小写字母转换为驼峰
     * @param string $str 要转换的字符串
     * @param boolean $ucfirst 首字母是否大写
     * @return string 返回转换后的字符串
     */
    private function str2hump($str, $ucfirst = false)
    {
        $str = str_replace(' ', '', ucwords(str_replace('-', ' ', $str)));
        return $ucfirst ? $str : lcfirst($str);
    }

    /**
     * 返回此次请求的模块ID
     * @return string
     */
    public function module()
    {
        return $this->router[0];
    }

    /**
     * 返回此次请求的控制器ID
     * @return string
     */
    public function controller()
    {
        return $this->router[1];
    }

    /**
     * 返回此次请求的方法ID
     * @return string
     */
    public function action()
    {
        return $this->router[2];
    }

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
    public function createUrl($path, $params = [], $normal = false)
    {
        if (strncmp($path, '/', 1) === 0) {
            $route = rtrim($path, '/');
        } elseif ($path = trim($path, '/')) {
            $routeArr = explode('/', $path);
            switch (count($routeArr)) {
                case 1:
                    $route = implode('/', $this->binding ? [
                        $this->controller(), $routeArr[0]
                    ] : [
                        $this->module(), $this->controller(), $routeArr[0]
                    ]);
                    break;
                case 2:
                    $route = implode('/', $this->binding ? [
                        $routeArr[0], $routeArr[1]
                    ] : [
                        $this->module(), $routeArr[0], $routeArr[1]
                    ]);
                    break;
                case 3:
                    $route = implode('/', $this->binding ? [
                        $this->controller(), $this->action()
                    ] : $routeArr);
                    break;
                default:
                    $route = implode('/', $this->router);
            }
        } else {
            $route = implode('/', $this->binding ? [
                $this->controller(), $this->action()
            ] : $this->router);
        }
        //路由反解析
        foreach ($this->rule as $r => $v) {
            $r = preg_replace('/\$$/', '', $r, 1, $absolute);
            if ($route === $v[0] && false !== preg_match_all('/:([^\/]+)/', $r, $matchs)) {
                $replace = [];
                foreach ($matchs[1] as $k) {
                    if (!isset($params[$k]) || isset($v[$k]) && !preg_match($v[$k], $params[$k])) {
                        $absolute = false;
                        continue 2;
                    }
                    $replace[] = urlencode($params[$k]);
                }
                $params = array_diff_key($params, array_flip($matchs[1]));
                $keys = array_map(function ($val) {
                    return ":$val";
                }, $matchs[1]);
                $route = str_replace($keys, $replace, $r);
                break;
            }
            $absolute = false;
        }
        //拼接URL
        $url = ($this->pathinfo ? '/index.php/' : '/') . trim($route, '/');
        //如果为完全匹配,多余的参数形式就用[?a=1&b=2]
        if ($normal || isset($absolute) && $absolute) {
            $query = http_build_query($params, '', '&');
            $url .= $this->suffix ? $this->suffix : '';
            $url .= $query ? "?$query" : '';
        } else {
            $p = [];
            foreach ($params as $name => $value) {
                if (in_array(gettype($value), ['string', 'integer', 'double', 'boolean']) && $value !== '') {
                    $p[$name] = $value;
                }
            }
            $query = str_replace(['=', '&'], '/', http_build_query($p, '', '&'));
            $url .= $query ? "/$query" : '';
            $url .= $this->suffix ? $this->suffix : '';
        }
        return \Lying::$maker->request()->host(true) . $url;
    }
}
