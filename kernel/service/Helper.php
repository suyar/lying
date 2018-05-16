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
     * 返回一个变量的字符串表示,其返回的表示是合法的 PHP 代码
     * @param mixed $var 要导出的变量
     * @return string 返回变量的字符串表示
     */
    public function export($var)
    {
        return $this->exportInternal($var, 0);
    }

    /**
     * 递归返回一个变量的字符串表示,其返回的表示是合法的 PHP 代码
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
                    $spaces = str_repeat(' ', $level * 4);
                    $tmp = "[";
                    foreach ($var as $key => $val) {
                        $tmp .= "\n" . $spaces . '    ';
                        $tmp .= $this->exportInternal($key, 0) . ' => ' . $this->exportInternal($val, $level + 1) . ',';
                    }
                    $tmp .= "\n" . $spaces . ']';
                    return $tmp;
                }
            case 'object':
                if ($var instanceof \Closure) {
                    return $this->exportClosure($var);
                } else {
                    try {
                        return 'unserialize(' . var_export(serialize($var), true) . ')';
                    } catch (\Exception $e) {
                        if ($var instanceof \IteratorAggregate) {
                            $varAsArray = [];
                            foreach ($var as $key => $value) {
                                $varAsArray[$key] = $value;
                            }
                            return $this->exportInternal($varAsArray, $level);
                        } elseif ('__PHP_Incomplete_Class' !== get_class($var) && method_exists($var, '__toString')) {
                            return var_export($var->__toString(), true);
                        } else {
                            return var_export($this->dump($var), true);
                        }
                    }
                }
            default:
                return var_export($var, true);
        }
    }

    /**
     * 导出匿名函数的字符串表示
     * @param \Closure $closure 匿名函数实例
     * @return string 返回匿名函数的字符串表示
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
     * 返回变量的相关信息
     * @param mixed $var 要打印的变量
     * @param bool $highlight 是否高亮代码
     * @return string 返回要打印的变量
     */
    public function dump($var, $highlight = false)
    {
        $output = $this->dumpInternal($var, 0);
        if ($highlight) {
            $result = highlight_string("<?php\n" . $output, true);
            $output = preg_replace('/&lt;\\?php<br \\/>/', '', $result, 1);
        }
        return $output;
    }

    /**
     * 递归返回变量打印信息
     * @param mixed $var 要打印的变量
     * @param int $level 变量层级
     * @param array $objects 相同的对象
     * @return string 返回打印的字符串
     * @throws \Exception __debuginfo返回非数组的时候抛出异常
     */
    private function dumpInternal($var, $level, &$objects = [])
    {
        switch (gettype($var)) {
            case 'boolean':
                return $var ? 'true' : 'false';
            case 'integer':
                return "$var";
            case 'double':
                return "$var";
            case 'string':
                return "'" . addslashes($var) . "'";
            case 'resource':
                return '{resource}';
            case 'NULL':
                return 'null';
            case 'unknown type':
                return '{unknown}';
            case 'array':
                if (empty($var)) {
                    return '[]';
                } else {
                    $spaces = str_repeat(' ', $level * 4);
                    $tmp = '[';
                    foreach ($var as $key => $val) {
                        $tmp .= "\n" . $spaces . '    ';
                        $tmp .= $this->dumpInternal($key, 0, $objects) . ' => ' . $this->dumpInternal($val, $level + 1, $objects);
                    }
                    $tmp .= "\n" . $spaces . ']';
                    return $tmp;
                }
            case 'object':
                if (($id = array_search($var, $objects, true)) !== false) {
                    return get_class($var) . '#' . ($id + 1) . '(...)';
                } else {
                    $id = array_push($objects, $var);
                    $className = get_class($var);
                    $spaces = str_repeat(' ', $level * 4);
                    $tmp = "$className#$id\n" . $spaces . '(';
                    if ('__PHP_Incomplete_Class' !== get_class($var) && method_exists($var, '__debugInfo')) {
                        $dumpValues = $var->__debugInfo();
                        if (!is_array($dumpValues)) {
                            throw new \Exception('__debuginfo() must return an array');
                        }
                    } else {
                        $dumpValues = (array) $var;
                    }
                    foreach ($dumpValues as $key => $value) {
                        $keyDisplay = strtr(trim($key), "\0", ':');
                        $tmp .= "\n" . $spaces . "    [$keyDisplay] => ";
                        $tmp .= $this->dumpInternal($value, $level + 1, $objects);
                    }
                    $tmp .= "\n" . $spaces . ')';
                    return $tmp;
                }
        }
        return '';
    }
}
