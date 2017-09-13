<?php
namespace console\index\controller;

use lying\service\Controller;

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
