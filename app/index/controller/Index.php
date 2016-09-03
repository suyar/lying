<?php
namespace app\index\controller;
use core\Controller;
use app\index\model\User;
class Index extends Controller{
    public function index() {
        $user = new User();
        $res = $user->get();
        $res->username = 'xlq';
        var_dump($res->save());
    }
    
    public function add() {
        $user = new User();
        $user->username = "add";
        $user->password = "this is add";
        $user->sex = 1;
        var_dump($user->save());
    }
}