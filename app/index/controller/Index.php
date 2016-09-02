<?php
namespace app\index\controller;
use core\Controller;
use app\index\model\User;
class Index extends Controller{
    public function index() {
        $user = new User();
        $res = $user->get();
        $res->password = '259758';
        var_dump($res->save());
    }
}