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
     * @return string
     * @throws \Exception
     */
    public function index()
    {
        return $this->render('index');
    }
}
