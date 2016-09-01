<?php
namespace core;
class Encrypt {
    
    private static $encrypt;
    
    private function __construct() {}
    
    private function __clone() {}
    
    public static function getInstance() {
        if (!self::$encrypt instanceof self) {
            self::$encrypt = new self;
        }
        return self::$encrypt;
    }
    
    /**
     * RC4加密
     * @param string $data 要加密的字符串
     * @param string $key 密码,不能大于256字节
     * @return boolean|string 失败返回false($data和$key不为字符串以及$key长度大于256字节的时候返回false)
     */
    public function RC4($data, $key) {
        if (!is_string($data) || !is_string($key) || strlen($key) > 256 || strlen($key) == 0) return false;
        $k = $s = [];
        $keyLen = strlen($key);
        $dataLen = strlen($data);
        for ($i = 0; $i < 256; $i++) {
            $s[$i] = $i;
            $k[$i] = ord($key[$i % $keyLen]);
        }
        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $s[$i] + $k[$i]) % 256;
            $tmp = $s[$i];
            $s[$i] = $s[$j];
            $s[$j] = $tmp;
        }
        $cipher = '';
        for ($n = $j = $i = 0; $i < $dataLen; $i++) {
            $n = ($n + 1) % 256;
            $j = ($j + $s[$n]) % 256;
            
            $tmp = $s[$n];
            $s[$n] = $s[$j];
            $s[$j] = $tmp;
            
            $k = $s[($s[$n] + $s[$j]) % 256];
            $cipher .= chr(ord($data[$i]) ^ $k);
        }
        return $cipher;
    }
    
}