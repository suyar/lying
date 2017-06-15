<?php
namespace module\index\controller;

use lying\db\Schema;
use lying\service\Controller;

class Index extends Controller
{
    public $layout = 'layout';
    
    public function index()
    {
        $schema = new Schema(\Lying::$maker->db());
        var_dump($schema->getTableSchema('user'));
        return $this->render('index');
    }
}
