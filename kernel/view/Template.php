<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\view;

use lying\cache\FileCache;
use lying\service\Service;

/**
 * Class Template
 * @package lying\view
 */
class Template extends Service
{
    /**
     * @var string|FileCache
     */
    protected $cache;

    /**
     * @var View
     */
    protected $view;

    /**
     * @inheritdoc
     */
    protected function init()
    {
        parent::init();
        $this->cache = \Lying::$maker->cache($this->cache);
    }


    public function render($file, $params)
    {

    }
}
