<?php
namespace app\index\model;
use core\Model;
class Admin extends Model {
    /**
     * 返回关联的表名,如果不是继承core\Model,此方法不是必须的
     * @return string
     */
    public static function tableName() {
        return 'admin';
    }
}