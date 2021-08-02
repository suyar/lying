<?php
namespace module\index\controller;

use lying\service\Controller;

/**
 * Class IndexCtrl
 * @package module\index\controller
 */
class IndexCtrl extends Controller
{
    /**
     * 首页
     * @return string
     */
    public function index()
    {
        return $this->render('index');
    }
}
