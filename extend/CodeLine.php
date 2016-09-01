<?php
namespace extend;
/**
 *                    _ooOoo_
 *                   o8888888o
 *                   88" . "88
 *                   (| -_- |)
 *                   O\  =  /O
 *                ____/`---'\____
 *              .'  \\|     |//  `.
 *             /  \\|||  :  |||//  \
 *            /  _||||| -:- |||||-  \
 *            |   | \\\  -  /// |   |
 *            | \_|  ''\---/''  |   |
 *            \  .-\__  `-`  ___/-. /
 *          ___`. .'  /--.--\  `. . __
 *       ."" '<  `.___\_<|>_/___.'  >'"".
 *      | | :  `- \`.;`\ _ /`;.`/ - ` : | |
 *      \  \ `-.   \_ __\ /__ _/   .-` /  /
 * ======`-.____`-.___\_____/___.-`____.-'======
 *                    `=---='
 * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
 *         佛祖保佑       永无BUG   如期完成
 */
class CodeLine {
    /**
     * 今天0点0分的时间戳,用判断今天修改的文件
     * @var int
     */
    private $time;
    
    /**
     * 设置超时时间为0
     */
    public function __construct() {
        set_time_limit(0);
        clearstatcache();
        $this->time = strtotime(date('Y-m-d'));
    }
    
    /**
     * 代码行数统计
     * @param string $dir 要开始统计的目录
     * @param array $type 要统计的文件后缀名
     * @param string $exclude 要排除的目录或者文件,请用正则表达式
     * @return boolean|array 返回各个类型文件的统计行数
     */
    public function countLine($dir, $type = ['php'], $exclude = '/^\..*|backup(_\d+)?$/') {
        if (!is_dir($dir)) return false;
        $count = array_combine($type, array_fill(0, count($type), ['line'=>0, 'space'=>0]));
        $dirList = scandir($dir);
        foreach ($dirList as $d) {
            if ($d === '.' || $d === '..' || preg_match($exclude, $d)) continue;
            $path = "$dir/$d";
            if (is_file($path) && in_array(($extension = pathinfo($path, PATHINFO_EXTENSION)), $type)) {
                $handle = fopen($path, 'r');
                while (($buffer = fgets($handle, 8192)) !== false) {
                    if (trim($buffer) === '') $count[$extension]['space']++;
                    $count[$extension]['line']++;
                }
                fclose($handle);
            }else if (is_dir($path)) {
                $res = $this->countLine($path, $type, $exclude);
                foreach ($type as $t) {
                    $count[$t]['line'] += $res[$t]['line'];
                    $count[$t]['space'] += $res[$t]['space'];
                }
            }
        }
        return $count;
    }
    
    /**
     * 获取今天修改的文件列表
     * @param string $dir 开始统计的文件夹
     * @param array $type 要统计的文件后缀名
     * @param string $exclude 要排除的文件或者目录
     * @return boolean|array 成功返回文件列表和修改时间,失败返回false
     */
    public function countModify($dir, $type = ['php'], $exclude = '/^\..*|backup(_\d+)?$/') {
        if (!is_dir($dir)) return false;
        $fileList = [];
        $dirList = scandir($dir);
        foreach ($dirList as $d) {
            if ($d === '.' || $d === '..' || preg_match($exclude, $d)) continue;
            $path = "$dir/$d";
            if (is_file($path) && in_array(pathinfo($path, PATHINFO_EXTENSION), $type)) {
                $mtime = filemtime($path);
                if ($mtime >= $this->time) $fileList[] = ['file'=>$path, 'mtime'=>date('Y-m-d H:i:s', $mtime)];
            }else if (is_dir($path)) {
                $fileList = array_merge($fileList, $this->countModify($path, $type, $exclude));
            }
        }
        return $fileList;
    }
    
    /**
     * 备份文件
     * @param string $path 要存放备份文件的目录,必须是不存在的目录
     * @param array $fileList 要备份的文件列表,必须是countModify返回的数组
     */
    public function backup($path, $fileList) {
        $tmpPath = $path;
        for ($i = 1; is_dir($path); $i++) $path = $tmpPath."_$i";
        foreach ($fileList as $file) {
            $truePath = $path.str_replace(ROOT, '', $file['file']);
            $pathinfo = pathinfo($truePath);
            !is_dir($pathinfo['dirname']) ? mkdir($pathinfo['dirname'], 0777, true) : '';
            copy($file['file'], $pathinfo['dirname'].'/'.$pathinfo['basename']);
        }
    }
}