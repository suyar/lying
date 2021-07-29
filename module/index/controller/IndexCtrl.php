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

    public function s()
    {
        $mem = \Lying::$maker->cache('memcached');
        var_dump($mem->set('name', 'suyaqi', 10));
    }

    public function m()
    {
        $mem = \Lying::$maker->cache('memcached');
        var_dump($mem->exists('name'), $mem->get('name'));
    }
}
