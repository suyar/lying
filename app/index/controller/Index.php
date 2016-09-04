<?php
namespace app\index\controller;
use core\Controller;
use app\index\model\User;
class Index extends Controller{
    public function index() {
        //$res = User::insert(['id'=>10, 'name'=>'ooo']);
        $res = User::update(['username'=>'sususuyayayaqiqiqiq'], "id = ?", [1]);
        var_dump($res);
    }
    
}