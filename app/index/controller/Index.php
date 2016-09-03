<?php
namespace app\index\controller;
use core\Controller;
use app\index\model\User;
class Index extends Controller{
    public function index() {
        $res = User::findByPk(5);
        var_dump($res);
    }
    
}