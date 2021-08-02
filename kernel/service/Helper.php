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
}
