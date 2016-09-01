<?php
namespace app\index\controller;
use core\Controller;
use extend\CodeLine;
use app\index\model\Admin;
class Index extends Controller{
    public function index() {
        $count = new CodeLine();
        var_dump($count->countLine(ROOT));
        var_dump($count->countModify(ROOT));
    }
    
    public function admin() {
        $res = Admin::findByPk(1, ['name','tel']);
        var_dump($res);
    }
}