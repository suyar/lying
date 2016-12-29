<?php
namespace lying\service;

class Router extends Service
{
    /**
     * 解析路由
     * @return array 返回[$m, $c, $a]
     */
    public function parse()
    {
        $uri = maker()->request()->requestUri();
        $parse = parse_url($uri);
        //解析原生GET
        parse_str(isset($parse['query']) ? $parse['query'] : '', $_GET);
        //查找域名配置
        $host = maker()->request()->host();
        $config = maker()->config()->get('router');
        $config = isset($config[$host]) ? $config[$host] : $config['default'];
        //分割
        return $this->split($parse['path'], $config);
    }
    
    /**
     * 分割path参数
     * @param string $path
     * @param array $conf
     * @throws \Exception
     * @return array
     */
    private function split($path, $conf)
    {
        //去掉index.php
        $path = trim(preg_replace('/^\/index\.php/i', '', $path, 1), '/');
        //检查后缀名
        if ($path !== '' && isset($conf['suffix'])) {
            $validate = 0;
            $path = trim(preg_replace('/' . $conf['suffix'] . '$/i', '', $path, 1, $validate), '/');
            if ($validate === 0) {
                throw new \Exception('Page not found.', 404);
            }
        }
        //分割
        $tmpArr = array_filter(explode('/', $path));
        //设置module,ctrl,action
        $m = isset($conf['module']) && $conf['module'] ? $conf['module'] : (($m = array_shift($tmpArr)) ? $m : 'index');
        $c = ($c = array_shift($tmpArr)) ? $c : (isset($conf['ctrl']) && $conf['ctrl'] ? $conf['ctrl'] : 'index');
        $a = ($a = array_shift($tmpArr)) ? $a : (isset($conf['action']) && $conf['action'] ? $conf['action'] : 'index');
        //转换为驼峰
        $m = $this->convert($m);
        $c = $this->convert($c);
        $a = $this->convert($a);
        //解析多余的参数到GET
        $this->parseGet($tmpArr);
        //返回当前请求的m,c,a
        return [$m, $c.'Ctrl', $a];
    }
    
    /**
     * 把m,c,a的'-'转换为驼峰
     * @param string $val 要转换的字符串
     * @param boolean $ucfirst 首字母大写
     * @return string
     */
    private function convert($val, $ucfirst = false)
    {
        $val = str_replace(' ', '', ucwords(str_replace('-', ' ', strtolower($val))));
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