<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\captcha;

use lying\service\Service;

/**
 * Class Captcha
 * @package lying\captcha
 */
class Captcha extends Service
{
    /**
     * @var string 字符串
     */
    private static $_chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPRSTUVWXYZ23456789';

    /**
     * @var int 验证码长度
     */
    protected $length = 4;

    /**
     * @var int 宽
     */
    protected $width = 120;

    /**
     * @var int 高
     */
    protected $height = 40;

    /**
     * @var int 干扰线条数
     */
    protected $lines = 10;

    /**
     * @var array 字体库
     */
    protected $fonts = [];

    /**
     * @var array 背景色
     */
    protected $bg = [255, 255, 255];

    /**
     * @var int 字体大小
     */
    protected $fontSize = 20;

    /**
     * @var int 噪点
     */
    protected $noisy = 50;

    /**
     * @var int 验证码有效期
     */
    protected $expire = 120;

    /**
     * @inheritdoc
     */
    protected function init()
    {
        parent::init();
        foreach (glob(__DIR__ . DS . 'font' . DS . '*.ttf') as $ttf) {
            $this->fonts[] = $ttf;
        }
    }

    /**
     * 生成验证码
     * @param string $scene 验证码场景值
     */
    public function render($scene = 'default')
    {
        //验证码字符串
        $codeStr = substr(str_shuffle(self::$_chars), 0, $this->length);

        //验证码字体
        shuffle($this->fonts);
        $fontFile = reset($this->fonts);

        //创建画布
        $img = imagecreate($this->width, $this->height);

        //设置背景色
        imagecolorallocate($img, $this->bg[0], $this->bg[1], $this->bg[2]);

        //添加干扰线
        for ($j = 0; $j < $this->lines; $j++) {
            $lw = mt_rand(0, $this->width);
            $lh = mt_rand(0, $this->height);
            $lineColor = imagecolorallocate($img, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            imagearc($img, $this->width - $lw, $lh, $lw, $lh, mt_rand(0, 180), mt_rand(180, 360), $lineColor);
        }

        //添加噪点
        for ($j = 0; $j < $this->noisy; $j++) {
            $noisyColor = imagecolorallocate($img, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            imagesetpixel($img, mt_rand(0, $this->width), mt_rand(0, $this->height), $noisyColor);
        }

        //写入文字
        $x = mt_rand(0, $this->fontSize * 0.5);
        for ($j = 0; $j < $this->length; $j++) {
            $fontColor = imagecolorallocate($img, mt_rand(0, 200), mt_rand(0, 200), mt_rand(0, 200));
            $y = mt_rand($this->fontSize, $this->height);
            imagettftext($img, $this->fontSize, mt_rand(-40, 40), $x, $y, $fontColor, $fontFile, $codeStr[$j]);
            $x += mt_rand($this->fontSize * 1.2, $this->fontSize * 1.6);
        }

        //写入session
        \Lying::$maker->session->set('captcha_' . $scene, [$codeStr, time() + $this->expire]);

        //输出图片头
        \Lying::$maker->response->setHeader('Content-Type', 'image/png')->send();

        //输出图片
        imagepng($img);
    }

    /**
     * 校验验证码
     * @param string $code 验证码
     * @param string $scene 验证码场景值
     * @return bool 成功返回true,失败返回false
     */
    public function check($code, $scene = 'default')
    {
        if ($code) {
            $key = 'captcha_' . $scene;
            $scode = \Lying::$maker->session->get($key);
            if ($scode) {
                \Lying::$maker->session->remove($key);
                return $scode[1] >= time() && strval(strtolower($code)) === strval(strtolower($scode[0]));
            }
        }
        return false;
    }
}
