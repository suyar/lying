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
     * @param string|array $msg 错误提示
     * @param string|array $scene 字段校验场景
     * @return $this
     */
    public function rule($column, $rule, $msg, $scene = null)
    {
        foreach ((array)$scene as $s) {
            $this->_rules[$column . '.' . $s] = [$column, $rule, $msg, $s];
        }
        return $this;
    }

    /**
     * 获取数组的键(支持引用返回,引用返回的变量修改时,也会改变原来数组的内容)
     * @param array $data 要检索的数组
     * @param string $key 要检索的键,支持`.`分隔的键
     * @param mixed $default 键不存在返回的默认值
     * @param bool $exists 引用传递键是否存在
     * @return mixed 如果检索到相关的键,则返回内容,否则返回默认值
     */
    protected function &getValue(array &$data, $key, $default = null, &$exists = true)
    {
        foreach (explode('.', $key) as $k) {
            if (array_key_exists($k, $data)) {
                $data = &$data[$k];
            } else {
                $exists = false;
                return $default;
            }
        }

        return $data;
    }


    public function check($data, $onscene = null)
    {
        foreach ($this->_rules as $item) {
            list($column, $rule, $msg, $scene) = $item;
            if ($onscene == $scene) {
                $value = $this->getValue($data, $column, null, $exists);
                if ($rule instanceof \Closure) {
                    $result = $rule($column, $value, $data);
                }

            }

        }
    }
}
