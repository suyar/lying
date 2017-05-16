<?php
namespace module\index\controller;

use lying\service\Controller;

class Index extends Controller
{
    public $layout = 'layout';
    
    public function index()
    {
        $info = parse_url(\Lying::$maker->request()->uri());
        var_dump($info);
        return $this->render('index');
    }
}
