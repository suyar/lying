<?php
namespace module\index\controller;

use lying\db\Schema;
use lying\service\Controller;

class Index extends Controller
{
    public $layout = 'layout';
    
    public function index()
    {
        return $this->render('index');
    }
}
