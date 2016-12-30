<?php
namespace lying\service;

class Router extends Service
{
    /**
     * 当前路由
     * @var array
     */
    private $router;
    
    /**
     * 解析路由
     * @return array 返回[$m, $c, $a]
     */
    public function parse()
    {
        $uri = maker()->request()->requestUri();
        $parse = parse_url($uri);
        
        //解析原生GET;这里是为了去除转发规则中$_GET本身中无用的参数,可以注释掉
        parse_str(isset($parse['query']) ? $parse['query'] : '', $_GET);
        
        //查找域名配置
        $host = maker()->request()->host();
        $config = maker()->config()->get('router');
        $config = isset($config[$host]) ? $config[$host] : $config['default'];
        
        //解析路由
        return $this->resolve($parse['path'], $config);
    }
    
    /**
     * 分割PATH,路由匹配
     * @param string $path 请求的路径,不包括queryString
     * @param array $conf 配置参数
     * @throws \Exception
     * @return array
     */
    private function resolve($path, $conf)
    {
        //去掉index.php
        $path = trim(preg_replace('/^\/index\.php/i', '', $path, 1), '/');
        
        //检查后缀名
        if ($path !== '' && isset($conf['suffix'])) {
            $path = trim(preg_replace('/\\' . $conf['suffix'] . '$/i', '', $path, 1, $validate), '/');
            if ($validate === 0) {
                throw new \Exception('Page not found.', 404);
            }
        }
        
        //分割,过滤,重新索引
        $tmpArr = array_values(array_filter(explode('/', $path)));
        
        //对每个元素进行url解码,包括键值
        $tmpArr = array_map(function($val) {
            return urldecode($val);
        }, $tmpArr);
        
        //路由匹配
        foreach ($conf['rule'] as $pattern => $rule) {
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
                    //正则匹配
                    if (isset($rule[$key]) && preg_match($rule[$key], $tmpArr[$i]) === 0) {
                        $match = false;
                        break;
                    }
                    array_push($params, $key, $tmpArr[$i]);
                }elseif ($tmpArr[$i] !== $r) {
                    $match = false;
                    break;
                }
            }
            //匹配到就不进行下一条匹配
            if ($match) {
                $tmpArr = array_merge(explode('/', $route), $params);
                break;
            }
        }
        
        //设置module,ctrl,action
        $m = isset($conf['module']) && $conf['module'] ? $conf['module'] : (($m = array_shift($tmpArr)) ? $m : 'index');
        $c = ($c = array_shift($tmpArr)) ? $c : (isset($conf['ctrl']) && $conf['ctrl'] ? $conf['ctrl'] : 'index');
        $a = ($a = array_shift($tmpArr)) ? $a : (isset($conf['action']) && $conf['action'] ? $conf['action'] : 'index');
        
        //存下当前的路由,全部小写,没有转换成驼峰
        $this->router = [strtolower($m), strtolower($c), strtolower($a)];
        
        //解析多余的参数到GET
        $this->parseGet($tmpArr);
        
        //转换为驼峰,返回当前请求的m,c,a
        return [
            $this->convert($this->router[0]),
            $this->convert($this->router[1], true).'Ctrl',
            $this->convert($this->router[2])
        ];
    }
    
    /**
     * 把m,c,a的'-'转换为驼峰
     * @param string $val 要转换的字符串
     * @param boolean $ucfirst 首字母大写
     * @return string
     */
    private function convert($val, $ucfirst = false)
    {
        $val = str_replace(' ', '', ucwords(str_replace('-', ' ', $val)));
        return $ucfirst ? $val : lcfirst($val);
    }
    
    /**
     * 多余的path参数用来解析成get变量
     * @param array $params
     */
    private function parseGet($params)
    {
        while ($params) {
            $key = array_shift($params);
            $value = array_shift($params);
            $_GET[$key] = $value ? $value : '';
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
    
    
    public function url($path, $params = [])
    {
        $routeArr = explode($path, '/');
        $scheme = maker()->request()->scheme();
        $host = maker()->request()->host();
        
        $conf = maker()->config()->get('router');
        $suffix = isset($conf['suffix']) && $conf['suffix'] ? $conf['suffix'] : '';
        
        
        switch (count($routeArr)) {
            case 1:
                
        }
        
        
        var_dump($this->router());
        
    }
    
    /**
     * 生成url
     * @param string $path 形如"post"、"post/index"、"admin/post/index"或者完整网址,否则报错
     * @param array $params 要带的参数，使用path模式/id/1/name/carol.
     * 此参数只接受数组+字母组成的键/值,包含非数字、字母的参数会被忽略.
     * 注意：如果此参数的键值为纯数字，则键值将会被忽略，如createUrl('post', [1])将会变成/path/1而不是/path/0/1.
     * @param string $query 接受一个数组，此数组的参数会被编码成get参数的形式放在"?"之后.
     * 所有带有特殊字符(+、空格、|、/、?、%、#、&、<、>等)的键/值对，都应该放在此参数.
     * @throws \Exception
     * @return string
     */
    public function createUrl($path, $params = [], $query = [])
    {
        //如果是常规连接就带参数返回
        if (strncmp($path, 'http://', 7) === 0 || strncmp($path, 'https://', 8) === 0) {
            $query = $params ? http_build_query($params, '', '&', PHP_QUERY_RFC3986) : false;
            return $path .= ($query ? "?$query" : '');
        }
        
        $tmp = explode('/', $path);
        //查询当前对应的配置
        $host = $this->make()->getRequest()->host();
        $conf = $this->make()->getConfig()->get('router');
        $suffix = isset($conf['suffix']) && $conf['suffix'] ? $conf['suffix'] : '';
        $module = isset($conf['module']) && $conf['module'];
        
        switch (count($tmp)) {
            case 1:
                $url = $module ? ('/' . __CTRL__ . "/$tmp[0]") : ('/' . __MODULE__ . '/' . __CTRL__ . "/$tmp[0]");
                break;
            case 2:
                $url = $module ? ("/$tmp[0]/$tmp[1]") : ('/' . __MODULE__ . "/$tmp[0]/$tmp[1]");
                break;
            case 3:
                $url = "/$tmp[0]/$tmp[1]/$tmp[2]";
                break;
            default:
                throw new \Exception('The URL format is not correct', 500);
        }
        
        //设置path形式的参数
        foreach ($params as $key=>$param) {
            if (ctype_alnum($key) && ctype_alnum($param)) {
                $url .= is_numeric($key) ? "/$param" : "/$key/$param";
            }
        }
        //设置格式化的query参数
        $query = $query ? http_build_query($query, '', '&', PHP_QUERY_RFC3986) : false;
        
        $url .= $suffix . ($query ? "?$query" : '');
        return $url;
    }
}