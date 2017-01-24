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
        
        return $this->render('index', [
            'name' => 'suyaqi',
        ], $this->layout, ['title'=>'主页']);
    }
    
    public function setApc($count)
    {
        var_dump(apcu_store('cards', (int)$count));
    }
    
    
    public function dec()
    {
        var_dump(apcu_dec('cards', 1, $success));
        var_dump($success);
        var_dump(apcu_clear_cache());
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