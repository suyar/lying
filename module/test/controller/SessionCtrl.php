<?php
namespace module\test\controller;

use lying\service\Controller;
use lying\service\Session;

/**
 * Class SessionCtrl
 * @package module\test\controller
 */
class SessionCtrl extends Controller
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->session = \Lying::$maker->session();
    }

    public function index()
    {
        $links = [
            'isActive' => url('is-active'),
            'start' => url('start'),
            'set' => url('set'),
            'get' => url('get'),
            'exists' => url('exists'),
            'remove' => url('remove'),
            'destroy' => url('destroy'),
        ];
        foreach ($links as $key => $link) {
            echo '<a target="_blank" href="' . $link . '">' . $key . '</a><br>';
        }
    }

    public function isActive()
    {
        $result = $this->session->isActive();
        var_dump($result);
    }

    public function start()
    {
        $result = $this->session->start();
        var_dump($result);
    }

    public function set()
    {
        $result = $this->session->set('username', ['id'=>1, 'name'=>'苏亚琦']);
        var_dump($result);
    }

    public function get()
    {
        $result = $this->session->get('username');
        var_dump($result);
    }

    public function exists()
    {
        $result = $this->session->exists('username');
        var_dump($result);
    }

    public function remove()
    {
        $result = $this->session->remove('username');
        var_dump($result);
    }

    public function destroy()
    {
        $result = $this->session->destroy();
        var_dump($result);
    }
}
