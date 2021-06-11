<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\service;

use lying\upload\UploadFile;

/**
 * Class Request
 * @package lying\service
 */
class Request extends Service
{
    /**
     * @var array GET数组
     */
    private $_getParams;

    /**
     * @var array POST数组
     */
    private $_postParams;

    /**
     * @var array FILES数组
     */
    private $_fileParams;

    /**
     * @var array HTTP头
     */
    private $_headers;

    /**
     * @var array 当前请求的路由
     */
    private $_route;

    /**
     * @var string 原生请求数据
     */
    private $_rawPost;

    /**
     * @var string CSRF校验码
     */
    private $_csrfToken;

    /**
     * 获取当前请求的路由
     * @return array
     */
    public function resolve()
    {
        if ($this->_route === null) {
            $router = \Lying::$maker->router;
            $this->_route = [$router->module(), $router->controller(), $router->action()];
            $this->_getParams = $_GET;
            $this->_postParams = $_POST;
            $this->_fileParams = $_FILES;
        }
        return $this->_route;
    }

    /**
     * 返回GET参数,如果不设置$name,则返回整个GET数组
     * @param string|null $name 参数名,放空则返回整个GET数组
     * @param mixed $default 参数不存在的时候,返回的默认值
     * @return array|mixed|null 返回请求的GET参数
     */
    public function get($name = null, $default = null)
    {
        if ($name === null) {
            return $this->_getParams;
        } else {
            return isset($this->_getParams[$name]) ? $this->_getParams[$name] : $default;
        }
    }

    /**
     * 返回POST参数,如果不设置$name,则返回整个POST数组
     * @param string|null $name 参数名,放空则返回整个POST数组
     * @param mixed $default 参数不存在的时候,返回的默认值
     * @return array|mixed|null 返回请求的POST参数
     */
    public function post($name = null, $default = null)
    {
        if ($name === null) {
            return $this->_postParams;
        } else {
            return isset($this->_postParams[$name]) ? $this->_postParams[$name] : $default;
        }
    }

    /**
     * 获取上传的文件
     * @param string $name 文件字段名,如果是数组,就直接写数组名,不要写下标;如果不写这个字段,就是取所有上传的文件
     * @return UploadFile|UploadFile[]|false 如果只有一个文件,则返回File对象,多个文件返回File对象数组,没有文件返回false
     */
    public function file($name = null)
    {
        $files = [];
        if ($name === null) {
            foreach ($this->_fileParams as $key => $file) {
                if (is_array($file['name'])) {
                    foreach ($file['name'] as $i => $fname) {
                        $files[$key . '[' . $i . ']'] = new UploadFile([
                            'name' => $fname,
                            'type' => $file['type'][$i],
                            'size' => $file['size'][$i],
                            'tmp_name' => $file['tmp_name'][$i],
                            'error' => $file['error'][$i],
                        ]);
                    }
                } else {
                    $files[$key] = new UploadFile($file);
                }
            }
        } elseif (isset($this->_fileParams[$name])) {
            $file = $this->_fileParams[$name];
            if (is_array($file['name'])) {
                foreach ($file['name'] as $i => $fname) {
                    $files[$name . '[' . $i . ']'] = new UploadFile([
                        'name' => $fname,
                        'type' => $file['type'][$i],
                        'size' => $file['size'][$i],
                        'tmp_name' => $file['tmp_name'][$i],
                        'error' => $file['error'][$i],
                    ]);
                }
            } else {
                $files[$name] = new UploadFile($file);
            }
        }
        return $files ? (count($files) == 1 ? reset($files) : $files) : false;
    }

    /**
     * 返回请求方法,如:GET/POST/HEAD/PUT/PATCH/DELETE/OPTIONS/TRACE
     * @return string 返回大写的请求方式
     */
    public function method()
    {
        return isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';
    }

    /**
     * 是否为GET请求
     * @return bool
     */
    public function isGet()
    {
        return $this->method() === 'GET';
    }

    /**
     * 是否为POST请求
     * @return bool
     */
    public function isPost()
    {
        return $this->method() === 'POST';
    }

    /**
     * 是否为AJAX请求
     * @return bool
     */
    public function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    /**
     * 是否为PJAX请求
     * @return bool
     */
    public function isPjax()
    {
        return $this->isAjax() && isset($_SERVER['HTTP_X_PJAX']);
    }

    /**
     * 返回请求的原始数据php://input
     * @return bool|string
     */
    public function rawBody()
    {
        if ($this->_rawPost === null) {
            $this->_rawPost = file_get_contents('php://input');
        }
        return $this->_rawPost;
    }

    /**
     * 是否为HTTPS请求
     * @return bool
     */
    public function isHttps()
    {
        return isset($_SERVER['HTTPS']) && (strcasecmp($_SERVER['HTTPS'], 'on') === 0 || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') === 0;
    }

    /**
     * 判断是否在CLI模式下运行
     * @return bool
     */
    public function isCli()
    {
        return PHP_SAPI === 'cli';
    }

    /**
     * 获取CLI下的参数,不填写返回所有参数数组
     * @param int $offset 参数下标,0为脚本名称,1为参数1,以此类推,不存在返回$default
     * @param mixed $default 值不存在时的默认值
     * @return null|string|array
     */
    public function getArgv($offset = null, $default = null)
    {
        if (isset($_SERVER['argv'])) {
            if ($offset === null) {
                return $_SERVER['argv'];
            } else if (isset($_SERVER['argv'][$offset])) {
                return $_SERVER['argv'][$offset];
            }
        }
        return $default;
    }

    /**
     * 令行模式下传递给该脚本的参数的数目
     * @return int 不存在返回0
     */
    public function getArgc()
    {
        return isset($_SERVER['argc']) ? $_SERVER['argc'] : 0;
    }

    /**
     * 返回服务器端口
     * @return string
     */
    public function serverPort()
    {
        return isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : '';
    }

    /**
     * 返回服务器IP
     * @return string
     */
    public function serverIP()
    {
        return isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '';
    }

    /**
     * 返回HOST
     * @param bool $schema 是否显示协议头http(s)://,默认为false不显示
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
     * 返回请求的UA
     * @return string
     */
    public function userAgent()
    {
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    }

    /**
     * 返回客户端真实IP,失败返回空字符串
     * @return string
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
            return '';
        }
    }

    /**
     * 返回客户端端口
     * @return string
     */
    public function userPort()
    {
        return isset($_SERVER['REMOTE_PORT']) ? $_SERVER['REMOTE_PORT'] : '';
    }

    /**
     * 返回请求开始时间
     * @param bool $milli 是否毫秒级
     * @return int|null 获取失败返回null
     */
    public function time($milli = false)
    {
        if ($milli) {
            if (isset($_SERVER['REQUEST_TIME_FLOAT'])) {
                return intval($_SERVER['REQUEST_TIME_FLOAT'] * 1000);
            } else {
                return isset($_SERVER['REQUEST_TIME']) ? intval($_SERVER['REQUEST_TIME'] * 1000) : null;
            }
        } else {
            return isset($_SERVER['REQUEST_TIME']) ? intval($_SERVER['REQUEST_TIME']) : null;
        }
    }

    /**
     * 获取请求header
     * @param string $header 请求的header名,放空为获取所有header
     * @return string|array|null 未获取到返回null
     */
    public function getHeader($header = null)
    {
        if ($this->_headers === null) {
            $this->_headers = [];
            if (function_exists('getallheaders')) {
                foreach (getallheaders() as $name => $value) {
                    $this->_headers[strtolower($name)] = $value;
                }
            } elseif (function_exists('http_get_request_headers')) {
                foreach (http_get_request_headers() as $name => $value) {
                    $this->_headers[strtolower($name)] = $value;
                }
            } else {
                foreach ($_SERVER as $name => $value) {
                    if (strncmp($name, 'HTTP_', 5) === 0) {
                        $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                        $this->_headers[strtolower($name)] = $value;
                    }
                }
            }
        }

        if ($header === null) {
            return $this->_headers;
        } else {
            $header = strtolower($header);
            return isset($this->_headers[$header]) ? $this->_headers[$header] : null;
        }
    }

    /**
     * 获取csrfToken
     * @return string
     */
    public function getCsrfToken()
    {
        if ($this->_csrfToken === null) {
            $cookie = \Lying::$maker->cookie;
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_-.';
            if (($token = $cookie->get('_csrf')) === false) {
                $token = str_pad(uniqid('_csrf', true), 32, substr(str_shuffle($chars), 0, 4));
                $cookie->set('_csrf', $token);
            }
            $mask = substr(str_shuffle($chars), 0, 8);
            $this->_csrfToken = $mask . hash_hmac('sha256', $token, $mask);
        }
        return $this->_csrfToken;
    }

    /**
     * 校验csrfToken
     * @param string|null $csrfToken 手动传入csrfToken,放空的话则自动获取csrfToken
     * @return bool 校验成功返回true,失败返回false
     */
    public function validateCsrfToken($csrfToken = null)
    {
        if (in_array($this->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return true;
        }
        $csrfToken ||
        ($csrfToken = $this->post('_csrf')) ||
        ($csrfToken = isset($_SERVER['HTTP_X_CSRF_TOKEN']) ? $_SERVER['HTTP_X_CSRF_TOKEN'] : null);
        if ($csrfToken && ($cookieToken = \Lying::$maker->cookie->get('_csrf'))) {
            $mask = substr($csrfToken, 0, 8);
            $token = substr($csrfToken, 8);
            return hash_hmac('sha256', $cookieToken, $mask) === $token;
        }
        return false;
    }
}
