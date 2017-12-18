<?php
namespace module\index\controller;

use lying\service\Controller;

class IndexCtrl extends Controller
{
    public $layout = 'layout';
    
    public function index()
    {
        //$path以'/'开头或者为空
        echo \Lying::$maker->router()->createUrl1('', ['a'=>1, 'b'=>2]);
        echo "<br>";

        echo \Lying::$maker->router()->createUrl1('/', ['a'=>1, 'b'=>2]);
        echo "<br>";

        echo \Lying::$maker->router()->createUrl1('/user', ['a'=>1, 'b'=>2]);
        echo "<br>";

        echo \Lying::$maker->router()->createUrl1('/user/info.html', ['a'=>1, 'b'=>2]);
        echo "<br>";

        //$path不以'/'开头






        //return $this->render('index');
    }
}
