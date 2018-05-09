<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\service;

/**
 * Class Response
 * @package lying\service
 */
class Response extends Service
{
    const FORMAT_RAW = 'raw';

    const FORMAT_HTML = 'html';

    const FORMAT_JSON = 'json';

    const FORMAT_JSONP = 'jsonp';

    const FORMAT_XML = 'xml';

    /**
     * @var array HTTP状态码
     */
    private static $_httpCode = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        118 => 'Connection timed out',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        210 => 'Content Different',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        310 => 'Too many Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested range unsatisfiable',
        417 => 'Expectation failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable entity',
        423 => 'Locked',
        424 => 'Method failure',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        449 => 'Retry With',
        450 => 'Blocked by Windows Parental Controls',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway or Proxy Error',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        507 => 'Insufficient storage',
        508 => 'Loop Detected',
        509 => 'Bandwidth Limit Exceeded',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];

    /**
     * @var array 要发送的头
     */
    private $_headers = [];

    /**
     * @var int HTTP状态码
     */
    private $_statusCode = 200;

    /**
     * @var bool 是否已经发送过
     */
    private $_isSent = false;

    /**
     * @var mixed 要发送的数据
     */
    private $_data;

    /**
     * @var string 返回的类型
     */
    private $_format = 'html';

    /**
     * 设置要发送的头
     * @param string $name header名
     * @param string $value header值
     * @return $this
     */
    public function setHeader($name, $value)
    {
        $this->_headers[strtolower($name)] = $value;
        return $this;
    }

    /**
     * 发送已经设置的头信息,如果头已经发送了,那么这些头不会再发送
     * @return $this
     */
    private function sendHeaders()
    {
        if (!headers_sent($file,$line)) {
            foreach ($this->_headers as $name => $value) {
                $name = str_replace(' ', '-', ucwords(str_replace('-', ' ', $name)));
                header("$name: $value");
            }
            http_response_code($this->_statusCode);
        }
        return $this;
    }

    /**
     * 设置HTTP状态码
     * @param int $code
     * @return $this
     */
    public function setStatusCode($code)
    {
        $code = intval($code);
        if (isset(self::$_httpCode[$code])) {
            $this->_statusCode = $code;
        }
        return $this;
    }

    public function setFormat($format)
    {

    }

    /**
     * 设置要发送的数据
     * @param mixed $data 要发送的数据
     * @return $this
     */
    public function setContent($data)
    {
        $this->_data = $data;
        return $this;
    }

    /**
     * 预处理输出内容
     * @throws \Exception 发送的内容格式不正确时抛出异常
     */
    public function prepare()
    {
        if (is_array($this->_data)) {
            throw new \Exception('Response content must not be an array.');
        } elseif (is_object($this->_data)) {
            if (method_exists($this->_data, '__toString')) {
                $this->_data = $this->_data->__toString();
            } else {
                throw new \Exception('Response content must be a string or an object implementing __toString().');
            }
        }
    }

    /**
     * 发送内容
     */
    private function sendContent()
    {
        echo $this->_data;
    }

    /**
     * 发送响应
     * @throws \Exception 发送的内容格式不正确时抛出异常
     */
    public function send()
    {
        if ($this->_isSent == false) {
            $this->prepare();
            $this->sendHeaders();
            $this->sendContent();
            $this->_isSent = true;
        }
    }
}
