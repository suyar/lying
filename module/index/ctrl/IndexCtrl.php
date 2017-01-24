<?php
namespace module\index\ctrl;

use lying\base\Ctrl;

class IndexCtrl extends Ctrl
{
    public $layout = 'main';
    
    public $deny = [];
    
    public function init()
    {
        parent::init();
    }
    
    public function index()
    {
        $c = maker()->cache('dbCache');
        var_dump($c->set('name', 'lying', 10));
        
        return $this->render('index', [
            'name' => 'suyaqi',
        ], $this->layout, ['title'=>'主页']);
    }
    
    public function setApc($count)
    {
        var_dump(apcu_store([
            'name1'=>1,
            'name2'=>2,
            'name3'=>3,
        ], null, 5));
    }
    
    
    public function dec()
    {
        var_dump(maker()->cache('dbCache')->get('name'));
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