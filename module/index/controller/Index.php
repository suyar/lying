<?php
namespace module\index\controller;

use lying\base\Controller;

class Index extends Controller
{
    public $layout = 'layout';
    
    public function index()
    {
        return url('index/index', ['id'=>100]);
        return $this->render('index');
    }

    public function name()
    {


    }
}
