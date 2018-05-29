<?php
namespace module\index\controller;

use lying\service\Controller;
use module\index\model\UserModel;

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
