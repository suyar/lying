<?php
namespace module\index\ctrl;

use lying\base\Ctrl;

class IndexCtrl extends Ctrl
{
    public $layout = 'layout';
    
    public function index()
    {
        
        return $this->render('index');
    }
}
