<?php
namespace module\test\controller;

use lying\service\Controller;
use lying\service\Cookie;

/**
 * Class CookieCtrl
 * @package module\test\controller
 */
class CookieCtrl extends Controller
{
    /**
     * @var Cookie
     */
    private $cookie;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->cookie = \Lying::$maker->cookie();
    }

    public function index()
    {
        $links = [
            'set' => url('set'),
            'get' => url('get'),
            'exists' => url('exists'),
            'remove' => url('remove'),
        ];
        foreach ($links as $key => $link) {
            echo '<a target="_blank" href="' . $link . '">' . $key . '</a><br>';
        }
    }

    public function set()
    {
        $result = $this->cookie->set('username', ['id'=>1, 'name'=>'苏亚琦'], time() + 10);
        var_dump($result);
    }

    public function get()
    {
        $result = $this->cookie->get('username');
        var_dump($result);
    }

    public function exists()
    {
        $result = $this->cookie->exists('username');
        var_dump($result);
    }

    public function remove()
    {
        $result = $this->cookie->remove('username');
        var_dump($result);
    }
}
