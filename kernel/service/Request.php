<?php
/**
 * @author carolkey <me@suyaqi.cn>
 * @link https://github.com/carolkey/lying
 * @copyright 2017 Lying Framework
 * @license MIT
 */

namespace lying\service;

/**
 * Class Request
 * @package lying\service
 * @since 2.0
 */
class Request
{
    /**
     * @var array 请求的GET/POST数据
     */
    private $data;

    /**
     * @var string CSRF
     */
    private $csrfToken;

    /**
     * 装载GET/POST数据,此函数第一次执行有效
     * @param array $get
     */
    public function load($get)
    {
        if ($this->data === null) {
            $this->data = ['get' => $get, 'post' => $_POST];
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
            return $this->data['get'];
        } else {
            return isset($this->data['get'][$name]) ? $this->data['get'][$name] : $defaultValue;
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
            return $this->data['post'];
        } else {
            return isset($this->data['post'][$name]) ? $this->data['post'][$name] : $defaultValue;
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
        return isset($_SERVER['HTTPS']) && (strcasecmp($_SERVER['HTTPS'], 'on') === 0 || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') === 0;
    }

    /**
     * 判断是否在CLI模式下运行
     * @return boolean
     */
    public function isCli()
    {
        return php_sapi_name() === 'cli';
    }

    /**
     * 获取CLI下的参数
     * @param integer $offect 参数下标,0为脚本名称,1为参数1,不存在返回null
     * @param mixed $defaultValue 值不存在时的默认值
     * @return null|string
     */
    public function getArgv($offect = null, $defaultValue = null)
    {
        if (isset($_SERVER['argv'])) {
            if ($offect === null) {
                return $_SERVER['argv'];
            } else if (isset($_SERVER['argv'][$offect])) {
                return $_SERVER['argv'][$offect];
            }
        }
        return $defaultValue;
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
                $host .= ':' . $port;
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
     * 返回客户端真实IP
     * @return string|null
     */
    public function userIP()
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',')) {
                $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                return array_shift($ips);
            } else {
                return $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
        } elseif (isset($_SERVER['HTTP_X_REAL_IP'])) {
            return $_SERVER['HTTP_X_REAL_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        } else {
            return null;
        }
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

    /**
     * 获取csrfToken
     * @return string
     */
    public function getCsrfToken()
    {
        if ($this->csrfToken === null) {
            $cookie = \Lying::$maker->cookie();
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_-.';
            if (($token = $cookie->get('_csrf')) === false) {
                $token = str_pad(uniqid('_csrf', true), 32, substr(str_shuffle($chars), 0, 4));
                $cookie->set('_csrf', $token);
            }
            $mask = substr(str_shuffle($chars), 0, 8);
            $this->csrfToken = $mask . hash_hmac('sha256', $token, $mask);
        }
        return $this->csrfToken;
    }

    /**
     * 校验csrfToken
     * @param string|null $csrfToken 手动传入csrfToken,放空的话则自动获取csrfToken
     * @return boolean 校验成功返回true,失败返回false
     */
    public function validateCsrfToken($csrfToken = null)
    {
        if (in_array($this->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return true;
        }
        $csrfToken ||
        ($csrfToken = $this->post('_csrf')) ||
        ($csrfToken = isset($_SERVER['HTTP_X_CSRF_TOKEN']) ? $_SERVER['HTTP_X_CSRF_TOKEN'] : null);
        if ($csrfToken && ($cookieToken = \Lying::$maker->cookie()->get('_csrf'))) {
            $mask = substr($csrfToken, 0, 8);
            $token = substr($csrfToken, 8);
            return hash_hmac('sha256', $cookieToken, $mask) === $token;
        }
        return false;
    }
}
