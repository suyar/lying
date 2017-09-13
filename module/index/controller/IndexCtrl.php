<?php
namespace module\index\controller;

use lying\service\Controller;

class IndexCtrl extends Controller
{
    public $layout = 'layout';
    
    public function index()
    {
        return $this->render('index');
    }
}
