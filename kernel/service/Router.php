<?php
/**
 * @author carolkey <me@suyaqi.cn>
 * @since 2.0
 * @link https://github.com/carolkey/lying
 * @license MIT
 */
namespace lying\service;

/**
 * Class Router
 * @package lying\service
 */
class Router extends Service
{
    /**
     * @var boolean 是否绑定模块
     */
    private $binding;

    /**
     * @var boolean 是否PATHINFO
     */
    private $pathinfo;

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
            foreach ($this->host as $name => $value) {
                $this->$name = $value;
            }
        }
    }

    /**
     * 解析路由
     * @return array 返回路由数组[module, controller, action]
     * @throws \Exception 当后缀名不匹配的时候抛出404异常
     */
    public function resolve()
    {
        //解析URI
        $parse = parse_url(\Lying::$maker->request()->uri());
        //解析原生GET,这里是为了去除转发规则中$_GET本身中无用的参数
        parse_str(isset($parse['query']) ? $parse['query'] : '', $_GET);
        //去掉index.php
        $path = preg_replace('/^\/index\.php/i', '', $parse['path'], 1, $this->pathinfo);
        //匹配后缀名
        if (!empty($path) && !empty($this->suffix)) {
            $path = rtrim(preg_replace('/\\' . $this->suffix . '$/i', '', $path, 1, $validate), '/');
            if ($validate === 0) {
                throw new \Exception('Not Found (#404)', 404);
            }
        }
        //分割后对每个元素进行URL解码
        $pathArray = array_map(function ($val) {
            return urldecode($val);
        }, explode('/', trim($path, '/')));
        //路由匹配
        $pathNum = count($pathArray);
        foreach ($this->rule as $pattern => $rule) {
            //是否绝对匹配
            preg_replace('/\$$/', '', $pattern, 1, $absolute);
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
            $this->str2hump($this->router[1], true),
            $this->str2hump($this->router[2])
        ];
    }

    /**
     * 把横线分割的小写字母转换为驼峰
     * @param string $val 要转换的字符串
     * @param boolean $ucfirst 首字母是否大写
     * @return string 返回转换后的字符串
     */
    private function str2hump($val, $ucfirst = false)
    {
        $val = str_replace(' ', '', ucwords(str_replace('-', ' ', $val)));
        return $ucfirst ? $val : lcfirst($val);
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
     * 如果路径post,则生成[当前模块/当前控制器/post]
     * 如果路径post/index,则生成[当前模块/post/index]
     * 如果路径admin/post/index,则生成[admin/post/index]
     * ```
     * @param string $path 要生成的相对路径
     * @param array $params 要生成的参数,一个关联数组,如果有路由规则,参数中必须包含rule中的参数才能反解析
     * @param boolean $normal 是否把参数设置成?a=1&b=2
     * @return string 返回生成的URL
     */
    public function createUrl($path, $params = [], $normal = false)
    {
        $route = trim($path, '/');
        $route = empty($route) ? [] : explode('/', $route);
        switch (count($route)) {
            case 1:
                $route = [$this->router[0], $this->router[1], $route[0]];
                break;
            case 2:
                $route = [$this->router[0], $route[0], $route[1]];
                break;
            case 3:
                break;
            default:
                $route = $this->router;
        }
        $route = implode('/', $route);

        //路由反解析
        foreach ($this->rule as $r => $v) {
            if ($route === $v[0] && false !== preg_match_all('/:([^\/]+)/', $r, $matchs)) {
                foreach ($matchs[1] as $m) {
                    //寻找参数并且匹配参数正则,不匹配就继续寻找下一条规则
                    if (!isset($params[$m]) || isset($v[$m]) && !preg_match($v[$m], $params[$m])) {
                        continue 2;
                    }
                    $r = str_replace(":$m", urlencode($params[$m]), $r);
                }
                //反解析的参数都存在
                $params = array_diff_key($params, array_flip($matchs[1]));
                $route = $r;
                break;
            }
        }

        //过滤一些奇怪的值
        $p1 = $p2 = [];
        foreach ($params as $key => $val) {
            if (in_array(gettype($val), ['string', 'integer', 'double', 'boolean', 'array'])) {
                if ($normal || $val === '' || is_array($val)) {
                    $p1[$key] = $val;
                } else {
                    $p2[$key] = $val;
                }
            }
        }
        $p1 = http_build_query($p1, '', '&');
        $p2 = str_replace(['=', '&'], '/', http_build_query($p2, '', '&'));

        //URL拼接
        $schema = isset($_SERVER['HTTPS']) && (strcasecmp($_SERVER['HTTPS'], 'on') === 0) ? 'https://' : 'http://';
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
        $url = $schema . $host;
        $url .= ($this->pathinfo ? '/index.php/' : '/') . $route . '/';
        $url .= empty($p2) ? '' : "$p2/";
        $url .= empty($p1) ? '' : "?$p1";
        return $url;
    }
}
