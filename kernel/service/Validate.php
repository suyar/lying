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
        $validate->addRule('name', 'require', '不能为空')
        ->addRule('name', []);
    }

    /**
     * 设置错误信息
     * @param string $error
     */
    protected function setError($error)
    {
        $this->_error = $error;
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
     * @param string $msg 错误提示
     * @param string|array $scene 字段校验场景
     * @return $this
     */
    public function addRule($column, $rule, $msg, $scene = null)
    {
        $this->_rules[] = [$column, (array)$rule, $msg, (array)$scene];
        return $this;
    }


    public function check($data, $scene = null)
    {
        foreach ($this->_rules as $ruleArr) {
            list($column, $rule, $msg, $sceneArr) = $ruleArr;
            if (in_array($scene, $sceneArr)) {
                if ($rule instanceof \Closure) {
                    $result = call_user_func($rule, $column, $data);
                } elseif (is_array($rule)) {
                    $key = key($rule);
                    $value = current($rule);
                    if (is_string()) {

                    }
                }
            }
        }
    }
}
