<?php
/**
 * @author carolkey <me@suyaqi.cn>
 * @link https://github.com/carolkey/lying
 * @copyright 2017 Lying
 * @license MIT
 */

namespace lying\service;

/**
 * Class Lock
 * @package lying\service
 * @since 2.0
 */
class Lock extends Service
{
    /**
     * @var string 锁文件存储目录
     */
    protected $dir;

    /**
     * 初始化锁文件目录
     */
    protected function init()
    {
        empty($this->dir) && ($this->dir = DIR_RUNTIME . DS . 'lock');
        !is_dir($this->dir) && @mkdir($this->dir, 0777, true);
    }

    /**
     * 执行锁定代码
     * @param string $name 锁名
     * @param integer $type 锁类型
     * LOCK_SH 共享锁
     * LOCK_EX 独占锁
     * LOCK_NB 非阻塞,用法[LOCK_EX|LOCK_NB]
     * @param callable $call 锁定的代码
     * @return boolean|mixed 返回代码执行结果,锁定失败返回false
     */
    public function call($name, $type, callable $call)
    {
        if ($fp = @fopen($this->dir . DS . md5($name), 'w')) {
            if (@flock($fp, $type)) {
                $res = call_user_func($call);
                @flock($fp, LOCK_UN);
                @fclose($fp);
                return $res;
            } else {
                @fclose($fp);
            }
        }
        return false;
    }

    /**
     * 删除锁名对应的锁文件
     * @param string $name 锁名
     * @return boolean 成功返回true,失败返回false
     */
    public function removeFile($name)
    {
        return @unlink($this->dir . DS . md5($name));
    }
}
