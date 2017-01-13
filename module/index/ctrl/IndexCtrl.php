<?php
namespace module\index\ctrl;

use lying\base\Ctrl;
use lying\db\Query;

class IndexCtrl extends Ctrl
{
    public $layout = 'admin/main';
    
    protected function init()
    {
        
    }
    
    public function index()
    {
        $query = (new Query(maker()->db()))->from(['admin'])->where(['id'=>1])->select('name');
        
        
        
        var_dump($query->column());
        
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