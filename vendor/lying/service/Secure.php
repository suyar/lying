<?php
namespace lying\service;

class Secure
{
    /**
     * AES加密
     * @param string $data
     * @param string $key
     * @return string
     */
    public function encrypt($data, $key)
    {
        $key = $iv = md5($key, true);
        $res = openssl_encrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($res);
    }
    
    /**
     * AES解密
     * @param string $data
     * @param string $key
     * @return string
     */
    public function decrypt($data, $key)
    {
        $key = $iv = md5($key, true);
        return openssl_decrypt(base64_decode($data), 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    }
}