<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace console\index\controller;

use lying\service\Controller;

/**
 * Class IndexCtrl
 * @package console\index\controller
 */
class IndexCtrl extends Controller
{
    public function index()
    {
        $LOGO = <<<EOL
     __        __
    / / __ __ /_/__  __ ____
   / / / // // //  \/ // _  \
  / /_/ // // // /\  // // /
 /____\_  //_//_/ /_/_\_  /
    /____/          \____/
EOL;
        return $LOGO;
    }
}
