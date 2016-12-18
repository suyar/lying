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
            [null, 'qqq'],
            ['w', 'www'],
            ['e', 'eee'],
        ]);*/
        
        /*$db->createQuery()->from('user')->where([
            'id'=>1,
            'username'=>'lying',
            ['not in', 'id', [1,2,3,4]],
            ['null', 'username', true],
        ]);*/
        
        $res = $db->createQuery()->from('user')->buildCondition([
            'and',
            [
                'or',
                ['not in', 'id', [1,2,3,4]],
                ['null', 'username', true],
            ],
            ['null', 'username', true],
        ]);
        
        /*$res = $db->createQuery()->from('user')->buildCondition([
            'and',
            'id'=>1,
            ['null', 'id', true],
        ]);*/
        
        
        var_dump($res);
        
        
        /*return $this->render('index', [
            'name'=>'su',
            'ad'=>['name'=>'阿里云广告']
        ], [
            'title'=>'呵呵',
            'ad'=>['name'=>'鳄鱼鳄鱼男装']
        ]);*/
    }
    
    
    
}