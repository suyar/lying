<?php
namespace lying\service;

class Secure
{
    /**
     * AES加密
     * @param string $data 要加密的数据
     * @param string $key 加密密钥
     * @param string $iv 初始向量
     * @param string $type 加密类型
     * @param string $opt 选项
     * @return string
     */
    public function AES_encrypt($data, $key, $iv, $type = 'AES-256-CBC', $opt = OPENSSL_RAW_DATA)
    {
        return openssl_encrypt($data, $type, $key, $opt, $iv);
    }
    
    /**
     * AES解密
     * @param string $data 要解密的数据
     * @param string $key 加密密钥
     * @param string $iv 初始向量
     * @param string $type 加密类型
     * @param string $opt 选项
     * @return string
     */
    public function AES_decrypt($data, $key, $iv, $type = 'AES-256-CBC', $opt = OPENSSL_RAW_DATA)
    {
        return openssl_decrypt($data, $type, $key, $opt, $iv);
    }
}