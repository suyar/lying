<?php
namespace module\index\controller;

use lying\db\ActiveRecord;
use lying\service\Controller;
use module\index\model\User;

class Index extends Controller
{
    public $layout = 'layout';
    
    public function index()
    {
        return $this->render('index');
    }
}
