<?php
namespace module\index\ctrl;

use lying\base\Ctrl;

class Index extends Ctrl
{
    public $layout = 'layout';
    
    public function index()
    {
        
        return $this->render('index');
    }
}
