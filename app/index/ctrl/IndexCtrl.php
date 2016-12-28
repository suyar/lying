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
        $str = 'sdfdsf你的 是的呢是你的 是的呢是你';
        $key = '123456';
        $this->encrype($str, $key);
    }
    
    public function encrype($str, $key)
    {
        $key = md5($key);
        $publicKey = microtime(true);
        //var_dump($publicKey);
        
        $keystore = bin2hex($str);
        var_dump($keystore);
        
        $strLen = strlen($str);
        for ($i = 0; $i < $strLen; $i++) {
            
            //echo ord($str[$i]) . "<br>";
        }
    }
    
    
}