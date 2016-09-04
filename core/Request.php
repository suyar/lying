<?php
namespace core;
class Request {
    
    private static $request;
    
    private function __construct() {}
    
    private function __clone() {}
    
    public static function getInstance() {
        if (!self::$request instanceof self) {
            self::$request = new self;
        }
        return self::$request;
    }
    
    /**
     * 获取服务器的IP地址,失败返回false
     * @param boolean 是否带端口
     * @return string|boolean
     */
    public function serverIp($port = false) {
        $ip = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : false;
        if ($port && $ip) $ip .= ':'.$_SERVER['SERVER_PORT'];
    }
    
    /**
     * 获取客户端IP地址,失败返回false
     * @param string $port 是否带端口
     * @return string|boolean
     */
    public function remoteIp($port = false) {
        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false;
        if ($port && $ip) $ip .= ':'.$_SERVER['REMOTE_PORT'];
    }
    
    /**
     * 判断当前请求是否为GET
     * @return boolean
     */
    public function isGet() {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }
    
    /**
     * 判断是否为AJAX请求
     * @return boolean
     */
    public function isAjax() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }
    
    /**
     * 判断是否为PJAX请求
     * @return boolean
     */
    public function isPjax() {
        return $this->isAjax() && !empty($_SERVER['HTTP_X_PJAX']);
    }
    
    /**
     * 判断当前请求是否为POST
     * @return boolean
     */
    public function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    /**
     * 返回当前请求协议:http或者https
     * @return string
     */
    public function scheme() {
        return isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : ($_SERVER['SERVER_PORT'] == '443' ? 'https' : 'http');
    }
    
    /**
     * 根据IP获取详细地址
     * @param string $ip 不写默认为remoteIp
     * @return mixed
     */
    public function ipInfo($ip = false) {
        if ($ip === false) $ip = self::remoteIp();
        $url = "http://whois.pconline.com.cn/ipJson.jsp?callback=ip&ip=$ip&json=true";
        $json = Http::getInstance()->httpGet($url);
        return json_decode(iconv('GBK', 'utf-8', $json));
    }
    
}