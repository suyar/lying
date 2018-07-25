<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\view;

use lying\service\Service;

/**
 * Class Render
 * @package lying\view
 */
abstract class Render extends Service
{
    /**
     * 模板渲染
     * @param string $file 要渲染的模板文件
     * @param array $params 渲染的参数
     * @return string 返回渲染结果
     */
    abstract public function render($file, $params);
}
