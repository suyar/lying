<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace module\index\controller;

use lying\service\Controller;

/**
 * Class IndexCtrl
 * @package module\index\controller
 */
class IndexCtrl extends Controller
{
    public $layout = 'layout';
    
    public function index()
    {
        $redis = \Lying::$maker->redis();
        var_dump($redis->setex('name', 10, '666'));




        //return $this->render('index');
    }
}
