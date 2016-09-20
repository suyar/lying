<?php
namespace core;
/**
 * 路由
 * @author suyq
 * @version 1.0
 */
class Router {
    /**
     * 返回一个数组,包含class和method
     * @throws \Exception
     * @return array
     */
    public static function parse() {
        $request_uri = $_SERVER['REQUEST_URI'];
        $uri_info = parse_url($request_uri);
        $path = $uri_info['path'];
        $query = isset($uri_info['query']) ? $uri_info['query'] : false;
        if ($path !== '/' && !preg_match('/[a-zA-Z0-9]+\.html$/', basename($path))) throw new \Exception('Page not found', 404);
        $path = trim(str_replace('.html', '', $path), '/');
        $path = $path === '' ? [] : explode('/', $path);
        switch (count($path)) {
            case 0:
                $m = $c = $a = 'index';
                break;
            case 1:
                $m = strtolower(array_shift($path));
                $c = $a = 'index';
                break;
            case 2:
                $m = strtolower(array_shift($path));
                $c = strtolower(array_shift($path));
                $a = 'index';
                break;
            default:
                $m = strtolower(array_shift($path));
                $c = strtolower(array_shift($path));
                $a = strtolower(array_shift($path));
        }
        $class = 'app\\' . $m . '\\controller\\' . ucfirst($c);
        define('__MODULE__', $m);
        define('__CONTROLLER__', $c);
        define('__ACTION__', $a);
        self::collectGet($path, $query);
        return [$class, $a];
    }
    
    /**
     * 把PATHINFO里的参数解析出来
     * @param array $path
     * @param string $query
     */
    public static function collectGet($path, $query) {
        $_GET = [];
        if ($query) parse_str($query, $_GET);
        for ($i = 0; isset($path[$i]); $i += 2) {
            if($path[$i] === '')  continue;
            $_GET[$path[$i]] = isset($path[$i + 1]) ? $path[$i + 1] : '';
        }
    }
}