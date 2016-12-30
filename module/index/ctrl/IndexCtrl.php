<?php
namespace module\index\ctrl;

use lying\base\Ctrl;

class IndexCtrl extends Ctrl
{
    public $layout = 'admin/main';
    
    protected function init()
    {
        
    }
    
    public function index()
    {
        var_dump($_GET);
    }
    
    public function del()
    {
        var_dump($_GET);
    }
    
    public function userName()
    {
        //var_dump($_GET);
        maker()->router()->url('index/index/user', ['time'=>'2016-12-22', 'id'=>7]);
    }
}