<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/9
 * Time: 11:05
 */

namespace module\error\controller;


use lying\service\Controller;

class IndexCtrl extends Controller
{
    public function index($exception, $message)
    {
        return 111;
    }
}