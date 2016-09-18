<?php
namespace extend;
/**
 * 验证github的webhooks推送
 * @author suyq
 */
class Webhooks {
    
    /**
     * 验证github的webhooks推送
     * @param string $secret hmac的密钥
     * @param boolean $returnData 是否返回推送的数据(默认返回boolean)
     * @return string|boolean 成功返回ture或者数据,失败返回false
     */
    public static function validate($secret, $returnData = false) {
        if (isset($_SERVER['HTTP_X_HUB_SIGNATURE'])) {
            list($algo, $signature) = explode('=', $_SERVER['HTTP_X_HUB_SIGNATURE']);
            $data = file_get_contents('php://input');
            $res = hash_hmac($algo, $data, $secret);
            return $returnData ? $data : ($res == $signature);
        }
        return false;
    }
}