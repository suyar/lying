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
        
        $db->getSchema('user');
        
        
        
        /*return $this->render('index', [
            'name'=>'su',
            'ad'=>['name'=>'阿里云广告']
        ], [
            'title'=>'呵呵',
            'ad'=>['name'=>'鳄鱼鳄鱼男装']
        ]);*/
    }
    
    
    
}