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
     * @inheritdoc
     */
    final protected function init()
    {
        parent::init();
        self::rules($this);
    }

    protected static function rules(Validate $validate)
    {
        $validate->addRule('name', '', '', '')
            ->addRule('password', '', '', '');
    }


    /**
     * 添加校验规则
     * @param string $column 字段名
     * @param string|array $rule 规则
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

            }
        }
    }
}
