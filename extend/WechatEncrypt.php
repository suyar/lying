<?php
namespace extend;
/**
 * @author suyq
 * 1.第三方回复加密消息给公众平台;
 * 2.第三方收到公众平台发送的消息,验证消息的安全性,并对消息进行解密
 */
class WechatEncrypt {
    public static $OK = 0;
    //签名验证错误
    public static $ValidateSignatureError = -40001;
    //xml解析失败
    public static $ParseXmlError = -40002;
    //sha加密生成签名失败
    public static $ComputeSignatureError = -40003;
    //encodingAesKey非法
    public static $IllegalAesKey = -40004;
    //appid校验错误
    public static $ValidateAppidError = -40005;
    //aes加密失败
    public static $EncryptAESError = -40006;
    //aes解密失败
    public static $DecryptAESError = -40007;
    //解密后得到的buffer非法
    public static $IllegalBuffer = -40008;
    //base64加密失败
    public static $EncodeBase64Error = -40009;
    //base64解密失败
    public static $DecodeBase64Error = -40010;
    //生成xml失败
    public static $GenReturnXmlError = -40011;
    
    //公众平台上,开发者设置的token
    private $token;
    //公众平台上,开发者设置的EncodingAESKey
    private $encodingAesKey;
    //公众平台的appId
    private $appId;

    /**
     * 构造函数
     * @param string $token 公众平台上,开发者设置的token
     * @param string $encodingAesKey 公众平台上,开发者设置的EncodingAESKey
     * @param string $appId 公众平台的appId
     */
	public function __construct($token, $encodingAesKey, $appId) {
		$this->token = $token;
		$this->encodingAesKey = $encodingAesKey;
		$this->appId = $appId;
	}

	/**
	 * 将公众平台回复用户的消息加密打包.
	 * 1.对要发送的消息进行AES-CBC加密;
	 * 2.生成安全签名;
	 * 3.将消息密文和安全签名打包成xml格式
	 * @param string $replyMsg 公众平台待回复用户的消息,xml格式的字符串
	 * @param string|int $timeStamp 时间戳,可以自己生成,也可以用URL参数的timestamp
	 * @param string $nonce 随机串,可以自己生成,也可以用URL参数的nonce
	 * @param string $encryptMsg 加密后的可以直接回复用户的密文,包括msg_signature,timestamp,nonce,encrypt的xml格式的字符串,当return返回0时有效
	 * @return int 成功返回0,失败返回错误码
	 */
	public function encryptMsg($replyMsg, $timeStamp, $nonce, &$encryptMsg) {
		//加密
		$array = $this->encrypt($replyMsg, $this->appId, $this->encodingAesKey);
		if ($array[0] != 0) return $array[0];
		$encrypt = $array[1];
		//生成安全签名
		$array = $this->getSHA1($this->token, $timeStamp, $nonce, $encrypt);
		if ($array[0] != 0) return $array[0];
		$signature = $array[1];
		//生成发送的xml
		$encryptMsg = $this->generate($encrypt, $signature, $timeStamp, $nonce);
		return self::$OK;
	}
	
	/**
	 * 检验消息的真实性，并且获取解密后的明文.
	 * 1.利用收到的密文生成安全签名,进行签名验证;
	 * 2.若验证通过,则提取xml中的加密消息;
	 * 3.对消息进行解密
	 * @param string $msgSignature 签名串,对应URL参数的msg_signature
	 * @param string|int $timestamp 时间戳,对应URL参数的timestamp
	 * @param string $nonce 随机串,对应URL参数的nonce
	 * @param string $postData 密文,对应POST请求的数据
	 * @param string $msg 解密后的原文,当return返回0时有效
	 * @return int 成功返回0,失败返回错误码
	 */
	public function decryptMsg($msgSignature, $timestamp, $nonce, $postData, &$msg) {
        if (strlen($this->encodingAesKey) != 43) return self::$IllegalAesKey;
		//提取密文
		$array = $this->extract($postData);
		if ($array[0] != 0) return $array[0];
		$encrypt = $array[1];
		//验证安全签名
		$array = $this->getSHA1($this->token, $timestamp, $nonce, $encrypt);
		if ($array[0] != 0) return $array[0];
		$signature = $array[1];
		if ($signature != $msgSignature) return self::$ValidateSignatureError;
		$result = $this->decrypt($encrypt, $this->appId, $this->encodingAesKey);
		if ($result[0] != 0) return $result[0];
		$msg = $result[1];
		return self::$OK;
	}

	/****************************************计算公众平台的消息签名接口********************************/
	
	/**
	 * 用SHA1算法生成安全签名
	 * @param string $token 票据
	 * @param string|int $timestamp 时间戳
	 * @param string $nonce 随机字符串
	 * @param string $encrypt_msg 密文消息
	 * @return array
	 */
	private function getSHA1($token, $timestamp, $nonce, $encrypt_msg) {
	    try {
	        $array = array($encrypt_msg, $token, $timestamp, $nonce);
	        sort($array, SORT_STRING);
	        $str = implode($array);
	        return array(self::$OK, sha1($str));
	    } catch (\Exception $e) {
	        return array(self::$ComputeSignatureError, null);
	    }
	}
	
	/**********************提供提取消息格式中的密文及生成回复消息格式的接口**********************/
	
	/**
	 * 提取出xml数据包中的加密消息
	 * @param string $xmltext 待提取的xml字符串
	 * @return array 提取出的加密消息字符串
	 */
	private function extract($xmltext) {
	    try {
	        $xml = simplexml_load_string($xmltext, 'SimpleXMLElement', LIBXML_NOCDATA);
	        $encrypt = $xml->Encrypt;
	        return array(0, $encrypt);
	    } catch (\Exception $e) {
	        return array(self::$ParseXmlError, null);
	    }
	}
	
	/**
	 * 生成xml消息
	 * @param string $encrypt 加密后的消息密文
	 * @param string $signature 安全签名
	 * @param string|int $timestamp 时间戳
	 * @param string $nonce 随机字符串
	 * @return string
	 */
	private function generate($encrypt, $signature, $timestamp, $nonce) {
	    $format = "<xml>
                   <Encrypt><![CDATA[%s]]></Encrypt>
                   <MsgSignature><![CDATA[%s]]></MsgSignature>
                   <TimeStamp>%s</TimeStamp>
                   <Nonce><![CDATA[%s]]></Nonce>
                   </xml>";
	    return sprintf($format, $encrypt, $signature, $timestamp, $nonce);
	}
	
	/**********************提供基于PKCS7算法的加解密接口**********************/
	
	/**
	 * 对需要加密的明文进行填充补位
	 * @param string $text 需要进行填充补位操作的明文
	 * @return string 补齐明文字符串
	 */
	private function encode($text) {
	    $block_size = 32;
	    $text_length = strlen($text);
	    //计算需要填充的位数
	    $amount_to_pad = $block_size - ($text_length % $block_size);
	    if ($amount_to_pad == 0) {
	        $amount_to_pad = $block_size;
	    }
	    //获得补位所用的字符
	    $pad_chr = chr($amount_to_pad);
	    $tmp = '';
	    for ($index = 0; $index < $amount_to_pad; $index++) {
	        $tmp .= $pad_chr;
	    }
	    return $text . $tmp;
	}
	
	/**
	 * 对解密后的明文进行补位删除
	 * @param string $text 解密后的明文
	 * @return string 删除填充补位后的明文
	 */
	private function decode($text) {
	    $pad = ord(substr($text, -1));
	    if ($pad < 1 || $pad > 32) $pad = 0;
	    return substr($text, 0, (strlen($text) - $pad));
	}
	
	/**********************提供接收和推送给公众平台消息的加解密接口**********************/
	
	/**
	 * 对明文进行加密
	 * @param string $text 需要加密的明文
	 * @param string $appid 公众号appid
	 * @param string $key aesKey
	 * @return array 加密后的密文
	 */
	private function encrypt($text, $appid, $key) {
	    $key = base64_decode($key . '=');
	    try {
	        //获得16位随机字符串,填充到明文之前
	        $random = $this->getRandomStr();
	        $text = $random . pack('N', strlen($text)) . $text . $appid;
	        //网络字节序
	        $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
	        $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
	        $iv = substr($key, 0, 16);
	        //使用自定义的填充方式对明文进行补位填充
	        $text = $this->encode($text);
	        mcrypt_generic_init($module, $key, $iv);
	        //加密
	        $encrypted = mcrypt_generic($module, $text);
	        mcrypt_generic_deinit($module);
	        mcrypt_module_close($module);
	        //print(base64_encode($encrypted));
	        //使用BASE64对加密后的字符串进行编码
	        return array(self::$OK, base64_encode($encrypted));
	    } catch (\Exception $e) {
	        return array(self::$EncryptAESError, null);
	    }
	}
	
	/**
	 * 对密文进行解密
	 * @param string $encrypted 需要解密的密文
	 * @param string $appid 公众号appid
	 * @param string $key aesKey
	 * @return array 解密得到的明文
	 */
	private function decrypt($encrypted, $appid, $key) {
	    $key = base64_decode($key . '=');
	    try {
	        //使用BASE64对需要解密的字符串进行解码
	        $ciphertext_dec = base64_decode($encrypted);
	        $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
	        $iv = substr($key, 0, 16);
	        mcrypt_generic_init($module, $key, $iv);
	        //解密
	        $decrypted = mdecrypt_generic($module, $ciphertext_dec);
	        mcrypt_generic_deinit($module);
	        mcrypt_module_close($module);
	    } catch (\Exception $e) {
	        return array(self::$DecryptAESError, null);
	    }
	    try {
	        //去除补位字符
	        $result = $this->decode($decrypted);
	        //去除16位随机字符串,网络字节序和AppId
	        if (strlen($result) < 16) return array(self::$IllegalBuffer, null);
	        $content = substr($result, 16, strlen($result));
	        $len_list = unpack("N", substr($content, 0, 4));
	        $xml_len = $len_list[1];
	        $xml_content = substr($content, 4, $xml_len);
	        $from_appid = substr($content, $xml_len + 4);
	    } catch (\Exception $e) {
	        return array(self::$IllegalBuffer, null);
	    }
	    return $from_appid != $appid ? array(self::$ValidateAppidError, null) : array(0, $xml_content);
	}
	
	/**
	 * 随机生成16位字符串
	 * @return string 生成的字符串
	 */
	private function getRandomStr() {
	    $str = '';
	    $str_pol = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
	    $max = strlen($str_pol) - 1;
	    for ($i = 0; $i < 16; $i++) $str .= $str_pol[mt_rand(0, $max)];
	    return $str;
	}
}