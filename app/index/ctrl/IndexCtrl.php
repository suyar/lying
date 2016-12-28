<?php
namespace app\index\ctrl;

use lying\base\Ctrl;

class IndexCtrl extends Ctrl
{
    public $layout = 'admin/main';
    
    protected function init()
    {
        
    }
    
    public function index()
    {
        $str = '1';
        $key = '123456';
        $res = $this->encrype($str, $key, 2);
        var_dump(base64_encode($res));
        var_dump($this->decrypt($res, $key));
    }
    
    public function encrype($str, $key, $exp = 0)
    {
        $keyHash = md5($key, true);
        $strHash = md5($str, true);
        $rendomKey = substr(md5(microtime(true)), -4);
        
        $str = sprintf('%010d', $exp === 0 ? 0 : time() + $exp) . $strHash . $str;
        
        
        
        $strLen = strlen($str);
        $keyMap = bin2hex(md5($rendomKey.$keyHash));
        $keyLen = strlen($keyMap);
        
        
        $result = '';
        for ($i = 0; $i < $strLen; $i++) {
            $result .= chr(ord($str[$i]) ^ ord($keyMap[$i % $keyLen]));
        }
        return $rendomKey.$result;
    }
    
    public function decrypt($str, $key)
    {
        $keyHash = md5($key, true);
        $rendomKey = substr($str, 0, 4);
        
        $str = substr($str, 4);
        $keyMap = bin2hex(md5($rendomKey.$keyHash));
        $strLen = strlen($str);
        $keyLen = strlen($keyMap);
        
        $result = '';
        for ($i = 0; $i < $strLen; $i++) {
            $result .= chr(ord($str[$i]) ^ ord($keyMap[$i % $keyLen]));
        }
        $time = substr($result, 0, 10);
        $strHash = substr($result, 10, 16);
        $str = substr($result, 26);
        
        var_dump($time > time());
        
        var_dump($strHash === md5($str, true));
        
        return $str;
    }
    
    
}