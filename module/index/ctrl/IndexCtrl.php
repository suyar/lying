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
        $query = (new \lying\db\Query())
        ->select(['u'=>'lying.user'])
        ->from('user')
        ->join('left join', ['admin'])
        ->build();
    }
    
    public function get()
    {
        $a = [2=>'pp',3=>'oo'];
        $b = [2=>'dd', 3=>'cc'];
        var_dump(array_merge($a, $b));
        
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