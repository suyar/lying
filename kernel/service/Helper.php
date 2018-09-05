<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\service;

/**
 * Class Helper
 * @package lying\service
 */
class Helper
{
    /**
     * 创建文件夹
     * @param string $dir 文件夹
     * @param int $mode 权限,默认0775
     * @param bool $recursive 是否递归创建,默认是
     * @return bool 创建成功返回true,失败返回false
     */
    public function mkdir($dir, $mode = 0775, $recursive = true)
    {
        if (!is_dir($dir)) {
            @mkdir($dir, $mode, $recursive);
            @chmod($dir, $mode);
        }
        return is_dir($dir);
    }

    /**
     * 返回一个变量的字符串表示
     * @param mixed $var 要导出的变量
     * @return string 返回变量的字符串表示
     */
    public function export($var)
    {
        return $this->exportInternal($var, 0);
    }

    /**
     * 递归返回一个变量的字符串表示
     * @param mixed $var 要导出的变量
     * @param int $level 层级
     * @return string 返回变量的字符串表示
     */
    private function exportInternal($var, $level)
    {
        switch (gettype($var)) {
            case 'NULL':
                return 'null';
            case 'array':
                if (empty($var)) {
                    return '[]';
                } else {
                    $keys = array_keys($var);
                    $showKeys = ($keys !== range(0, count($var) - 1));
                    $spaces = str_repeat(' ', $level * 4);
                    $tmp = '[';
                    foreach ($var as $key => $val) {
                        $tmp .= "\n" . $spaces . '    ';
                        if ($showKeys) {
                            $tmp .= $this->exportInternal($key, 0) . ' => ';
                        }
                        $tmp .= $this->exportInternal($val, $level + 1) . ',';
                    }
                    $tmp .= "\n" . $spaces . ']';
                    return $tmp;
                }
            case 'object':
                try {
                    if ($var instanceof \Closure) {
                        return $this->exportClosure($var);
                    } else {
                        return 'unserialize(' . var_export(serialize($var), true) . ')';
                    }
                } catch (\Exception $e) {
                    return var_export($var, true);
                }
            default:
                return var_export($var, true);
        }
    }

    /**
     * 导出匿名函数的实例
     * @param \Closure $closure 匿名函数实例
     * @return string 返回匿名函数的实例
     * @throws \ReflectionException 反射出错抛出异常
     */
    private function exportClosure(\Closure $closure)
    {
        $reflection = new \ReflectionFunction($closure);

        $fileName = $reflection->getFileName();
        $start = $reflection->getStartLine();
        $end = $reflection->getEndLine();

        if ($fileName === false || $start === false || $end === false) {
            return 'function() {/* Error: unable to determine Closure source */}';
        }

        --$start;

        $source = implode("\n", array_slice(file($fileName), $start, $end - $start));
        $tokens = token_get_all('<?php ' . $source);
        array_shift($tokens);

        $closureTokens = [];
        $pendingParenthesisCount = 0;
        foreach ($tokens as $token) {
            if (isset($token[0]) && $token[0] === T_FUNCTION) {
                $closureTokens[] = $token[1];
                continue;
            }
            if ($closureTokens !== []) {
                $closureTokens[] = isset($token[1]) ? $token[1] : $token;
                if ($token === '}') {
                    $pendingParenthesisCount--;
                    if ($pendingParenthesisCount === 0) {
                        break;
                    }
                } elseif ($token === '{') {
                    $pendingParenthesisCount++;
                }
            }
        }

        return implode('', $closureTokens);
    }

    /**
     * 分页
     * @param int $total 总条数
     * @param int $page 页码
     * @param int $limit 每页显示条数
     * @return Pagination
     */
    public function paging($total, $page, $limit)
    {
        return new Pagination($total, $page, $limit);
    }

    /**
     * CURL进行HTTP GET请求
     * @param string $url 请求的URL
     * @param array $options 额外的CURL选项
     * @param string $curlError CURL错误信息
     * @param array $curlInfo CURL请求信息
     * @return mixed
     */
    public function httpGet($url, $options = [], &$curlError = '', &$curlInfo = [])
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, $options + [
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $res = curl_exec($ch);
        $curlError = curl_error($ch);
        $curlInfo = curl_getinfo($ch);
        curl_close($ch);
        return $res;
    }

    /**
     * CURL进行HTTP POST请求
     * @param string $url 请求的URL
     * @param mixed $data POST的数据
     * @param array $options 额外的CURL选项
     * @param string $curlError CURL错误信息
     * @param array $curlInfo CURL请求信息
     * @return mixed
     */
    public function httpPost($url, $data, $options = [], &$curlError = '', &$curlInfo = [])
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, $options + [
            CURLOPT_POST => true,
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SAFE_UPLOAD => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $res = curl_exec($ch);
        $curlError = curl_error($ch);
        $curlInfo = curl_getinfo($ch);
        curl_close($ch);
        return $res;
    }

    /**
     * 数组取值,支持点分割的键,为了避免歧义,数组的键不要有`.`
     * @param array $data 要取值的数组
     * @param string $key 要取的键,如果键为null,则返回整个数组
     * @param mixed $default 默认值
     * @param bool $exists 引用返回键是否存在
     * @return mixed
     */
    public function arrGetter(array $data, $key, $default = null, &$exists = null)
    {
        if ($key === null) {
            return $data;
        }

        foreach (explode('.', $key) as $k) {
            if (is_array($data) && array_key_exists($k, $data)) {
                $data = $data[$k];
            } else {
                $exists = false;
                return $default;
            }
        }

        $exists = true;
        return $data;
    }

    /**
     * 数组赋值,支持点分割的键,为了避免歧义,数组的键不要有`.`
     * @param array $data 要赋值的数组
     * @param string $key 赋值的键,如果为null,就把整个数组改变为$value
     * @param mixed $value 要设置的值
     * @return array 返回最后一维数组
     */
    public function arrSetter(array &$data, $key, $value)
    {
        if ($key === null) {
            return $data = $value;
        }

        $keys = explode('.', $key);
        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (!isset($data[$key]) || !is_array($data[$key])) {
                $data[$key] = [];
            }
            $data = &$data[$key];
        }
        $data[array_shift($keys)] = $value;
        return $data;
    }

    /**
     * 数组删除某个键,为了避免歧义,数组的键不要有`.`
     * @param array $data 要操作的数组
     * @param string $key 要删除的键
     */
    public function arrUnset(array &$data, $key)
    {
        $keys = explode('.', $key);
        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (isset($data[$key]) && is_array($data[$key])) {
                $data = &$data[$key];
            } else {
                return;
            }
        }
        unset($data[array_shift($keys)]);
    }
}
