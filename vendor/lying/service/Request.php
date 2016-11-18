<?php
namespace lying\service;

class Request
{
    /**
     * 返回请求的url，不包含host和#后面的参数
     * @return string 形如：/index.html?r=11
     */
    public function uri()
    {
        return $_SERVER['REQUEST_URI'];
    }
    
    /**
     * 返回当前请求的绝对路径(不包括#后面的内容)
     * @return string
     */
    public function absoluteUrl()
    {
        return $this->scheme() . '://' . $this->host() . $this->uri();
    }
    
    /**
     * 返回host
     * @return string 形如：lying.com，不带端口和协议类型
     */
    public function host()
    {
        return $_SERVER['HTTP_HOST'];
    }
    
    /**
     * 返回当前请求的协议类型，
     * @return string 返回'http'或者'https'
     */
    public function scheme()
    {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    }
    
    /**
     * 获取服务器的IP地址
     * @return string|boolean
     */
    public function serverIp() {
        return isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : false;
    }
    
    /**
     * 获取客户端IP地址,失败返回false
     * @return string
     */
    public function remoteIp() {
        return $_SERVER['REMOTE_ADDR'];
    }
    
    /**
     * 返回浏览器UA
     * @return string|boolean
     */
    public function UA()
    {
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : false;
    }
    
    /**
     * 返回请求的方法
     * @return string "GET", "HEAD", "POST", "PUT"
     */
    public function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }
    
    /**
     * 是否为POST请求
     * @return boolean
     */
    public function isPost()
    {
        return $this->method() === 'POST';
    }
    
    /**
     * 是否为GET请求
     * @return boolean
     */
    public function isGet()
    {
        return $this->method() === 'GET';
    }
    
    /**
     * 是否为HEAD请求
     * @return boolean
     */
    public function isHead()
    {
        return $this->method() === 'HEAD';
    }
    
    /**
     * 是否为PUT请求
     * @return boolean
     */
    public function isPut()
    {
        return $this->method() === 'PUT';
    }
    
    /**
     * 是否为Ajax请求
     * @return boolean
     */
    public function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }
    
    /**
     * 是否为Pjax请求
     * @return boolean
     */
    public function isPjax()
    {
        return $this->isAjax() && !empty($_SERVER['HTTP_X_PJAX']);
    }
    
    /**
     * 返回POST原生数据
     * @return string
     */
    public function rawData()
    {
        return file_get_contents('php://input');
    }
}