<?php
namespace app\index\ctrl;

use lying\base\Ctrl;

class IndexCtrl extends Ctrl
{
    public $layout = 'admin/main';
    
    protected function init()
    {
        
    }
    
    public function index()
    {
        $db = $this->make()->getDb();
        
        //$db->createQuery()->from('user')->insert(['username'=>null, 'password'=>'123654']);
        /*$db->createQuery()->from('user')->batchInsert([
            ['username'=>'q', 'password'=>'qqq'],
            ['username'=>'w', 'password'=>'www'],
            ['username'=>'e', 'password'=>'eee'],
        ]);*/
        /*$db->createQuery()->from('user')->batchInsert(['username', 'password'], [
            ['q', 'qqq'],
            ['w', 'www'],
            ['e', 'eee'],
        ]);*/
        
        /*return $this->render('index', [
            'name'=>'su',
            'ad'=>['name'=>'阿里云广告']
        ], [
            'title'=>'呵呵',
            'ad'=>['name'=>'鳄鱼鳄鱼男装']
        ]);*/
    }
    
    
    
}