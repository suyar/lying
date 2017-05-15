<?php
namespace module\index\controller;

use lying\service\Controller;

class Index extends Controller
{
    public $layout = 'layout';
    
    public function index()
    {
        var_dump(\Lying::$maker->request()->uri());
        return $this->render('index');
    }
}
