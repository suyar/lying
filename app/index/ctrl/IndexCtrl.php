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
        
        $query = $db->createQuery()->from('admin')->select('max(id)')->where(['admin.id'=>100]);
        
        $res = $db->createQuery()
        //->distinct()
        //->select(['id','name'])
        ->from(['user'])
        //->where(['or', 'id'=>1, ['in', 'id', [7, 8, 9]], ['not exists', null, $query]])
        //->andWhere("username = :username", [':username'=>'susu'])
        //->orWhere(['>=', 'val - sex', 10])
        //->orderBy(['id'=>SORT_DESC, 'name'])
        //->groupBy('id')
        //->having(['count(name)'=>10])
        //->join('LEFT JOIN', ['f'=>$query], "admin.id = user.id")
        //->limit(1)
        //->union($query)
        //->buildQuery();
        ->fecthAll();
        var_dump($res);
        
        //$db->createQuery()->update('user', 'val = val * 2', ['id'=>1]);
        //$res = $db->createQuery()->delete('user');
        //var_dump($res);
    }
    
    
    
}