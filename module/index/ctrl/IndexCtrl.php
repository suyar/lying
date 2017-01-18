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
    
    public function index($id = 1)
    {
        
        //throw new \Exception('测试异常', 404);
        //strpos($haystack, $needle);
        //trigger_error('主动触发了一个错误');
        //strpos($haystack, $needle)
        
        return $this->render('index', [
            'name' => 'suyaqi',
        ], $this->layout, ['title'=>'主页']);
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