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
            //todo:文件上传优化得修改此地方
            return $value->size;
        } else {
            return mb_strlen((string)$value, 'UTF-8');
        }
    }

    /**
     * 验证数据长度是否在某个区间,适用于字符串/数组/上传的文件
     * @param mixed $value 数据
     * @param array $size 区间
     * @return bool
     */
    protected function validateSize($value, $size = [])
    {
        if (isset($size[0]) && isset($size[1])) {
            list($min, $max) = $size;
            if (is_array($value)) {
                $value = count($value);
            } elseif ($value instanceof \lying\upload\UploadFile) {
                //todo:文件上传优化得修改此地方
                $value = $value->size;
            } else {
                $value = mb_strlen((string)$value, 'UTF-8');
            }
            return $value >= $min && $value <= $max;
        }
        return false;
    }


    /**
     * 验证数据是否大于某个数
     * @param mixed $value 数据
     * @param mixed $min
     * @return bool
     * todo:
     */
    public function validateMin($value, $min = null)
    {
        return isset($min) && $value >= $min;
    }

    /**
     * 验证数据是否小于某个数
     * @param mixed $value 数据
     * @param mixed $max
     * @return bool
     * todo:
     */
    public function validateMax($value, $max)
    {
        return isset($max) && $value <= $max;
    }


    public function validateConfirm($value, $columns)
    {

    }

    public function validateRegex($value, $regex = null)
    {

    }

    /**
     * 验证字段是否为['yes', 'on', '1', 1, true, 'true']
     * @param mixed $value 数据
     * @param mixed $only 如果设置了此字段,则仅当字段等于此值才返回true
     * @return bool
     */
    public function validateAccepted($value, $only = null)
    {
        return isset($only) ? $value === $only : in_array($value, ['yes', 'on', '1', 1, true, 'true'], true);
    }


}
