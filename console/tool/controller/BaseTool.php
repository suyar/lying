<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace console\tool\controller;

use lying\service\Controller;

/**
 * Class BaseTool
 * @package console\tool\controller
 */
class BaseTool extends Controller
{
    /**
     * 标准输入
     * @return string
     */
    protected function stdIn()
    {
        return trim(fgets(STDIN));
    }

    /**
     * 标准输出
     * @param string $tips 输出内容
     * @param bool $br 是否换行,默认true
     */
    protected function stdOut($tips, $br = true)
    {
        fwrite(STDOUT, $tips . ($br ? "\n" : ''));
    }

    /**
     * 标准错误输出
     * @param string $err 错误信息
     */
    protected function stdErr($err)
    {
        fwrite(STDERR, 'ERROR: ' . $err);
        exit;
    }
}
