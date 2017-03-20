<?php
namespace module\index\controller;

use lying\base\Controller;

class Index extends Controller
{
    public $layout = 'layout';
    
    public function index()
    {

        return $this->render('index');
    }
}
