<?php

/**
 * 文章内链替换
 * 替换规则:相同的链接只替换一次,权重高的先替换,权重一样的按照词长优先,词长一样的按照ID降序
 * @param string $content 文章内容
 * @param array $links 内链词数组[$word=>$link],权重高的在前面,此参数不写为还原内链
 * @return string 返回替换后的内容
 */
function articleInnerLink($content, $links = array())
{
    //还原内链
    $content = preg_replace('/<a class=\"epwk_inner_link\"[^>]+>([^<]+)<\/a>/', '$1', $content);

    //内链替换
    if (is_array($links) && !empty($links)) {

        //匿名函数,相同关键字仅替换一次
        $func_replace_once = function ($search, $replace, $subject, &$isReplaced) {
            $pos = strpos($subject, $search);
            if ($pos === false) {
                $isReplaced = false;
                return $subject;
            } else {
                $isReplaced = true;
                return substr_replace($subject, $replace, $pos, strlen($search));
            }
        };

        //最终替换的数组
        $finalReplace = array();

        //已经替换过的链接数组
        $exsist = array();

        //非HTML匹配规则
        $reg = '/(?:<[^>]*>)?([^<]*)(?:<[^>]*>)?/';

        //正则匹配替换
        $content = preg_replace_callback($reg, function ($matches) use ($links, &$exsist, $func_replace_once, &$finalReplace) {
            if (trim($matches[1])) {
                $tmp = $matches[1];
                foreach ($links as $word => $link) {
                    if (!in_array($link, $exsist)) {
                        $isReplaced = false;
                        $replaceStr = '<a class="epwk_inner_link" href="' . $link . '" target="_blank">' . $word . '</a>';
                        $replaceStrMd5 = '[' . md5($replaceStr) . ']';
                        $tmp = $func_replace_once($word, $replaceStrMd5, $tmp, $isReplaced);
                        if ($isReplaced) {
                            $exsist[] = $link;
                            $finalReplace[$replaceStrMd5] = $replaceStr;
                        }
                    }
                }
                return str_replace($matches[1], $tmp, $matches[0]);
            }
            return $matches[0];
        }, $content);

        //进行最终的替换
        if ($finalReplace) {
            $keys = $vals = array();
            foreach ($finalReplace as $k => $v) {
                $keys[] = $k;
                $vals[] = $v;
            }
            $content = str_replace($keys, $vals, $content);
        }

    }

    return $content;
}

//文章内容
$content = <<<EOL
<div>
    <b>今天天气不错，来点智能家居，智能家居真是不错</b>
</div>
<div>
    <b>今天天气不错，来点智能家居，智能家居真是不错</b>
</div>
EOL;

//内链
$links = [
    '智能家居' => 'zhinengjiaju',
    '智能' => 'zhineng',
    '家居' => 'jiaju',
];

$stime = microtime(true);
$content = articleInnerLink($content, $links);
echo $content;

