<?php
namespace module\test\controller;

use lying\cache\Cache;
use lying\service\Controller;

/**
 * Class CacheCtrl
 * @package module\test\controller
 */
class CacheCtrl extends Controller
{
    /**
     * @var Cache
     */
    private $cache;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        //$cacheId = 'cache';
        //$cacheId = 'apcu';
        $cacheId = 'memcached';
        $this->cache = \Lying::$maker->cache($cacheId);
    }

    public function index()
    {
        $links = [
            'add' => url('add'),
            'madd' => url('madd'),
            'set' => url('set'),
            'mset' => url('mset'),
            'get' => url('get'),
            'mget' => url('mget'),
            'exist' => url('exist'),
            'del' => url('del'),
            'flush' => url('flush'),
        ];
        foreach ($links as $key => $link) {
            echo '<a target="_blank" href="' . $link . '">' . $key . '</a><br>';
        }
    }

    public function add()
    {
        $result = $this->cache->add('username', ['id'=>1, 'name'=>'苏亚琦'], 10);
        var_dump($result);
    }

    public function madd()
    {
        $result = $this->cache->madd(['username'=>['id'=>1, 'name'=>'苏亚琦'], 'sex'=>1], 10);
        var_dump($result);
    }

    public function set()
    {
        $result = $this->cache->set('username', ['id'=>1, 'name'=>'苏亚琦'], 10);
        var_dump($result);
    }

    public function mset()
    {
        $result = $this->cache->mset(['username'=>['id'=>1, 'name'=>'苏亚琦'], 'sex'=>1], 10);
        var_dump($result);
    }

    public function get()
    {
        $result = $this->cache->get('username');
        var_dump($result);
    }

    public function mget()
    {
        $result = $this->cache->mget(['username', 'sex']);
        var_dump($result);
    }

    public function exist()
    {
        $result = $this->cache->exist('username');
        var_dump($result);
    }

    public function del()
    {
        $result = $this->cache->del('username');
        var_dump($result);
    }

    public function flush()
    {
        $result = $this->cache->flush();
        var_dump($result);
    }
}
