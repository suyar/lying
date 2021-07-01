<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\db;

use lying\service\Service;

/**
 * Class BaseActive
 * @package lying\db
 */
class BaseActive extends Service
{
    /**
     * 旧数据赋值,这个在ActiveRecord才有用
     */
    protected function reload()
    {

    }
}
