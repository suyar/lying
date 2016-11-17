<?php
namespace lying\service;

class Router extends Service
{
    /**
     * 解析路由
     * @return array
     */
    public function parse()
    {
        $request = $this->get('request');
        $uri = $request->uri();
        $parse = parse_url($uri);
        
        //解析普通get参数
        parse_str(isset($parse['query']) ? $parse['query'] : '', $_GET);
        
        //查找域名配置
        $host = $request->host();
        $conf = $this->get('config')->load('router');
        $conf = isset($conf[$host]) ? $conf[$host] : $conf['default'];
        
        //分割
        return $this->split(strtolower($parse['path']), $conf);
    }
    
    /**
     * 分割path参数
     * @param string $path
     * @param array $conf
     * @throws \Exception
     * @return array
     */
    public function split($path, $conf)
    {
        //判断后缀名
        if ($path !== '/' && isset($conf['suffix']) && $conf['suffix']) {
            if (preg_match('/\\'. $conf['suffix'] .'$/', $path)) {
                $path = preg_replace('/\\'. $conf['suffix'] .'$/', '', $path);
            }else {
                throw new \Exception('Unknown path ' . $path, 404);
            }
        }
        
        //匹配路由规则
        $match = [];
        foreach ($conf['rule'] as $key=>$value) {
            if (preg_match($key, $path, $match)) {
                $path = $value;
                unset($match[0]);
                break;
            }
        }
        
        //分割
        $t = array_filter(explode('/', $path));
        
        //替换参数
        $t = array_map(function($val) use (&$match) {
            return strpos($val, ':') === 0 ? array_shift($match) : $val;
        }, $t);
        
        //查找mudule
        $m = isset($conf['module']) && $conf['module'] ? $conf['module'] : array_shift($t);
        $m = is_null($m) ? $conf['default_module'] : $m;
        
        //确定controller和action
        switch (count($t)) {
            case 0:
                $c = $conf['default_ctrl'];
                $a = $conf['default_action'];
                break;
            case 1:
                $c = array_shift($t);
                $a = $conf['default_action'];
                break;
            default:
                $c = array_shift($t);
                $a = array_shift($t);
                $this->resolveGet($t);
        }
        define('__MODULE__', $m);
        define('__CTRL__', $c);
        define('__ACTION__', $a);
        return [$m, ucfirst($c).'Ctrl', $a];
    }
    
    /**
     * 多余的path参数用来解析成get变量
     * @param array $params
     */
    public function resolveGet($params)
    {
        while ($params) {
            $key = array_shift($params);
            $value = array_shift($params);
            $_GET[$key] = $value;
        }
    }
    
    /**
     * 生成url
     * @param string $path 形如"post"、"post/index"、"admin/post/index",否则报错
     * @param array $params 要带的参数，使用path模式/id/1/name/carol.
     * 此参数只接受数组+字母组成的键/值,包含非数字、字母的键/值会被忽略.
     * @param string $after 如果为true,则$params参数编码后放在"?"之后，如/index.html?id=1.
     * 如果此参数为false（默认）,则所有的参数作为path模式;如果此参数为数组,次参数作为常规模式编码后放在"?"之后.
     * 所有带有特殊字符(+、空格、|、&、<、>等)的参数，不管是键/值都应该放在此参数，或者把此参数设置为true.
     * @throws \Exception
     * @return string
     */
    public function createUrl($path, $params = [], $after = false)
    {
        $tmp = explode('/', trim($path, '/'));
        
        $host = $this->get('request')->host();
        $conf = $this->get('config')->load('router');
        $conf = isset($conf[$host]) ? $conf[$host] : $conf['default'];
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
        
        if ($after === true) {
            $after = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        }else {
            foreach ($params as $key=>$param) {
                if (ctype_alnum($key) && ctype_alnum($param)) {
                    $url .= is_numeric($key) ? "/$param" : "/$key/$param";
                }
            }
            $after = is_array($after) ? http_build_query($after, '', '&', PHP_QUERY_RFC3986) : '';
        }
        
        $url .= $suffix . ($after ? "?$after" : '');
        return $url;
    }
}