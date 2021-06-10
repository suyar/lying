<?php
namespace lying\service;

/**
 * Class Pagination
 * @package lying\service
 *
 * @property int $total 总记录数
 * @property int $pages 总页数
 * @property int $page 当前页码
 * @property int $offset 偏移量
 * @property int $limit 每页显示条数
 */
class Pagination
{
    /**
     * @var int 总记录数
     */
    protected $total;

    /**
     * @var int 总页数
     */
    protected $pages;

    /**
     * @var int 当前页码
     */
    protected $page;

    /**
     * @var int 偏移量
     */
    protected $offset;

    /**
     * @var int 每页显示条数
     */
    protected $limit;

    /**
     * Pagination constructor.
     * @param int $total 总条数
     * @param int $page 页码
     * @param int $limit 每页显示条数
     */
    public function __construct($total, $page, $limit)
    {
        list($total, $page, $limit) = [intval($total), intval($page), intval($limit)];
        $this->total = $total > 0 ? $total : 0;
        $this->limit = $limit > 0 ? $limit : 20;
        $this->pages = ceil($this->total / $this->limit) ?: 1;
        $this->page = $page > 0 ? ($page > $this->pages ? $this->pages : $page) : 1;
        $this->offset = ($this->page - 1) * $this->limit;

    }

    /**
     * 获取分页HTML
     * @param callable $getUrl 拼接分页URL的回调函数，接受一个页码参数
     * @param int $groups 连续显示的分页数量
     * @return string 返回拼接后的URL
     */
    public function html(callable $getUrl, $groups = 5)
    {
        if ($this->pages > 1) {
            //分页HTML容器
            $html = ['<div>'];

            //计算组数量
            $groups = $groups > 0 ? ($groups > $this->pages ? $this->pages : $groups) : ($this->pages > 5 ? 5 : $this->pages);
            //计算当前组
            $index = ceil(($this->page + ($groups > 1 ? 1 : 0)) / $groups);

            //显示首页
            if ($index > 1) {
                $html[] = '<a href="' . $getUrl(1) . '">首页</a>';
            }

            //显示上一页
            if ($this->page > 1) {
                $html[] = '<a href="' . $getUrl($this->page - 1) . '">上一页</a>';
            }

            //计算当前页码组的起始页
            $halve = floor(($groups - 1) / 2);
            if ($index > 1) {
                $spage = $this->page - $halve;
                $max = $this->page + ($groups - $halve - 1);
                $epage = $max > $this->pages ? $this->pages : $max;
            } else {
                $spage = 1;
                $epage = $groups;
            }

            //防止最后一组出现“不规定”的连续页码数
            if($epage - $spage < $groups - 1){
                $spage = $epage - $groups + 1;
            }

            //显示左侧分隔符
            if ($spage > 2) {
                $html[] = '<span>&#x2026;</span>';
            }

            //输出连续页码
            for (; $spage <= $epage; $spage ++) {
                if ($spage == $this->page) {
                    $html[] = '<span>' . $spage . '</span>';
                } else {
                    $html[] = '<a href="' . $getUrl($spage) . '">' . $spage . '</a>';
                }
            }

            //显示右侧分隔符
            if ($this->pages > $groups && $this->pages > ($epage + 1)) {
                $html[] = '<span>&#x2026;</span>';
            }

            //显示下一页
            if ($this->page < $this->pages) {
                $html[] = '<a href="' . $getUrl($this->page + 1) . '">下一页</a>';
            }

            //输出末页
            if ($this->pages > $groups && $this->pages > $epage) {
                $html[] = '<a href="' . $getUrl($this->pages) . '">末页</a>';
            }

            $html[] = '</div>';

            return implode($html);
        }
        return '';
    }

    /**
     * 获取私有属性的值
     * @param string $name 属性
     * @return mixed
     */
    public function __get($name)
    {
        return $this->$name;
    }

    /**
     * 设置未知属性的值报错
     * @param string $name 属性名
     * @param mixed $value 属性值
     * @throws \Exception
     */
    public function __set($name, $value)
    {
        throw new \Exception("Unable to reset property value: {$name}.");
    }
}
