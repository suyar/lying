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
     * @var array 校验的数据
     */
    private $_data = [];

    /**
     * @var string 当前正在校验的字段
     */
    private $_column;

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


    protected function setError($column, $message, $ruleName = null)
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
        $this->_data = $data;

        $helper = \Lying::$maker->helper;

        foreach ($this->_rules as $item) {
            list($this->_column, $rule, $message, $onscene) = $item;
            if ($onscene == $scene) {
                $value = $helper->arrGetter($this->_data, $this->_column, null, $exists);
                //自定义验证
                if (is_callable($rule)) {
                    $result = call_user_func_array($rule, [&$value, $this->_column, $this->_data, $exists]);
                    if ($result === true) {
                        //如果函数使用引用传值,则应该要改变原始数组的值
                        $helper->arrSetter($this->_data, $this->_column, $value);
                    } else {
                        $this->setError($this->_column, $message);
                    }
                } else {
                    $rule = (array)$rule;

                    //设置了过滤器
                    if (array_key_exists('filter', $rule)) {
                        $filter = $rule['filter'];
                        if (is_callable($filter)) {
                            $value = call_user_func_array($filter, [$value]);
                            $helper->arrSetter($this->_data, $this->_column, $value);
                        }
                        unset($rule['filter']);
                    }

                    //字段必须
                    if (array_key_exists('require', $rule) || ($key = array_search('require', $rule)) !== false) {
                        if (isset($key)) {

                        } else {

                        }


                    }



                }

                if (is_string($rule)) {
                    $method = 'validate' . ucfirst($rule);
                    if (method_exists($this, $method)) {
                        $result = call_user_func_array([$this, $method], [$value]);
                        if ($result !== true) {
                            $this->setError($this->_column, $message, $rule);
                        }
                    }
                } elseif (is_array($rule)) {


                    //设置了默认值
                    $default = null;
                    if (array_key_exists('default', $rule)) {
                        $default = $rule['default'];
                        unset($rule['default']);
                    }

                    //字段必须
                    if (isset($rule['require'])) {
                        if (is_callable($rule['require'])) {
                            $result = call_user_func_array($rule['require'], [$value, $this->_column, $this->_data]);
                        } else {
                            $result = false;
                        }

                        if ($result === true) {
                            $helper->arrSetter($this->_data, $this->_column, $value);
                        } else {
                            $this->setError($this->_column, $message, $rule);
                        }

                        unset($rule['require']);
                    } elseif (($key = array_search('require', $rule)) !== false) {
                        $result = $this->validateRequire($value, $default);

                        if ($result === true) {
                            $helper->arrSetter($this->_data, $this->_column, $value);
                        } else {
                            $this->setError($this->_column, $message, $rule);
                        }

                        unset($rule[$key]);
                    }


                    foreach ($rule as $name => $r) {
                        if (is_string($name)) {
                            $method = $method = 'validate' . ucfirst($name);


                            $result = $this->$method($value, $r);

                        } else {

                        }
                    }

                }


            }

        }
    }


    protected function validateRequire(&$value, $default = null)
    {

    }

    /**
     * 验证是否为整型
     * @param mixed $value 数据
     * @return bool
     */
    protected function validateInt($value)
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * 验证是否为小数
     * @param mixed $value 数据
     * @return bool
     */
    protected function validateFloat($value)
    {
        return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
    }

    /**
     * 校验数据是否为字符串
     * @param mixed $value 数据
     * @return bool
     */
    protected function validateString($value)
    {
        return is_string($value);
    }

    /**
     * 验证数据是否为纯数字
     * @param mixed $value 数据
     * @return bool
     */
    protected function validateNum($value)
    {
        return is_numeric($value);
    }

    /**
     * 验证数据是否为邮箱
     * @param mixed $value 数据
     * @return bool
     */
    protected function validateEmail($value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * 验证数据是否为数组
     * @param mixed $value 数据
     * @return bool
     */
    protected function validateArray($value)
    {
        return is_array($value);
    }

    /**
     * 验证数据是否为布尔型[true, false, 0, 1, '0', '1']被判定为布尔型
     * @param mixed $value 数据
     * @param bool $booleanOnly 如果此值为true,则只有[true, false]被判定为布尔型
     * @return bool
     */
    protected function validateBool($value, $booleanOnly = false)
    {
        return in_array($value, $booleanOnly ? [true, false] : [true, false, 0, 1, '0', '1'], true);
    }

    /**
     * 验证字段是否为['yes', 'on', '1', 1, true, 'true']
     * @param mixed $value 数据
     * @param mixed $only 如果设置了此字段,则仅当字段等于此值才返回true
     * @return bool
     */
    protected function validateAccepted($value, $only = null)
    {
        return isset($only) ? $value === $only : in_array($value, ['yes', 'on', '1', 1, true, 'true'], true);
    }

    /**
     * 验证数据是否是一个有效的日期
     * @param mixed $value 数据
     * @return bool
     */
    protected function validateDate($value)
    {
        return (is_string($value) || is_numeric($value)) && strtotime($value) !== false;
    }

    /**
     * 验证数据是否符合指定的日期格式
     * @param mixed $value 数据
     * @param mixed $format 数据格式
     * @return bool
     */
    protected function validateDateFormat($value, $format = null)
    {
        if (is_string($value) || is_numeric($value) || is_string($format)) {
            $info = date_parse_from_format($format, $value);
            return 0 == $info['warning_count'] && 0 == $info['error_count'];
        }
        return false;
    }

    /**
     * 验证数据是否为纯字母
     * @param mixed $value 数据
     * @return bool
     */
    protected function validateAlpha($value)
    {
        return ctype_alpha($value);
    }

    /**
     * 验证数据是否为字母和数字的组合
     * @param mixed $value
     * @return bool
     */
    protected function validateAlnum($value)
    {
        return ctype_alnum($value);
    }

    /**
     * 验证数据是否为字母和数字以及[_-]
     * @param mixed $value 数据
     * @return bool
     */
    protected function validateAldash($value)
    {
        return (is_string($value) || is_numeric($value)) && preg_match('/^[A-Za-z0-9\-\_]+$/', $value) > 0;
    }

    /**
     * 验证数据是否为URL
     * @param mixed $value 数据
     * @return bool
     */
    protected function validateUrl($value)
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * 检查是否为有效的URL
     * @param string $value 数据,如果带http/https会自动解析出host
     * @param string|array $type 支持['A', 'MX', 'NS', 'SOA', 'PTR', 'CNAME', 'AAAA', 'A6', 'SRV', 'NAPTR', 'TXT', 'ANY'],默认['A', 'AAAA']
     * @return bool
     */
    protected function validateActiveUrl($value, $type = 'A')
    {
        if (is_string($value)) {
            $host = parse_url($value, PHP_URL_HOST) ?: $value;
            foreach ((array)$type as $t) {
                if (in_array($t, ['A', 'MX', 'NS', 'SOA', 'PTR', 'CNAME', 'AAAA', 'A6', 'SRV', 'NAPTR', 'TXT', 'ANY'])) {
                    if (!checkdnsrr($host, $t)) {
                        return false;
                    }
                }
            }
            return true;
        }
        return false;
    }

    /**
     * 验证数据是否为IP地址
     * @param mixed $value 数据
     * @return bool
     */
    protected function validateIp($value)
    {
        return filter_var($value, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * 验证数据是否为IP4地址
     * @param mixed $value 数据
     * @return bool
     */
    protected function validateIpv4($value)
    {
        return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
    }

    /**
     * 验证数据是否为IP6地址
     * @param mixed $value 数据
     * @return bool
     */
    protected function validateIpv6($value)
    {
        return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }

    /**
     * 验证数据是否在某个数组里面
     * @param mixed $value 数据
     * @param array $array
     * @return bool
     */
    protected function validateIn($value, $array = [])
    {
        return is_array($array) && in_array($value, $array);
    }

    /**
     * 验证数据是否不在某个数组里面
     * @param mixed $value 数据
     * @param array $array
     * @return bool
     */
    protected function validateNotIn($value, $array = [])
    {
        return !is_array($array) || !in_array($value, $array);
    }

    /**
     * 验证数据是否在某个区间,仅用于数字验证
     * @param mixed $value 数据
     * @param array $between 区间:[10,20]
     * @return bool
     */
    protected function validateBetween($value, $between = [])
    {
        if (isset($between[0]) && isset($between[1])) {
            list($min, $max) = $between;
            return $value >= $min && $value <= $max;
        }
        return false;
    }

    /**
     * 验证数据是否不在某个区间,仅用于数字验证
     * @param mixed $value 数据
     * @param array $between 区间:[10,20]
     * @return bool
     */
    protected function validateNotBetween($value, $between = [])
    {
        if (isset($between[0]) && isset($between[1])) {
            list($min, $max) = $between;
            return $value <= $min || $value >= $max;
        }
        return false;
    }

    /**
     * 验证数据是否等于某个值
     * @param mixed $value 数据
     * @param mixed $eq
     * @return bool
     */
    protected function validateEq($value, $eq = null)
    {
        return $value == $eq;
    }

    /**
     * 验证数据是否大于某个值,仅用于数字验证
     * @param mixed $value 数据
     * @param mixed $gt
     * @return bool
     */
    protected function validateGt($value, $gt = null)
    {
        return isset($gt) && $value > $gt;
    }

    /**
     * 验证数据是否大等于某个值,仅用于数字验证
     * @param mixed $value 数据
     * @param mixed $egt
     * @return bool
     */
    protected function validateEgt($value, $egt = null)
    {
        return isset($egt) && $value > $egt;
    }

    /**
     * 验证数据是否小于某个值,仅用于数字验证
     * @param mixed $value 数据
     * @param mixed $lt
     * @return bool
     */
    protected function validateLt($value, $lt = null)
    {
        return isset($lt) && $value > $lt;
    }

    /**
     * 验证数据是否小等于某个值,仅用于数字验证
     * @param mixed $value 数据
     * @param mixed $elt
     * @return bool
     */
    protected function validateElt($value, $elt = null)
    {
        return isset($elt) && $value > $elt;
    }

    /**
     * 获取数据长度
     * @param mixed $value 数据
     * @return int
     */
    protected function getSize($value)
    {
        if (is_array($value)) {
            return count($value);
        } elseif ($value instanceof \lying\upload\UploadFile) {
            //todo:文件上传优化要修改此地方
            return $value->size;
        } else {
            return mb_strlen(strval($value), 'UTF-8');
        }
    }

    /**
     * 验证数据长度是否在某个区间,适用于字符串/数组/上传的文件
     * @param mixed $value 数据
     * @param array $length 区间
     * @return bool
     */
    protected function validateLength($value, $length = [])
    {
        if (isset($length[0]) && isset($length[1])) {
            list($min, $max) = $length;
            $value = $this->getSize($value);
            return $value >= $min && $value <= $max;
        }
        return false;
    }

    /**
     * 验证数据是否大于某个数,适用于字符串/数组/上传的文件
     * @param mixed $value 数据
     * @param mixed $min 最小长度
     * @return bool
     */
    protected function validateMin($value, $min = null)
    {
        return isset($min) && $this->getSize($value) >= $min;
    }

    /**
     * 验证数据是否小于某个数,适用于字符串/数组/上传的文件
     * @param mixed $value 数据
     * @param mixed $max 最大长度
     * @return bool
     */
    protected function validateMax($value, $max)
    {
        return isset($max) && $this->getSize($value) <= $max;
    }

    /**
     * 校验数据是否和某个字段一致
     * @param mixed $value 数据
     * @param string $column 字段名
     * @return bool
     */
    protected function validateConfirm($value, $column = null)
    {
        $other = \Lying::$maker->helper->arrGetter($this->_data, $column);
        return $value === $other;
    }

    /**
     * 校验数据是否和某个字段不一致
     * @param mixed $value 数据
     * @param string $column 字段名
     * @return bool
     */
    protected function validateDiff($value, $column = null)
    {
        $other = \Lying::$maker->helper->arrGetter($this->_data, $column);
        return $value !== $other;
    }

    /**
     * 校验数据是否符合某个正则
     * @param mixed $value 数据
     * @param string $regex 正则表达式
     * @return bool
     */
    protected function validateRegex($value, $regex = null)
    {
        if (isset($regex) && (is_string($value) || is_numeric($value))) {
            return preg_match($regex, $value) > 0;
        }
        return false;
    }

    /**
     * 校验数据是否不符合某个正则
     * @param mixed $value 数据
     * @param string $regex 正则表达式
     * @return bool
     */
    protected function validateNotRegex($value, $regex = null)
    {
        if (isset($regex) && (is_string($value) || is_numeric($value))) {
            return preg_match($regex, $value) < 1;
        }
        return false;
    }

    /**
     * 校验数据是否为一个可解析的json
     * @param mixed $value 数据
     * @return bool
     */
    protected function validateJson($value)
    {
        if (is_scalar($value) || method_exists($value, '__toString')) {
            json_decode($value);
            return json_last_error() === JSON_ERROR_NONE;
        }
        return false;
    }


    protected function validateFile()
    {

    }

    protected function validateExt()
    {

    }

    protected function validateMime()
    {

    }
}
