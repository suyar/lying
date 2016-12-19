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
        $db->createQuery()->select([])
        ->where(['or', 'id'=>1, ['in', 'id', [7, 8, 9]]])
        ->andWhere("username = :username", [':username'=>'susu'])
        ->orWhere(['>=', 'val - sex', 10])
        ->getWhere();
        
    }
    
    
    
}