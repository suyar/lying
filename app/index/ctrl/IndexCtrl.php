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
        //var_dump($_SERVER);
        //maker()->cookie()->set('name', 'suyaqi');
        $key = '123';
        $data = 'suyaqi';
        $key = $iv = md5($key, true);
        $res = openssl_encrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        $res = base64_encode($res);
        var_dump($res);
        $res = openssl_decrypt(base64_decode($res), 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        var_dump($res);
    }
    
    
    
}