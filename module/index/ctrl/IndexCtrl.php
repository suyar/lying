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
        var_dump(gethostbyaddr($_SERVER['REMOTE_ADDR']));
        var_dump($_SERVER);
    }
    
}