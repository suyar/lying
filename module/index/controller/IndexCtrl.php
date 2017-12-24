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
        $this->redirect('user/info/name', ['a'=>1, 'id'=>123, 'name'=>'susu']);
        //return $this->render('index');
    }
}
