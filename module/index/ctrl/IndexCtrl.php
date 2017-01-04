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
        $m = new \Memcached();
        var_dump($m->addServer('localhost', 11211));
        var_dump($m->getResultMessage());
        var_dump($m->set('ip', false));
        var_dump($m->getResultMessage());
    }
    
    public function get()
    {
        $m = new \Memcached();
        $m->addServer('localhost', 11211);
        var_dump($m->get('ip'));
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