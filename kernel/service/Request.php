<?php
namespace lying\service;

/**
 * 请求组件
 *
 * @author carolkey <me@suyaqi.cn>
 * @since 2.0
 * @link https://carolkey.github.io/
 * @license MIT
 */
class Request
{
    /**
     * 服务器使用的CGI规范的版本；例如："CGI/1.1"
     * @return string|null
     */
    public function cgiVersion()
    {
        return isset($_SERVER['GATEWAY_INTERFACE']) ? $_SERVER['GATEWAY_INTERFACE'] : null;
    }
    
    /**
     * 当前运行脚本所在的服务器的IP地址
     * @return string|null
     */
    public function serverAddr()
    {
        return isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : null;
    }
    
    /**
     * 当前运行脚本所在的服务器的主机名
     * 如果脚本运行于虚拟主机中，该名称是由那个虚拟主机所设置的值决定
     * @return string|null
     */
    public function serverName()
    {
        return isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : null;
    }
    
    /**
     * 服务器标识字符串，在响应请求时的头信息中给出
     * @return string|null
     */
    public function serverSoftware()
    {
        return isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : null;
    }
    
    /**
     * 请求页面时通信协议的名称和版本；例如："HTTP/1.0"
     * @return string|null
     */
    public function protocol()
    {
        return isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : null;
    }
    
    /**
     * 访问页面使用的请求方法；例如，GET，POST，HEAD，PUT，PATCH，DELETE
     * 如果请求方法为HEAD，PHP脚本将在发送Header头信息之后终止(这意味着在产生任何输出后，不再有输出缓冲)
     * @return string|null
     */
    public function method()
    {
        return isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : null;
    }
    
    /**
     * 请求开始时的时间戳
     * @param boolean $msec 是否返回13位时间戳
     * @return string|null
     */
    public function time($msec = false)
    {
        if ($msec) {
            return isset($_SERVER['REQUEST_TIME_FLOAT']) ? $_SERVER['REQUEST_TIME_FLOAT'] * 1000 : null;
        } else {
            return isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : null;
        }
    }
    
    /**
     * 查询字符串
     * @return string|null
     */
    public function queryString()
    {
        return isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : null;
    }
    
    /**
     * 当前运行脚本所在的文档根目录，在服务器配置文件中定义
     * @return string|null
     */
    public function docRoot()
    {
        return isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : null;
    }
    
    /**
     * 当前请求头中Host:项的内容，如果存在的话
     * @return string|null
     */
    public function httpHost()
    {
        return isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null;
    }
    
    /**
     * 当前请求头中User-Agent:项的内容，如果存在的话
     * 该字符串表明了访问该页面的用户代理的信息
     * @return string|null
     */
    public function UA()
    {
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
    }
    
    /**
     * 返回当前请求的协议"https"或"http"
     * @return string
     */
    public function scheme()
    {
        return isset($_SERVER['HTTPS']) && (strcasecmp($_SERVER['HTTPS'], 'on') === 0) ? 'https' : 'http';
    }
    
    /**
     * 浏览当前页面的用户的IP地址
     * @return string|null
     */
    public function remoteAddr()
    {
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
    }
    
    /**
     * 用户机器上连接到Web服务器所使用的端口号
     * @return string|null
     */
    public function remotePort()
    {
        return isset($_SERVER['REMOTE_PORT']) ? $_SERVER['REMOTE_PORT'] : null;
    }
    
    /**
     * 当前执行脚本的绝对路径
     * 如果在CLI模式下使用相对路径执行脚本，那么将包含用户指定的相对路径
     * @return string|null
     */
    public function scriptFile()
    {
        return isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : null;
    }
    
    /**
     * Web服务器使用的端口
     * 如果使用SSL安全连接，则这个值为用户设置的HTTP端口
     * @return string|null
     */
    public function serverPort()
    {
        return isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : null;
    }
    
    /**
     * 包含当前脚本的路径
     * @return string|null
     */
    public function scriptName()
    {
        return isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : null;
    }
    
    /**
     * URI用来指定要访问的页面；例如："/index.html"
     * @return string|null
     */
    public function requestUri()
    {
        return isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null;
    }
    
    /**
     * 包含由客户端提供的，跟在真实脚本名称之后并且在查询语句之前的路径信息，如果存在的话
     * @return string|null
     */
    public function pathInfo()
    {
        return isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : null;
    }
    
    /**
     * 返回不为空的主机名；例如：abc.com，127.0.0.1
     * @return string
     */
    public function host()
    {
        $host = $this->httpHost();
        return $host ? $host : $this->serverName();
    }

    /**
     * 返回当前请求的完整URL
     * @return string
     */
    public function currentUrl()
    {
        return $this->scheme() . '://' . $this->host() . $this->requestUri();
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
     * 是否为OPTIONS请求
     * @return boolean
     */
    public function isOptions()
    {
        return $this->method() === 'OPTIONS';
    }
    
    /**
     * 是否为DELETE请求
     * @return boolean
     */
    public function isDelete()
    {
        return $this->method() === 'DELETE';
    }
    
    /**
     * 是否为PATCH请求
     * @return boolean
     */
    public function isPatch()
    {
        return $this->method() === 'PATCH';
    }
    
    /**
     * 是否为AJAX请求
     * @return boolean
     */
    public function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }
    
    /**
     * 是否为PJAX请求
     * @return boolean
     */
    public function isPjax()
    {
        return $this->isAjax() && !empty($_SERVER['HTTP_X_PJAX']);
    }
    
    /**
     * 返回原生请求数据
     * @return string
     */
    public function rawBody()
    {
        return file_get_contents('php://input');
    }
}
