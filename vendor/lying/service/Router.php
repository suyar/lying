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
        $request = maker()->request();
        $uri = $request->uri();
        $parse = parse_url($uri);
        
        //解析普通get参数
        parse_str(isset($parse['query']) ? $parse['query'] : '', $_GET);
        
        //查找域名配置
        $host = $request->host();
        $config = maker()->config();
        $conf = $config->get('router');
        $conf = isset($conf[$host]) ? $conf[$host] : $conf['default'];
        //设置路由配置为当前配置
        $config->set('router', $conf);
        
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
        $path = '/' . trim($path, '/');
        
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