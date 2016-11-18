<?php
namespace app\index\ctrl;

use lying\base\Ctrl;

class IndexCtrl extends Ctrl
{
    public $layout = 'admin/main';
    public function index()
    {
        \Lying::$container->get('cookie')->send('name', '苏亚琦');
        
        var_dump(\Lying::$container->get('cookie')->find('name'));
        //$this->redirect(['admin/index/index'], ['dsds&'=>3, 'dddd'=>'50%','name1'=>'suyaqi'], ['name'=>'su=yaqi']);
        /*return $this->render('index', [
            'name'=>'su',
            'ad'=>['name'=>'阿里云广告']
        ], [
            'title'=>'哈哈',
            'ad'=>['name'=>'鳄鱼鳄鱼']
        ]);*/
    }
}