<?php
namespace app\index\controller;
use core\Controller;
use extend\CodeLine;
class Index extends Controller{
    public function index() {
        $code = new CodeLine();
        var_dump($code->countLine(ROOT));
        
    }
    
}