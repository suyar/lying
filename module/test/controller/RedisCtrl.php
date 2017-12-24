<?php
namespace module\test\controller;

use lying\service\Controller;
use lying\service\Redis;

/**
 * Class RedisCtrl
 * @package module\test\controller
 */
class RedisCtrl extends Controller
{
    /**
     * @var Redis
     */
    private $redis;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->redis = \Lying::$maker->redis();
    }

    public function index()
    {
        $links = [
            'set' => url('set'),
            'get' => url('get'),
            'flush-db' => url('flush-db'),
        ];
        foreach ($links as $key => $link) {
            echo '<a target="_blank" href="' . $link . '">' . $key . '</a><br>';
        }
    }

    public function set()
    {
        $result = $this->redis->set('username', json_encode(['id'=>1, 'name'=>'苏亚琦']), 10);
        var_dump($result);
    }

    public function get()
    {
        $result = $this->redis->get('username');
        var_dump($result);
    }

    public function flushDb()
    {
        $result = $this->redis->flushDB();
        var_dump($result);
    }
}
