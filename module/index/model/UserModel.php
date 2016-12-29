<?php
namespace app\index\model;

use lying\base\Model;

class UserModel extends Model
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