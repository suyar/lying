<?php
namespace lying\service;

/**
 * 数据加密服务
 *
 * @author carolkey <me@suyaqi.cn>
 * @since 2.0
 * @link https://carolkey.github.io/
 * @license MIT
 */
class Secure
{
    /**
     * 异或加密
     * @param string $str 要加密的字符串
     * @param string $key 密钥
     * @return string 加密后的字符串
     */
    public function xorEncrypt($str, $key)
    {
        $key = strtoupper(sha1($key));
        $str .= hash_hmac('sha256', $str, $key, true);
        
        $strLen = strlen($str);
        $result = '';
        for ($i = 0; $i < $strLen; $i++) {
            $result .= chr(ord($str[$i]) ^ ord($key[$i % 40]));
        }
        return str_replace(['+', '/', '='], ["_$key[0]", "_$key[19]", "_$key[39]"], base64_encode($result));
    }
    
    /**
     * 异或解密；注意，这里可能返回空的字符串，请用false === $result判断返回值
     * @param string $str 要解密的字符串
     * @param string $key 密钥
     * @return string|boolean 成功返回解密后的字符串，失败返回false
     */
    public function xorDecrypt($str, $key)
    {
        $key = strtoupper(sha1($key));
        $str = base64_decode(str_replace(["_$key[0]", "_$key[19]", "_$key[39]"], ['+', '/', '='], $str));
        
        $strLen = strlen($str);
        $result = '';
        for ($i = 0; $i < $strLen; $i++) {
            $result .= chr(ord($str[$i]) ^ ord($key[$i % 40]));
        }
        $str = substr($result, 0, -32);
        return substr($result, -32) === hash_hmac('sha256', $str, $key, true) ? $str : false;
    }
}
