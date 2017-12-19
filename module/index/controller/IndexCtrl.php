<?php
namespace module\index\controller;

use lying\service\Controller;

class IndexCtrl extends Controller
{
    public $layout = 'layout';
    
    public function index()
    {
        //$path以'/'开头或者为空,这种不会反解析路由规则
        echo \Lying::$maker->router()->createUrl('', ['a'=>1, 'b'=>2]);
        echo "<br>\n";

        echo \Lying::$maker->router()->createUrl('/', ['a'=>1, 'b'=>2]);
        echo "<br>\n";

        echo \Lying::$maker->router()->createUrl('/user', ['a'=>1, 'b'=>2]);
        echo "<br>\n";

        echo \Lying::$maker->router()->createUrl('/user/info.html', ['a'=>1, 'b'=>2]);
        echo "<br>\n";

        //$path不以'/'开头
        echo \Lying::$maker->router()->createUrl('admin/blog/get', ['id'=>1, 'name'=>'susu', 'num'=>23]);
        echo "<br>\n";

        echo \Lying::$maker->router()->createUrl('login/index', ['id'=>1, 'name'=>'susu', 'num'=>23], true, true);
        echo "<br>\n";





        //return $this->render('index');
    }
}
