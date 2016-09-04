<?php
namespace core;
/**
 * cURL组件
 * @author suyq
 */
class Http {
    
    private static $http;
    
    private function __construct() {}
    
    private function __clone() {}
    
    public static function getInstance() {
        if(!self::$http instanceof self) {
            self::$http = new self;
        }
        return self::$http;
    }
    
    /**
     * HTTP GET请求
     * @param string $url 要请求的url
     * @param array $options curl选项,用数组表示
     * @return string|boolean 失败返回false
     */
    public function httpGet($url, $options = []) {
        $ch = curl_init($url);
        if ($ch === false) return $ch;
        $opt = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_TIMEOUT => 3
        ];
        $opt = $options ? $options : $opt;
        $res = curl_setopt_array($ch, $opt);
        $res = $res ? curl_exec($ch) : $res;
        curl_close($ch);
        return $res;
    }
    
    /**
     * HTTPS GET请求,默认不校验CA证书
     * @param string $url 要请求的url
     * @param array $options curl选项,用数组表示
     * @return string|boolean 失败返回false
     */
    public function httpsGet($url, $options = []) {
        $ch = curl_init($url);
        if ($ch === false) return $ch;
        $opt = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_TIMEOUT => 3
        ];
        $opt = $options ? $options : $opt;
        $res = curl_setopt_array($ch, $opt);
        $res = $res ? curl_exec($ch) : $res;
        curl_close($ch);
        return $res;
    }
    
    /**
     * HTTP POST请求
     * @param string $url 要请求的url
     * @param array $data 要发送的数据,文件请用CURLFile来上传文件
     * @param array $options curl选项,用数组表示
     * @return string|boolean 失败返回false
     */
    public function httpPost($url, $data, $options = []) {
        $ch = curl_init($url);
        if ($ch === false) return $ch;
        $opt = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_TIMEOUT => 3
        ];
        $opt = $options ? $options : $opt;
        $res = curl_setopt_array($ch, $opt);
        $res = $res ? curl_exec($ch) : $res;
        curl_close($ch);
        return $res;
    }
    
    /**
     * HTTP POST请求,默认不校验CA证书
     * @param string $url 要请求的url
     * @param array $data 要发送的数据,文件请用CURLFile来上传文件
     * @param array $options curl选项,用数组表示
     * @return string|boolean 失败返回false
     */
    public function httpsPost($url, $data, $options = []) {
        $ch = curl_init($url);
        if ($ch === false) return $ch;
        $opt = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_TIMEOUT => 3
        ];
        $opt = $options ? $options : $opt;
        $res = curl_setopt_array($ch, $opt);
        $res = $res ? curl_exec($ch) : $res;
        curl_close($ch);
        return $res;
    }
}