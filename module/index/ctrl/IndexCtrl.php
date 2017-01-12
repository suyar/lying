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
        $arr = [
            'and',
            'lying.name'=>'lying',
            'id'=>[1,2,3],
            'sex'=>null,
            [
                'or', 
                ['and', 'a'=>1, 'b'=>2],
                ['in', 'baby', [666,888]],
            ],
        ];
        
        $query = (new \lying\db\Query())
        ->select(['u'=>'lying.user'])
        ->from('user')
        ->join('left join', 'admin', 'user.id = admin.id')
        ->where($arr)
        ->build();
    }
    
    public function get()
    {
        //a = 1 or b = 2 and c = 3 or d = 4
        
        $arr = [
            'and',
            'name'=>'lying',
            ['or', 'a'=>1, 'b'=>2],
            ['or', 'c'=>3, 'd'=>4]
        ];
        
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