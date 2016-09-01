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
        $path = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : (isset($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : '/');
        if ($path !== '/' && !preg_match('/[a-zA-Z0-9]+\.html$/', basename($path))) throw new \Exception("Page not found", 404);
        $path = trim(str_replace('.html', '', $path), '/');
        $path = $path === '' ? [] : explode('/', $path);
        switch (count($path)) {
            case 0:
                $m = $c = $a = 'index';
                break;
            case 1:
                $m = array_shift($path);
                $c = $a = 'index';
                break;
            case 2:
                $m = array_shift($path);
                $c = array_shift($path);
                $a = 'index';
                break;
            default:
                $m = array_shift($path);
                $c = array_shift($path);
                $a = array_shift($path);
        }
        $class = 'app\\' . strtolower($m) . '\\controller\\' . ucfirst(strtolower($c));
        self::collectReq($path);
        return [$class, $a];
    }
    
    /**
     * 把PATHINFO里的参数解析出来
     * @param unknown $path
     */
    public static function collectReq($path) {
        isset($_GET) && $_GET ? array_shift($_GET) : '';
        for ($i = 0; isset($path[$i]); $i += 2) {
            if($path[$i] === '')  continue;
            $_GET[$path[$i]] = isset($path[$i + 1]) ? $path[$i + 1] : '';
        }
    }
    
    
}