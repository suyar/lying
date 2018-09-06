<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\service;

/**
 * Class Validate
 * @package lying\service
 */
class Validate extends Service
{
    /**
     * @var array 校验规则
     */
    private $_rules = [];

    /**
     * @var string 错误信息
     */
    private $_error = '';

    /**
     * @inheritdoc
     */
    final protected function init()
    {
        parent::init();
        self::rules($this);
    }

    /**
     * 定义校验规则
     * @param Validate $validate
     */
    protected static function rules(Validate $validate)
    {
        $validate->rule('name', 'require', '不能为空', 'login')
            ->rule('name', 'require', '不能为空', 'login')
            ->rule('name', 'require', '不能为空', 'login')
            ->rule('name', 'require', '不能为空', 'login');
    }


    protected function setError($column, $message, $ruleName = '')
    {
        $this->_error = $message;
    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getError()
    {
        return $this->_error;
    }

    /**
     * 添加校验规则
     * @param string $column 字段名
     * @param string|array|\Closure $rule 规则
     * @param string|array $message 错误提示
     * @param string|array $onscene 字段校验场景
     * @return $this
     */
    public function rule($column, $rule, $message, $onscene = '')
    {
        foreach ((array)$onscene as $scene) {
            $this->_rules[$column . '.' . $scene] = [$column, $rule, $message, $scene];
        }
        return $this;
    }


    public function check(array $data, $scene = '')
    {
        $helper = \Lying::$maker->helper;

        foreach ($this->_rules as $item) {

            list($column, $rule, $message, $onscene) = $item;

            if ($onscene == $scene) {

                $value = $helper->arrGetter($data, $column, null, $exists);

                if (is_callable($rule)) {
                    $result = call_user_func_array($rule, [&$value, $column, $data, $exists]);
                    if ($result === true) {
                        //如果函数使用引用传值,则应该要改变原始数组的值
                        $helper->arrSetter($data, $column, $value);
                    } else {

                    }

                } elseif (is_string($rule)) {
                    $method = 'valid' . ucfirst($rule);
                    if (method_exists($this, $method)) {
                        $result = call_user_func_array([$this, $method], [$value]);
                    }

                } elseif (is_array($rule)) {
                    //设置了过滤器
                    if (array_key_exists('filter', $rule)) {
                        $filter = $rule['filter'];
                        if (is_callable($filter)) {
                            $value = call_user_func_array($filter, [$value]);
                            $helper->arrSetter($data, $column, $value);
                        }
                        unset($rule['filter']);
                    }

                    if (array_key_exists('default', $rule)) {
                        $default = $rule['default'];
                        unset($rule['default']);
                    }





                    foreach ($rule as $name => $r) {
                        if (is_string($name)) {
                            $method = $method = 'valid' . ucfirst($name);
                            $result = $this->$method($value, $r);

                        } else {

                        }
                    }

                }



            }

        }
    }


    /**
     * 校验字段值是否不为空
     * @param mixed $value 检验的值
     * @param mixed $default 默认值
     * @return bool
     */
    public function validRequire(&$value, $default = null)
    {
        $result = $value === null || $value === '' || (is_array($value) && count($value) < 1);
        if ($result === true && isset($default)) {
            $value = $default;
            $result = false;
        }
        return !$result;
    }
}
