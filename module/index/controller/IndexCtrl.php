<?php
namespace module\index\controller;

use lying\service\Controller;
use lying\service\Helper;

/**
 * Class IndexCtrl
 * @package module\index\controller
 */
class IndexCtrl extends Controller
{
    public $layout = 'layout';

    /**
     * 首页
     * @return string
     * @throws \Exception
     */
    public function index()
    {
        trigger_error('hello');
        //return $this->render('index');
    }
}
