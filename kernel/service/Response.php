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
    /**
     * @var string HTTP版本
     */
    private $_version;

    /**
     * @var array 要发送的头
     */
    private $_headers = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (isset($_SERVER['SERVER_PROTOCOL']) && $_SERVER['SERVER_PROTOCOL'] === 'HTTP/1.0') {
            $this->_version = '1.0';
        } else {
            $this->_version = '1.1';
        }
    }

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
        }
        return $this;
    }


}
