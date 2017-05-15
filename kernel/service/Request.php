<?php
namespace lying\service;

/**
 * 请求组件
 *
 * @author carolkey <me@suyaqi.cn>
 * @since 2.0
 * @link https://github.com/carolkey/lying
 * @license MIT
 */
class Request
{
    /**
     * @var array 请求的GET/POST数据
     */
    private $requestData;

    /**
     * 装载GET/POST数据,此函数第一次执行有效
     * @param array $get
     */
    public function load($get)
    {
        if ($this->requestData === null) {
            $this->requestData = [
                'get' => $get,
                'post' => $_POST
            ];
        }
    }

    /**
     * 返回GET参数,如果不设置$name,则返回整个GET数组
     * @param string|null $name 参数名
     * @param mixed $defaultValue 参数不存在的时候,返回的默认值
     * @return array|mixed
     */
    public function get($name = null, $defaultValue = null)
    {
        if ($name === null) {
            return $this->requestData['get'];
        } else {
            return isset($this->requestData['get'][$name]) ? $this->requestData['get'][$name] : $defaultValue;
        }
    }

    /**
     * 返回POST参数,如果不设置$name,则返回整个POST数组
     * @param string|null $name 参数名
     * @param mixed $defaultValue 参数不存在的时候,返回的默认值
     * @return array|mixed
     */
    public function post($name = null, $defaultValue = null)
    {
        if ($name === null) {
            return $this->requestData['post'];
        } else {
            return isset($this->requestData['post'][$name]) ? $this->requestData['post'][$name] : $defaultValue;
        }
    }

    /**
     * 返回请求方法:GET/POST/HEAD/PUT/PATCH/DELETE/OPTIONS/TRACE
     * @return string
     */
    public function method()
    {
        return isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';
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
     * 是否为POST请求
     * @return boolean
     */
    public function isPost()
    {
        return $this->method() === 'POST';
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
        return $this->isAjax() && isset($_SERVER['HTTP_X_PJAX']);
    }

    /**
     * 返回请求的原始数据
     * @return boolean|string
     */
    public function rawBody()
    {
        return file_get_contents('php://input');
    }

    /**
     * 是否为HTTPS
     * @return boolean
     */
    public function isHttps()
    {
        return isset($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'], 'on') === 0;
    }

    /**
     * 返回服务器端口
     * @return integer
     */
    public function serverPort()
    {
        return isset($_SERVER['SERVER_PORT']) ? (integer)$_SERVER['SERVER_PORT'] : 80;
    }

    /**
     * 返回服务器IP
     * @return string|null
     */
    public function serverIP()
    {
        return isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : null;
    }

    /**
     * 返回HOST
     * @param boolean $schema 是否显示协议头http(s)://
     * @return string
     */
    public function host($schema = false)
    {
        $secure = $this->isHttps();
        $host = $schema ? ($secure ? 'https://' : 'http://') : '';
        if (isset($_SERVER['HTTP_HOST'])) {
            $host .= $_SERVER['HTTP_HOST'];
        } elseif (isset($_SERVER['SERVER_NAME'])) {
            $host .= $_SERVER['SERVER_NAME'];
            $port = $this->serverPort();
            if ((!$secure && $port !== 80) || ($secure && $port !== 443)) {
                $host .= $port;
            }
        }
        return $host;
    }

    /**
     * 返回查询字符串
     * @return string
     */
    public function queryString()
    {
        return isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
    }

    /**
     * 返回REQUEST_URI:/index.php?a=1形式的字符串
     * @return string
     */
    public function uri()
    {
        return isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    }

    /**
     * 返回UA
     * @return string|null
     */
    public function userAgent()
    {
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
    }

    /**
     * 返回客户端IP
     * @return string|null
     */
    public function userIP()
    {
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
    }

    /**
     * 返回客户端端口
     * @return string|null
     */
    public function userPort()
    {
        return isset($_SERVER['REMOTE_PORT']) ? $_SERVER['REMOTE_PORT'] : null;
    }

    /**
     * 返回请求开始时间
     * @param boolean $millisecond 是否毫秒级
     * @return string|null
     */
    public function time($millisecond = false)
    {
        if ($millisecond) {
            if (isset($_SERVER['REQUEST_TIME_FLOAT'])) {
                return $_SERVER['REQUEST_TIME_FLOAT'] * 1000;
            } else {
                return isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] * 1000 : null;
            }
        } else {
            return isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : null;
        }
    }
}
