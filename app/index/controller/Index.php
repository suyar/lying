<?php
namespace app\index\controller;
use core\Controller;
class Index extends Controller{
    public function index() {
        var_dump(\App::request()->ipInfo());
    }
    
}