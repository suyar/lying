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
        var_dump(__METHOD__);
    }
    
    public function del()
    {
        var_dump(__METHOD__);
    }
    
    public function userName()
    {
        echo "1000次反解析(秒):<br>";
        $s = microtime(true);
        for ($i = 0; $i < 1000; $i++) {
            $url = maker()->router()->url('user-name', [
                'time'=>'2016-12-22',
                'id'=>7,
                'game'=>'lol+',
                'type'=>'苏亚琦'
            ]);
        }
        var_dump(microtime(true) - $s);
    }
}