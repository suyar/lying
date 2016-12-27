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
        maker()->logger()->log(['尤里', '苏', 'name'=>['666', 'sex'=>false]]);
    }
    
    
    
}