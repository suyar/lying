<?php
namespace module\index\model;

use lying\base\AR;

class UserModel extends AR
{

    
    public static function table()
    {
        return 'user';
    }
    
    public static function db()
    {
        return 'db';
    }
    
}