<?php
namespace extend;
/**
 * 开放平台相关操作
 * @author suyq
 */
class WechatOpen {
    //第三方平台appid
    private $_component_appid;
    //第三方平台appsecret
    private $_component_appsecret;
    //微信后台推送的ticket,10分钟推送一次
    private $_component_verify_ticket;
    //curl组件
    private $_http;
    //错误代码
    private $_errcode = 0;
    //错误信息
    private $_errmsg = '';
    
    /**
     * 构造函数
     * @param string $component_appid 第三方平台appid
     * @param string $component_appsecret 第三方平台appsecret
     * @param string $component_verify_ticket 微信后台推送的ticket,10分钟推送一次
     */
    public function __construct($component_appid, $component_appsecret, $component_verify_ticket) {
        $this->_component_appid = $component_appid;
        $this->_component_appsecret = $component_appsecret;
        $this->_component_verify_ticket = $component_verify_ticket;
        $this->_http = \App::http();
    }
    
    /**
     * 设置错误代码和错误信息
     * @param int $errcode 错误代码
     * @param string $errmsg 错误信息
     */
    private function setError($errcode, $errmsg) {
        $this->_errcode = $errcode;
        $this->_errmsg = $errmsg;
    }
    
    /**
     * 出错的时候获取错误代码
     * @return int 如果没出错,返回0
     */
    public function getErrCode() {
        $errcode = $this->_errcode;
        $this->_errcode = 0;
        return $errcode;
    }
    
    /**
     * 出错的时候获取错误信息
     * @return string 如果没出错,返回空字符串
     */
    public function getErrmsg() {
        $errmsg = $this->_errmsg;
        $this->_errmsg = '';
        return $errmsg;
    }
    
    /**
     * 获取第三方平台的token,有效期7200秒(一般设置为7000秒过期)
     * @return string|boolean 成功返回component_access_token,失败返回false,可调用getErrmsg()获取错误信息
     */
    public function component_access_token() {
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_component_token';
        $data = json_encode([
            'component_appid'         => $this->_component_appid,
            'component_appsecret'     => $this->_component_appsecret,
            'component_verify_ticket' => $this->_component_verify_ticket
        ]);
        try {
            $jsonStr = $this->_http->httpsPost($url, $data);
            $jsonObj = json_decode($jsonStr);
            if ($jsonObj && isset($jsonObj->component_access_token)) {
                return $jsonObj->component_access_token;
            }else if (isset($jsonObj->errcode)) {
                $this->setError($jsonObj->errcode, $jsonObj->errmsg);
            }else {
                $this->setError(-1, '获取component_access_token失败');
            }
            return false;
        } catch (\Exception $e) {
            $this->setError(-1, $e->getMessage());
            return false;
        }
    }
    
    /**
     * 获取授权用的pre_auth_code
     * @param string $token 第三方平台的component_access_token
     * @return string|boolean 成功返回pre_auth_code,失败返回false,可调用getErrmsg()获取错误信息
     */
    public function pre_auth_code($token) {
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode?component_access_token='.$token;
        $data = json_encode([
            'component_appid' => $this->_component_appid
        ]);
        try {
            $jsonStr = $this->_http->httpsPost($url, $data);
            $jsonObj = json_decode($jsonStr);
            if ($jsonObj && isset($jsonObj->component_appid)) {
                return $jsonObj->component_appid;
            }else if (isset($jsonObj->errcode)) {
                $this->setError($jsonObj->errcode, $jsonObj->errmsg);
            }else {
                $this->setError(-1, '获取pre_auth_code失败');
            }
            return false;
        } catch (\Exception $e) {
            $this->setError(-1, $e->getMessage());
            return false;
        }
    }
    
    /**
     * 获取跳转到授权页面的url
     * @param string $token 第三方平台的component_access_token
     * @param string $redirect_uri 要跳转的url(可带get参数)
     * @return string|boolean 成功返回url,失败返回false,可调用getErrmsg()获取错误信息
     */
    public function auth_uri($token, $redirect_uri) {
        $pre_auth_code = $this->pre_auth_code($token);
        if ($pre_auth_code !== false) {
            $redirect_uri = urlencode($redirect_uri);
            $uri = 'https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid='.$this->_component_appid.'&pre_auth_code='.$pre_auth_code.'&redirect_uri='.$redirect_uri;
            return $uri;
        }
        return false;
    }
    
    /**
     * 使用授权码换取公众号的接口调用凭据和授权信息
     * @param string $token 第三方平台的component_access_token
     * @param string $authorization_code 回调uri附带的auth_code参数
     * @return object|boolean 成功返回接口调用凭据和授权信息的object,失败返回false,可调用getErrmsg()获取错误信息
     * 不清楚的字段请查看文档https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1453779503&token=&lang=zh_CN
     */
    public function api_query_auth($token, $authorization_code) {
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_query_auth?component_access_token='.$token;
        $data = json_encode([
            'component_appid'    => $this->_component_appid,
            'authorization_code' => $authorization_code
        ]);
        try {
            $jsonStr = $this->_http->httpsPost($url, $data);
            $jsonObj = json_decode($jsonStr);
            if ($jsonObj && isset($jsonObj->authorization_info)) {
                return $jsonObj;
            }else if (isset($jsonObj->errcode)) {
                $this->setError($jsonObj->errcode, $jsonObj->errmsg);
            }else {
                $this->setError(-1, '获取公众号的接口调用凭据失败');
            }
            return false;
        } catch (\Exception $e) {
            $this->setError(-1, $e->getMessage());
            return false;
        }
    }
    
    /**
     * 刷新授权公众号的接口调用凭据
     * @param string $token 第三方平台的component_access_token
     * @param string $authorizer_appid 公众号appid
     * @param string $authorizer_refresh_token 公众号refresh_token
     * @return object|boolean 成功返回接口调用凭据的object,失败返回false,可调用getErrmsg()获取错误信息
     * 不清楚的字段请查看文档https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1453779503&token=&lang=zh_CN
     */
    public function api_authorizer_token($token, $authorizer_appid, $authorizer_refresh_token) {
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token?component_access_token='.$token;
        $data = json_encode([
            'component_appid'          => $this->_component_appid,
            'authorizer_appid'         => $authorizer_appid,
            'authorizer_refresh_token' => $authorizer_refresh_token
        ]);
        try {
            $jsonStr = $this->_http->httpsPost($url, $data);
            $jsonObj = json_decode($jsonStr);
            if ($jsonObj && isset($jsonObj->authorizer_access_token)) {
                return $jsonObj;
            }else if (isset($jsonObj->errcode)) {
                $this->setError($jsonObj->errcode, $jsonObj->errmsg);
            }else {
                $this->setError(-1, '刷新公众号的接口调用凭据失败');
            }
            return false;
        } catch (\Exception $e) {
            $this->setError(-1, $e->getMessage());
            return false;
        }
    }
    
    /**
     * 获取授权方的公众号帐号基本信息
     * @param string $token 第三方平台的component_access_token
     * @param string $authorizer_appid 公众号appid
     * @return object|boolean 成功返回公众号帐号基本信息的object,失败返回false,可调用getErrmsg()获取错误信息
     * 不清楚的字段请查看文档https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1453779503&token=&lang=zh_CN
     */
    public function api_get_authorizer_info($token, $authorizer_appid) {
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info?component_access_token='.$token;
        $data = json_encode([
            'component_appid' => $this->_component_appid,
            'authorizer_appid'=> $authorizer_appid
        ]);
        try {
            $jsonStr = $this->_http->httpsPost($url, $data);
            $jsonObj = json_decode($jsonStr);
            if ($jsonObj && isset($jsonObj->authorizer_info)) {
                return $jsonObj;
            }else if (isset($jsonObj->errcode)) {
                $this->setError($jsonObj->errcode, $jsonObj->errmsg);
            }else {
                $this->setError(-1, '获取授权方的公众号帐号基本信息失败');
            }
            return false;
        } catch (\Exception $e) {
            $this->setError(-1, $e->getMessage());
            return false;
        }
    }
    
    /**
     * 获取授权方的选项设置信息
     * @param string $token 第三方平台的component_access_token
     * @param string $authorizer_appid 公众号appid
     * @param int $option 1、地理位置上报开关;2、语音识别开关;3、多客服开关
     * @return int|boolean 0:关(无上报),1:开(进入会话时上报),2:每5s上报
     */
    public function api_get_authorizer_option($token, $authorizer_appid, $option) {
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_option?component_access_token='.$token;
        $opt = '';
        switch ($option) {
            case 1:$opt = 'location_report';break;
            case 2:$opt = 'voice_recognize';break;
            case 3:$opt = 'customer_service';break;
            default:$opt = 'location_report';
        }
        $data = json_encode([
            'component_appid' => $this->_component_appid,
            'authorizer_appid'=> $authorizer_appid,
            'option_name'     => $opt
        ]);
        try {
            $jsonStr = $this->_http->httpsPost($url, $data);
            $jsonObj = json_decode($jsonStr);
            if ($jsonObj && isset($jsonObj->option_value)) {
                return $jsonObj->option_value;
            }else if (isset($jsonObj->errcode)) {
                $this->setError($jsonObj->errcode, $jsonObj->errmsg);
            }else {
                $this->setError(-1, '获取授权方的选项设置信息失败');
            }
            return false;
        } catch (\Exception $e) {
            $this->setError(-1, $e->getMessage());
            return false;
        }
    }
    
    /**
     * 设置授权方的选项信息
     * @param string $token 第三方平台的component_access_token
     * @param string $authorizer_appid 公众号appid
     * @param int $option 1、地理位置上报开关;2、语音识别开关;3、多客服开关
     * @param int $value 选项值,只能是0、1、2(当$option为1的时候可以设置,5秒上报一次)
     * @return boolean 成功返回true,失败返回false,可调用getErrmsg()获取错误信息
     */
    public function api_set_authorizer_option($token, $authorizer_appid, $option, $value) {
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_set_authorizer_option?component_access_token='.$token;
        $opt = '';
        switch ($option) {
            case 1:$opt = 'location_report';break;
            case 2:$opt = 'voice_recognize';break;
            case 3:$opt = 'customer_service';break;
            default:$opt = 'location_report';
        }
        $value = in_array($value, [0,1,2]) ? $value : 0;
        $data = json_encode([
            'component_appid' => $this->_component_appid,
            'authorizer_appid'=> $authorizer_appid,
            'option_name'     => $opt,
            'option_value'    => (int)$value
        ]);
        try {
            $jsonStr = $this->_http->httpsPost($url, $data);
            $jsonObj = json_decode($jsonStr);
            if (isset($jsonObj->errcode) && $jsonObj->errcode == 0) {
                return true;
            }else if (isset($jsonObj->errcode)) {
                $this->setError($jsonObj->errcode, $jsonObj->errmsg);
            }else {
                $this->setError(-1, '设置授权方的选项信息失败');
            }
            return false;
        } catch (\Exception $e) {
            $this->setError(-1, $e->getMessage());
            return false;
        }
    }
}