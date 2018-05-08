<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\service;

/**
 * Class Encrypter
 * @package lying\service
 */
class Encrypter extends Service
{
    /**
     * 位或加密
     * @param mixed $data 要加密的数据
     * @param string $key 密钥
     * @param int $expire 过期时间戳
     * @return string 返回密文
     */
    public function xorEncrypt($data, $key, $expire = 0)
    {
        $key = strtoupper(sha1($key));
        $keyLen = strlen($key);
        $data = serialize($data);

        $data .= hash_hmac('sha256', $data, $key, true) . sprintf('%010d', $expire);
        $result = '';
        for ($i = 0, $strLen = strlen($data); $i < $strLen; $i++) {
            $result .= chr(ord($data[$i]) ^ ord($key[$i % $keyLen]));
        }
        return base64_encode($result);
    }

    /**
     * 位或解密
     * @param string $data 密文字符串
     * @param string $key 密钥
     * @return bool|mixed 返回解密后的数据,失败返回false
     */
    public function xorDecrypt($data, $key)
    {
        $key = strtoupper(sha1($key));
        $keyLen = strlen($key);

        $data = base64_decode($data);
        $result = '';
        for ($i = 0, $strLen = strlen($data); $i < $strLen; $i++) {
            $result .= chr(ord($data[$i]) ^ ord($key[$i % $keyLen]));
        }
        $expire = substr($result, -10);
        if ($expire === '0000000000' || $expire >= time()) {
            $result = substr($result, 0, -10);
            $content = substr($result, 0, -32);
            $hash = substr($result, -32);
            if (strcmp(hash_hmac('sha256', $content, $key, true), $hash) === 0) {
                return @unserialize($content);
            }
        }
        return false;
    }
}
