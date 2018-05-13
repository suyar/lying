<?php
namespace module\index\controller;

use lying\service\Controller;

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
     * @throws \Throwable
     */
    public function index()
    {
        return $this->render('index');
    }
}
