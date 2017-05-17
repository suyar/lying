<?php
namespace module\index\controller;

use lying\service\Controller;

class Index extends Controller
{
    public $layout = 'layout';
    
    public function index()
    {

        var_dump(\Lying::$maker->router()->createUrl('admin/blog/get', ['id'=>12345]));
        //return $this->render('index');
    }
}
