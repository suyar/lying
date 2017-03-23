<?php
namespace lying\service;

/**
 * 路由组件
 *
 * @author carolkey <me@suyaqi.cn>
 * @since 2.0
 * @link https://carolkey.github.io/
 * @license MIT
 */
class Router
{
    /**
     * @var array 存当前路由[$m,$c,$a]
     */
    private $router;

    /**
     * @var array 当前路由的配置数组
     */
    private $config;
    
    /**
     * 解析路由
     * @return array 返回路由数组[$m, $c, $a]
     */
    public function parse()
    {
        //解析URL
        $parse = parse_url(\Lying::$maker->request()->requestUri());
        
        //解析原生GET；这里是为了去除转发规则中$_GET本身中无用的参数
        parse_str(isset($parse['query']) ? $parse['query'] : '', $_GET);
        
        //查找域名对应的路由配置
        $host = \Lying::$maker->request()->host();
        $config = \Lying::$maker->config()->read('router');
        $this->config = isset($config[$host]) ? $config[$host] : $config['default'];
        
        //解析路由
        return $this->resolve($parse['path']);
    }

    /**
     * 返回当前路由配置的某个配置项
     * @param string $name 配置键名
     * @param mixed $default 如果键名不存在，返回的值
     * @return mixed|null 返回的配置值
     */
    private function config($name, $default = false)
    {
        return isset($this->config[$name]) && !empty($this->config[$name]) ? $this->config[$name] : $default;
    }
    
    /**
     * 分割PATH，路由匹配
     * @param string $path 请求的路径，不包括查询字符串
     * @throws \Exception 路由不匹配抛出404错误
     * @return array 返回[模块, 控制器, 方法]
     */
    private function resolve($path)
    {
        //去掉index.php，不区分大小写
        $path = trim(preg_replace('/^\/index\.php/i', '', $path, 1), '/');

        //检查后缀名
        if ($path !== '' && ($suffix = $this->config('suffix'))) {
            $path = rtrim(preg_replace('/\\' . $suffix . '$/i', '', $path, 1, $validate), '/');
            if ($validate === 0) {
                throw new \Exception('Page not found.', 404);
            }
        }
        
        //对每个元素进行url解码，包括键名
        $tmpArr = array_map(function($val) {
            return urldecode($val);
        }, explode('/', $path));
        
        //模块
        $m = ($m = $this->config('module')) ? $m : (($m = array_shift($tmpArr)) ? $m : 'index');

        //路由匹配
        foreach ($this->config('rule', []) as $pattern => $rule) {
            $patternArr = explode('/', $pattern);
            //path个数不匹配，匹配下一个
            if (count($tmpArr) < count($patternArr)) continue;
            //参数映射
            $params = [];
            $match = true;
            foreach ($patternArr as $i => $r) {
                if (strncmp($r, ':', 1) === 0) {
                    $key = ltrim($r, ':');
                    if (isset($rule[$key]) && preg_match($rule[$key], $tmpArr[$i]) === 0) {
                        $match = false;
                        break;
                    } else {
                        array_push($params, $key, $tmpArr[$i]);
                    }
                } elseif ($tmpArr[$i] !== $r) {
                    $match = false;
                    break;
                }
            }
            //匹配到就不进行下一条匹配
            if ($match) {
                $tmpArr = array_merge(explode('/', $rule[0]), $params, array_splice($tmpArr, count($patternArr)));
                $m = array_shift($tmpArr);
                break;
            }
        }

        //设置控制器，方法
        $c = ($c = array_shift($tmpArr)) ? $c : $this->config('controller', 'index');
        $a = ($a = array_shift($tmpArr)) ? $a : $this->config('action', 'index');

        //存下当前的路由，全部小写，没有转换成驼峰
        $this->router = [strtolower($m), strtolower($c), strtolower($a)];
        
        //解析多余的参数到GET
        $this->parseGet($tmpArr);
        
        //转换为驼峰,返回当前请求的模块、控制器、方法
        return [
            $this->convert($this->router[0]),
            $this->convert($this->router[1], true),
            $this->convert($this->router[2])
        ];
    }
    
    /**
     * 把横线分割的小写字母转换为驼峰
     * @param string $val 要转换的字符串
     * @param boolean $ucfirst 首字母是否大写
     * @return string 返回转换后的字符串
     */
    private function convert($val, $ucfirst = false)
    {
        $val = str_replace(' ', '', ucwords(str_replace('-', ' ', $val)));
        return $ucfirst ? $val : lcfirst($val);
    }
    
    /**
     * 多余的path参数用来解析成get变量
     * @param array $params path中的参数数组
     */
    private function parseGet($params)
    {
        while ($params) {
            $key = array_shift($params);
            $value = array_shift($params);
            $_GET[$key] = $value === null ? '' : $value;
        }
    }
    
    /**
     * 返回此次请求的路由
     * @param boolean $string 是否以字符串的形式返回
     * @return array|string user-name/index/index
     */
    public function router($string = false)
    {
        return $string ? implode('/', $this->router) : $this->router;
    }
    
    /**
     * url生成
     * @param string $path 要生成的相对路径
     * 如果路径post，则生成当前模块，当前控制器下的Post方法
     * 如果路径post/index，则生成当前模块，控制器为Post下的index方法
     * 如果路径admin/post/index，则生成模块为admin，控制器为Post下的index方法
     * @param array $params 要生成的参数，一个关联数组，如果有路由规则，参数中必须包含rule中的参数才能反解析
     * @param boolean $normal 是否把参数设置成?a=1&b=2
     * @return string 返回生成的url
     */
    public function createUrl($path, $params = [], $normal = false)
    {
        $route = explode('/', trim($path, '/'));
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
        foreach ($this->config('rule', []) as $r => $v) {
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
                $match = true;
                break;
            }
        }

        //如果没有匹配到并且绑定了模块，去除模块名
        if (!isset($match)) {
            $route = preg_replace('/^' . $this->config('module') . '\//', '', $route);
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
        $url = \Lying::$maker->request()->scheme() . '://' . \Lying::$maker->request()->host();
        $url .= ($this->config('pathinfo') ? '/index.php/' : '/') . $route;
        $url .= empty($p2) ? '' : "/$p2";
        $url .= $this->config('suffix', '/');
        $url .= empty($p1) ? '' : "?$p1";
        return $url;
    }
}
