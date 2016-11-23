<?php
namespace lying\service;

class Request
{
    /**
     * 返回请求的url，不包含host和#后面的参数
     * @return null|string 形如:/index.html?r=11
     */
    public function uri()
    {
        return isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null;
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
     * @return null|string 形如：lying.com，不带端口和协议类型
     */
    public function host()
    {
        return isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null;
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
     * @return string|null
     */
    public function serverIp() {
        return isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : null;
    }
    
    /**
     * 获取客户端IP地址,失败返回null
     * @return string|null
     */
    public function remoteIp() {
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
    }
    
    /**
     * 返回浏览器UA
     * @return string|null
     */
    public function UA()
    {
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
    }
    
    /**
     * 返回请求的方法
     * @return string|null "GET", "HEAD", "POST", "PUT"
     */
    public function method()
    {
        return isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : null;
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