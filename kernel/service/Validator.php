<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\service;

/**
 * Class Validator
 * @package lying\service
 */
class Validator extends Service
{
    private $_rules = [];

    private $_data;

    private $_rule;

    private $_column;

    /**
     * @inheritdoc
     */
    protected function init()
    {
        parent::init();
        static::rules($this);
    }

    /**
     * 预定义规则
     * @param Validator $validator
     */
    protected static function rules(Validator $validator)
    {

    }

    /**
     * 添加校验规则
     * @param string $column 字段名
     * @param string|array|callable $rules 规则
     * @param string|array $message 错误信息
     * @param string $onscene 校验场景
     * @return $this
     */
    public function rule($column, $rules, $message, $onscene = '')
    {
        foreach ((array)$onscene as $scene) {
            $this->_rules[$scene . '->' . $column] = [$column, $rules, $message, $scene];
        }
        return $this;
    }

    /**
     * 判断当前字段是否存在某些规则,只要有一个匹配就返回true
     * @param string|array $rules
     * @return bool
     */
    protected function hasRule($rules)
    {
        foreach ((array)$rules as $rule) {
            if (array_key_exists($rule, $this->_rule)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 获取验证规则的值
     * @param string $rule
     * @param mixed $value
     * @return bool
     */
    protected function getRule($rule, &$value)
    {
        if ($this->hasRule($rule)) {
            $value = $this->_rule[$rule];
            return true;
        }
        return false;
    }

    public function verify(array $data, $onscene = '')
    {
        $helper = \Lying::$maker->helper;
        $this->_data = $data;
        foreach ($this->_rules as $item) {
            list($this->_column, $this->_rule, $message, $scene) = $item;
            if ($scene == $onscene) {
                $value = $helper->arrGetter($this->_data, $this->_column, null, $exists);
                if (is_callable($this->_rule)) {
                    $result = call_user_func_array($this->_rule, [&$value, $this->_column, $this->_data, $exists]);
                    $result && $helper->arrSetter($this->_data, $this->_column, $value);
                } else {
                    $rules = [];
                    foreach ((array)$this->_rule as $k => $rule) {
                        if (is_int($k) && is_string($rule) && method_exists($this, 'valid' . ucfirst($rule))) {
                            $rules[$rule] = null;
                        } elseif (method_exists($this, 'valid' . ucfirst($k)) || in_array($k, ['filter', 'default'])) {
                            $rules[$k] = $rule;
                        }
                    }
                    $this->_rule = $rules;

                    //过滤器
                    if ($this->getRule('filter', $filter) && is_callable($filter)) {
                        $value = $filter($value);
                    }

                    if (!$exists || $value === '' || $value === null) {

                    }







                }
            }
        }
    }
}
