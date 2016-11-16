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
        if ($path !== '/' && $conf['suffix']) {
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
                array_shift($match);
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
        
        //确定controller和action
        $length = count($t);
        switch ($length) {
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
}