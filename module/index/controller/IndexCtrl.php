<?php
namespace module\index\controller;

use lying\service\Controller;

/**
 * Class IndexCtrl
 * @package module\index\controller
 */
class IndexCtrl extends Controller
{
    /**
     * 首页
     * @return string
     */
    public function index()
    {
        return $this->render();
    }

    public function validate()
    {
        $validate = new \lying\service\Validate();

        //自定义验证
        $func = function (&$value, $column, $data, $exists) {
            $value = trim($value); //引用传值会改变原数组的值,这个值可能会参与其他规则的验证
            return $value === '';
        };
        $validate->rule('name', $func, '用户名错误');
        $validate->rule('name', [$this, 'checkName'], '用户名错误');

        //require
        $validate->rule('name', 'require', '用户名不能为空');
        //require验证失败设置默认值
        $validate->rule('name', ['require', 'default'=>'randname'], '用户名不能为空');
        //满足条件才进行require判断
        $validate->rule('name', ['require'=>function ($value, $column, $data) { return true; }, 'default'=>'randname'], '用户名不能为空');
        $validate->rule('name', ['require'=>[$this, 'checkName']], '用户名不能为空');

        //int
        $validate->rule('age', 'int', '年龄只能为整数');
        $validate->rule('age', ['int'=>[5,10]], '年龄只能为5-10的整数');

        //number
        $validate->rule('paypass', 'number', '支付密码只能为整数');

        //float
        $validate->rule('money', 'float', '金额需要');



    }
}
