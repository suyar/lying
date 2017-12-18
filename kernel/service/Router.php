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
        $this->initRule();
        $this->parse();
    }

    /**
     * 预解析路由规则
     */
    private function initRule()
    {
        $rules = [];
        if (is_array($this->rule) && $this->rule) {
            foreach ($this->rule as $pattern => $rule) {
                $temp = [];
                $pattern = preg_replace('/\$$/', '', $pattern, 1, $temp['absolute']);
                $temp['pattern'] = $pattern;
                $temp['router'] = $rule[0];
                $temp['suffix'] = isset($rule[1]) ? $rule[1] : false;
                if (preg_match_all('/<([^<>]+)>/', $pattern, $matches)) {
                    foreach ($matches[1] as $k => $match) {
                        if (strpos($match, ':')) {
                            list($col, $reg) = explode(':', $match);
                            $temp['params'][$col] = ['reg'=>$reg, 'rep'=>$matches[0][$k], 'name'=>$col];
                        } else {
                            $temp['params'][$match] = ['reg'=>false, 'rep'=>$matches[0][$k], 'name'=>$match];
                        }
                    }
                } else {
                    $temp['params'] = [];
                }
                $rules[] = $temp;
            }
        }
        $this->rule = $rules;
    }

    /**
     * 解析路由规则
     * @param string $path 请求路径
     * @return bool 匹配到了返回true,没有匹配返回false
     */
    private function parseRule($path)
    {
        foreach ($this->rule as $rule) {
            //匹配后缀
            if ($rule['suffix']) {
                $len = strlen($rule['suffix']);
                if (substr_compare($path, $rule['suffix'], -$len, $len) === 0) {
                    $path = substr_replace($path, '', -$len, $len);
                } else {
                    continue;
                }
            }

            //匹配参数个数
            $pathArr = array_map(function ($val) {
                return urldecode($val);
            }, explode('/', $path));
            $patternArr = explode('/', $rule['pattern']);
            $count_path = count($pathArr);
            $count_pattern = count($patternArr);
            if ($count_path < $count_pattern || $rule['absolute'] && $count_path !== $count_pattern) {
                continue;
            }

            //匹配路由规则
            $cols = $rule['params'];
            $params = [];
            foreach ($patternArr as $k => $val) {
                if (strpos($val, '<') === 0) {
                    $col = array_shift($cols);
                    if ($col['reg'] && !preg_match("/{$col['reg']}/", $pathArr[$k])) {
                        continue 2;
                    } else {
                        $params[$col['name']] = $pathArr[$k];
                    }
                } elseif (strcmp($val, $pathArr[$k]) !== 0) {
                    continue 2;
                }
            }

            //匹配成功
            $_GET = array_merge($params, $_GET);
            while (array_slice($pathArr, $count_path)) {
                $k = array_shift($pathArr);
                $v = array_shift($pathArr);
                $_GET[$k] = $v;
            }
            $routerArr = explode('/', $rule['router']);
            $this->router = [
                strtolower($this->binding ? $this->module : array_shift($routerArr)),
                strtolower(array_shift($routerArr)),
                strtolower(array_shift($routerArr)),
            ];
            return true;
        }
        return false;
    }

    /**
     * 常规解析路由
     * @param string $path 请求路径
     * @return bool 无论什么情况都返回true
     */
    private function parseNormal($path)
    {
        if ($path && $this->suffix) {
            $len = strlen($this->suffix);
            if (substr_compare($path, $this->suffix, -$len, $len) === 0) {
                substr_replace($path, $this->suffix, -$len, $len);
            }
        }

        $pathArr = array_map(function ($val) {
            return urldecode($val);
        }, explode('/', $path));

        $this->router = [
            strtolower($this->binding ? $this->module : (array_shift($pathArr) ?: $this->module)),
            strtolower(array_shift($pathArr) ?: $this->controller),
            strtolower(array_shift($pathArr) ?: $this->action),
        ];

        while ($pathArr) {
            $k = array_shift($pathArr);
            $v = array_shift($pathArr);
            $_GET[$k] = $v;
        }

        return true;
    }

    /**
     * 解析请求路由
     */
    private function parse()
    {
        $request = \Lying::$maker->request();
        $uri = $request->isCli() ? $request->getArgv(1, '/') : $request->uri();
        $parse = parse_url($uri);
        isset($parse['query']) && parse_str($parse['query'], $_GET);
        $path = preg_replace('/^\/index\.php/i', '', $parse['path'], 1);
        $path = trim($path, '/');
        $path && $this->parseRule($path) || $this->parseNormal($path);
        $request->load();
    }

    /**
     * 把横线分割的小写字母转换为驼峰
     * @param string $str 要转换的字符串
     * @param string $delimiter 分隔符
     * @param boolean $ucfirst 首字母是否大写
     * @return string 返回转换后的字符串
     */
    private function str2hump($str, $delimiter, $ucfirst = false)
    {
        $str = str_replace(' ', '', ucwords(str_replace($delimiter, ' ', $str)));
        return $ucfirst ? $str : lcfirst($str);
    }

    /**
     * 返回可供调度器使用的数组
     * @return array
     */
    public function resolve()
    {
        return [
            $this->str2hump($this->router[0], '-'),
            $this->str2hump($this->router[0], '-', true) . 'Ctrl',
            $this->str2hump($this->router[0], '-'),
        ];
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
     * @param string $path 路径
     * @param array $params 参数数组,不合法的参数将被剔除
     * @param bool $host 是否携带完整域名,包含协议头,默认是
     * @param bool $normal 是否把参数设置成?a=1&b=2,默认否,优先pathinfo
     * @return string
     */
    public function createUrl1($path, array $params = [], $host = true, $normal = false)
    {
        $host = $host ? \Lying::$maker->request()->host(true) : '';
        if (strncmp($path, '/', 1) === 0 || !$path) {
            $path = rtrim($path, '?&');
            strpos($path, '.') || ($path = rtrim($path, '/') . '/');
            if ($query =http_build_query($params, '', '&', PHP_QUERY_RFC3986)) {
                $query = (strpos($path, '?') ? '&' : '?') . $query;
            }
            return $host . $path . $query;
        } else {
            $pathArr = explode('/', rtrim($path, '/'));
            switch (count($pathArr)) {
                case 3:
                    $routeArr = [$this->binding ? $this->module : $pathArr[0], $pathArr[1], $pathArr[2]];
                    break;
                case 2:
                    $routeArr = [$this->module, $pathArr[0], $pathArr[1]];
            }
        }
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
