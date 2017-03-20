<?php
namespace lying\service;

class Router
{
    /**
     * @var array 当前路由
     */
    private $router;
    
    /**
     * 解析路由
     * @return array 返回[$m, $c, $a]
     */
    public function parse()
    {
        $uri = maker()->request()->requestUri();
        
        //解析URL
        $parse = parse_url($uri);
        
        //解析原生GET;这里是为了去除转发规则中$_GET本身中无用的参数,本句代码可选
        parse_str(isset($parse['query']) ? $parse['query'] : '', $_GET);
        
        //查找路由域名配置
        $host = maker()->request()->host();
        $config = maker()->config()->get('router');
        $config = isset($config[$host]) ? $config[$host] : $config['default'];
        
        //重新设置已经载入的router配置(因为已经确定配置)
        maker()->config()->set('router', $config);
        
        //解析路由
        return $this->resolve($parse['path'], $config);
    }
    
    /**
     * 分割PATH,路由匹配
     * @param string $path 请求的路径,不包括queryString
     * @param array $conf 配置参数
     * @throws \Exception
     * @return array 返回[$m, $c, $a]
     */
    private function resolve($path, $conf)
    {
        //去掉index.php,不区分大小写
        $path = trim(preg_replace('/^\/index\.php/i', '', $path, 1), '/');
        
        //检查后缀名
        if ($path !== '' && isset($conf['suffix'])) {
            $path = rtrim(preg_replace('/\\' . $conf['suffix'] . '$/i', '', $path, 1, $validate), '/');
            if ($validate === 0) {
                throw new \Exception('Page not found.', 404);
            }
        }
        
        //分割路径
        $tmpArr = explode('/', $path);
        
        //对每个元素进行url解码,包括键值
        $tmpArr = array_map(function($val) {
            return urldecode($val);
        }, $tmpArr);
        
        //设置module
        $m = isset($conf['module']) ? $conf['module'] : (($m = array_shift($tmpArr)) ? $m : 'index');
        
        //路由匹配
        foreach (isset($conf['rule']) ? $conf['rule'] : [] as $pattern => $rule) {
            $patternArr = explode('/', $pattern);
            //path个数不匹配,匹配下一个
            if (count($tmpArr) < count($patternArr)) {
                continue;
            }
            //映射参数
            $route = array_shift($rule);
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
                $tmpArr = array_merge(explode('/', $route), $params, array_splice($tmpArr, count($patternArr)));
                $m = array_shift($tmpArr);
                break;
            }
        }
        
        //设置ctrl,action
        $c = ($c = array_shift($tmpArr)) ? $c : (isset($conf['ctrl']) ? $conf['ctrl'] : 'index');
        $a = ($a = array_shift($tmpArr)) ? $a : (isset($conf['action']) ? $conf['action'] : 'index');
        
        //存下当前的路由,全部小写,没有转换成驼峰
        $this->router = [strtolower($m), strtolower($c), strtolower($a)];
        
        //解析多余的参数到GET
        $this->parseGet($tmpArr);
        
        //转换为驼峰,返回当前请求的m,c,a
        return [
            $this->convert($this->router[0]),
            $this->convert($this->router[1], true),
            $this->convert($this->router[2])
        ];
    }
    
    /**
     * 把m,c,a的'-'转换为驼峰
     * @param string $val 要转换的字符串
     * @param boolean $ucfirst 首字母大写
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
     * url生成,支持反解析
     * @param string $path 要生成的相对路径
     * 如果路径post,则生成当前module,当前ctrl下的post方法;
     * 如果路径post/index,则生成当前module,ctrl为Post下的index方法;
     * 如果路径admin/post/index,则生成当前module为admin,ctrl为Post下的index方法;
     * @param array $params 要生成的参数,一个关联数组,如果有路由规则,参数中必须包含rule中的参数才能反解析
     * @return string
     */
    public function createUrl($path, $params = [])
    {
        $route = explode('/', $path);
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
        //匹配路由,反解析
        $conf = maker()->config()->get('router');
        foreach ($conf['rule'] as $r => $v) {
            if ($route === $v[0]) {
                preg_match_all('/:([^\/]+)/', $r, $matchs);
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
        
        //拼接参数        
        $query = str_replace(['=', '&'], '/', http_build_query($params, '', '&', PHP_QUERY_RFC3986));
        //协议类型
        $scheme = maker()->request()->scheme();
        //主机名
        $host = maker()->request()->host();
        //是否启用pathinfo
        $pathinfo = isset($conf['pathinfo']) && $conf['pathinfo'];
        //后缀
        $suffix = isset($conf['suffix']) ? $conf['suffix'] : '';
        //没有匹配到并且设置了默认module,就去掉route的module
        if (!isset($match) && isset($conf['module'])) {
            $route = preg_replace('/^'.$conf['module'].'\//', '', $route);
        }
        return $scheme . '://' . $host . ($pathinfo ? '/index.php/' : '/') . $route . '/' . $query . $suffix;
    }
}
