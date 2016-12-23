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
        
        $db->createQuery()
            ->distinct()
            ->select(['id','name'])
            ->from(['user'])
            ->where(['or', 'id'=>1, ['in', 'id', [7, 8, 9]]])
            ->andWhere("username = :username", [':username'=>'susu'])
            ->orWhere(['>=', 'val - sex', 10])
            ->orderBy(['id'=>SORT_DESC, 'name'])
            ->groupBy('id')
            ->having(['count(name)'=>10])
            ->join('LEFT JOIN', 'file', "admin.id = user.id")
            ->limit(1)
            ->buildQuery();
        
    }
    
    
    
}