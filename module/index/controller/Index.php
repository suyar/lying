<?php
namespace module\index\controller;

use lying\service\Controller;

class Index extends Controller
{
    public $layout = 'layout';
    
    public function index()
    {
        $a = [1,2,3];
        var_dump((string)$a);exit;
        return $this->render('index');
    }

    public function user($id)
    {



    }
}
