<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\view;

use lying\cache\FileCache;
use lying\service\Service;

/**
 * Class Template
 * @package lying\view
 */
class Template extends Service
{
    /**
     * @var string|FileCache
     */
    protected $cache;

    /**
     * @var View
     */
    protected $view;

    /**
     * @var array 原样显示的代码
     */
    private $_native = [];

    /**
     * @var array 加载的模板文件列表
     */
    private $_include = [];

    /**
     * @inheritdoc
     */
    protected function init()
    {
        parent::init();
        if ($this->cache) {
            $this->cache = \Lying::$maker->cache($this->cache);
        } else {
            $this->cache = \Lying::$maker->register('template', [
                'class' => 'lying\cache\FileCache',
                'dir' => DIR_RUNTIME . DS . 'compile',
                'gc' => 80,
                'suffix' => 'php',
                'serialize' => false,
            ])->cache('template');
        }
    }

    /**
     * 返回渲染后的模板
     * @param string $file 要渲染的文件
     * @param array $params 模板参数
     * @param string $viewPath 视图路径
     * @return string
     * @throws \Throwable
     */
    public function render($file, $params, $viewPath)
    {
        $cacheKey = $viewPath . $file;
        $cacheFile = $this->cache->cacheFile($cacheKey);

        if (!$this->cache->exists($cacheKey) || !$this->notModify($cacheFile)) {
            $this->compile($file, $viewPath);
        }

        return $this->view->renderPhp($cacheFile, $params);
    }

    /**
     * 检查原始模板文件是否没有被修改过
     * @param string $cacheFile 缓存的编译文件
     * @return bool 没有被修改过返回true,否则返回false
     */
    private function notModify($cacheFile)
    {
        if (is_file($cacheFile) && is_readable($cacheFile) && ($handle = fopen($cacheFile, 'r'))) {

            $firstLine = fgets($handle);

            fclose($handle);

            if ($firstLine && preg_match('/\/\*(.+)\*\//', $firstLine, $matches)) {
                foreach (unserialize($matches[1]) as $path => $time) {
                    //原始模板文件不存在或者被修改过
                    if (!is_file($path) || filemtime($path) > $time) {
                        return false;
                    }
                }
                return true;
            }
        }
        return false;
    }

    /**
     * 把windows换行符转换成linux换行符并去除多余空行
     * @param string $content
     */
    private function parseEol(&$content)
    {
        $content = preg_replace('/\r?\n(\r?\n)*[ |\t]*(\r?\n)*\r\n/s', "\n", $content);
    }

    /**
     * 移除注释块
     * @param string $content
     */
    private function parseComment(&$content)
    {
        $content = preg_replace('/\{\*(.*?)\*\}/s', '', $content);
    }

    /**
     * 解析/还原原样显示的内容
     * @param $content
     * @param bool $revert 是否还原,默认否
     */
    private function parseNative(&$content, $revert = false)
    {
        if ($revert) {
            $regex = '/<!--###native(\d)evitan###-->/';
            if (preg_match_all($regex, $content, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $content = str_replace($match[0], $this->_native[$match[1]], $content);
                }
                $this->_native = [];
            }
        } else {
            $regex = '/\{native\}(.*?)\{\/native\}/s';
            if (preg_match_all($regex, $content,$matches, PREG_SET_ORDER)) {
                $count = count($this->_native);
                foreach ($matches as $match) {
                    $this->_native[$count] = $match[1];
                    $content = str_replace($match[0], "<!--###native{$count}evitan###-->", $content);
                    $count++;
                }
            }
        }
    }

    /**
     * 解析引用的块
     * @param string $content
     * @return array 返回[0=>'search',1=>'replace']
     */
    private function parseBlock(&$content)
    {
        $regex = '/\{block[ \t]+name[ \t]*=[ \t]*([\'"])(\S+?)\1[ \t]*\}(.*?)\{\/block\}/s';
        $blocks = [];
        if (preg_match_all($regex, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $blocks[$match[2]] = [$match[0], $match[3]];
            }
        }
        return $blocks;
    }

    /**
     * 解析模板继承
     * @param string $content
     * @return string
     */
    private function parseExtend(&$content)
    {
        $regex = '/^\s*\{extend[ \t]+name[ \t]*=[ \t]*([\'"])(\S+?)\1[ \t]*\}\r?\n/';

        $blocks = [];

        $call = function ($tpl) use (&$regex, &$call, &$blocks, &$content) {

            $this->parseComment($tpl);
            $this->parseNative($tpl);

            if (preg_match($regex, $tpl, $matches)) {

                $parentFile = $this->view->resovePath($matches[2]);

                $this->_include[$parentFile] = filemtime($parentFile);

                $call(file_get_contents($parentFile));

                $subblocks = $this->parseBlock($tpl);

                foreach ($subblocks as $name => $subblock) {
                    if (isset($blocks[$name])) {
                        if (preg_match('/\{parent\}/', $subblock[1])) {
                            $subblock[1] = str_replace('{parent}', $blocks[$name][1], $subblock[1]);
                        }
                        $blocks[$name][1] = $subblock[1];
                    }
                }

            } else {
                $blocks = $this->parseBlock($tpl);
                $content = $tpl;
            }
        };

        $call($content);

        if ($blocks) {
            $search = $replace = [];
            foreach ($blocks as $block) {
                $search[] = $block[0];
                $replace[] = $block[1];
            }
            $content = str_replace($search, $replace, $content);
        }

        return $content;
    }

    /**
     * 解析模板引用
     * @param string $content
     * @throws \Exception
     */
    private function parseInclude(&$content)
    {
        $regex = '/\{include[ \t]+name[ \t]*=[ \t]*([\'"])(\S+?)\1[ \t]*\}/s';

        if (preg_match_all($regex, $content, $matches, PREG_SET_ORDER)) {

            foreach ($matches as $match) {

                $childFile = $this->view->resovePath($match[2]);

                $this->_include[$childFile] = filemtime($childFile);

                $childTpl = file_get_contents($childFile);

                $this->parseComment($childTpl);

                $this->parseNative($childTpl);

                $content = str_replace($match[0], $childTpl, $content);
            }
        }
    }

    /**
     * 编译普通标签
     * @param string $content
     */
    private function parseTags(&$content)
    {
        $pattern = [
            '/\{(\$\S+.*?)[ \t]*\}/',
            '/\{:(\S+.*?)[ \t]*\}/',
            '/\{if[ \t]+(\S+.*?)[ \t]*\}/',
            '/\{else[ \t]*if[ \t]+(\S+.*?)[ \t]*\}/',
            '/\{else\}/',
            '/\{\/if\}/',
            '/\{loop[ \t]+(\S+.*?)[ \t]+(\$[A-Za-z_]\w*?)[ \t]+(\$[A-Za-z_]\w*?)[ \t]*\}/',
            '/\{loop[ \t]+(\S+.*?)[ \t]+(\$[A-Za-z_]\w*?)[ \t]*\}/',
            '/\{\/loop\}/',
            '/\{for[ \t]+(.*?)[ \t]*;[ \t]*(.*?)[ \t]*;[ \t]*(.*?)[ \t]*\}/',
            '/\{\/for\}/',
            '/\{(break|continue)\}/',

            '/^(\s*\{)!(extend[ \t]+name[ \t]*=[ \t]*([\'"])\S+?\3[ \t]*\}\r?\n)/',
            '/(\{)!((?:native|break|continue)\})/',
            '/(\{)!(block[ \t]+name[ \t]*=[ \t]*([\'"])\S+?\3[ \t]*\})/',
            '/(\{)!(include[ \t]+name[ \t]*=[ \t]*([\'"])\S+?\3[ \t]*\})/',
            '/(\{)!(\$\S+.*?[ \t]*\})/',
            '/(\{)!(:\S+.*?[ \t]*\})/',
            '/(\{)!(if[ \t]+\S+.*?[ \t]*\})/',
            '/(\{)!(else[ \t]*if[ \t]+\S+.*?[ \t]*\})/',
            '/(\{)!(else\})/',
            '/(\{)!(loop[ \t]+\S+.*?[ \t]+\$[A-Za-z_]\w*?[ \t]+\$[A-Za-z_]\w*?[ \t]*\})/',
            '/(\{)!(loop[ \t]+\S+.*?[ \t]+\$[A-Za-z_]\w*?[ \t]*\})/',
            '/(\{)!(for[ \t]+.*?[ \t]*;[ \t]*.*?[ \t]*;[ \t]*.*?[ \t]*\})/',
            '/(\{)!(\/(?:loop|if|native|block|for)\})/',
        ];

        $replace = [
            '<?= htmlentities($1); ?>',
            '<?= $1; ?>',
            '<?php if ($1): ?>',
            '<?php elseif ($1): ?>',
            '<?php else: ?>',
            '<?php endif; ?>',
            '<?php foreach ($1 as $2 => $3): ?>',
            '<?php foreach ($1 as $2): ?>',
            '<?php endforeach; ?>',
            '<?php for ($1; $2; $3): ?>',
            '<?php endfor; ?>',
            '<?php $1; ?>',

            '$1$2',
            '$1$2',
            '$1$2',
            '$1$2',
            '$1$2',
            '$1$2',
            '$1$2',
            '$1$2',
            '$1$2',
            '$1$2',
            '$1$2',
            '$1$2',
            '$1$2',
        ];

        $content = preg_replace($pattern, $replace, $content);
    }

    /**
     * 编译html文件
     * @param string $file 目标文件
     * @param string $viewPath 视图路径
     * @throws \Exception
     */
    private function compile($file, $viewPath)
    {
        $this->_include[$file] = filemtime($file);

        $content = file_get_contents($file);

        $this->parseExtend($content);

        $this->parseInclude($content);

        $this->parseTags($content);

        $this->parseNative($content, true);

        $this->parseEol($content);

        $head = '<?php /*' . serialize($this->_include) . "*/ ?>\n";

        $this->cache->set($viewPath . $file, $head . $content);
    }
}
