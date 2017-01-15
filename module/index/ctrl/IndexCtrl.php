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
        
    }
    
    public function get()
    {
        
    }
    
    public function userName()
    {
        $url = url('user-name', [
            'time'=>'2016-12-22',
            'id'=>7,
            'game'=>'lol+',
            'type'=>'苏亚琦'
        ]);
        var_dump($url);
    }
}