<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\service;

/**
 * Class UploadFile
 * @package lying\service
 *
 * @property string $name 文件的原名称
 * @property string $type 文件的MIME类型
 * @property int $size 文件的大小,单位为字节
 * @property string $tmp_name 文件被上传后在服务端储存的临时文件名
 * @property int $error 和该文件上传相关的错误代码
 */
class UploadFile extends Service
{
    /**
     * @var string 文件的原名称
     */
    protected $name;

    /**
     * @var string 文件的MIME类型
     */
    protected $type;

    /**
     * @var int 文件的大小,单位为字节
     */
    protected $size;

    /**
     * @var string 文件被上传后在服务端储存的临时文件名
     */
    protected $tmp_name;

    /**
     * @var int 和该文件上传相关的错误代码
     */
    protected $error;



    /**
     * @var string 文件名
     */
    private $_baseName;

    /**
     * @var string 文件的扩展名
     */
    private $_extension;

    /**
     * @var string 文件的真实MIME
     */
    private $_mimeType;

    /**
     * @var string 文件的MD5
     */
    private $_md5;

    /**
     * @var string 文件的SHA1
     */
    private $_sha1;







    /**
     * @inheritdoc
     */
    protected function init()
    {
        parent::init();
        $this->_baseName = pathinfo($this->name, PATHINFO_BASENAME);;
        $this->_extension = pathinfo($this->name, PATHINFO_EXTENSION);
        $this->_mimeType = (new \finfo(FILEINFO_MIME_TYPE))->file($this->tmp_name);
        $this->_md5 = md5_file($this->tmp_name);
        $this->_sha1 = sha1_file($this->tmp_name);


    }

    /**
     * 获取客户端文件名
     * @return string
     */
    public function getClientName()
    {
        return $this->name;
    }

    /**
     * 获取客户端文件的MIME类型;此MIME类型在PHP端并不检查,因此不要想当然认为有这个值
     * @return string
     */
    public function getClientMimeType()
    {
        return $this->type;
    }

    /**
     * 获取已上传文件的大小,单位为字节
     * @return int
     */
    public function getClientSize()
    {
        return $this->size;
    }

    /**
     * 获取客户端文件扩展名
     * @return string
     */
    public function getClientExtension()
    {
        return $this->_extension;
    }

    /**
     * 获取客户端文件扩展名文件名
     * @return string
     */
    public function getClientBaseName()
    {
        return $this->_baseName;
    }

    /**
     * 根据文件内容获取mime类型
     * @return bool|string
     */
    public function getMimeType()
    {
        return $this->_mimeType;
    }














    /**
     * 获取上传的文件名(不包含扩展名)
     * @return string
     */
    public function getBaseName()
    {
        return $this->_baseName;
    }

    /**
     * 获取上传文件的扩展名(不包含.)
     * @return string
     */
    public function getExtension()
    {
        return $this->_extension;
    }

    /**
     * 返回上传文件的MD5值
     * @return string
     */
    public function getMd5()
    {
        return $this->_md5;
    }

    /**
     * 返回保存的文件名,默认为MD5.扩展名,如果没有扩展名,则默认不加`.扩展名`
     * @return string
     */
    public function getSaveName()
    {
        return $this->_saveName;
    }

    /**
     * 手动设置文件保存时的名称
     * @param string $saveName 文件保存时使用的名称(带后缀,不带路径,/或者\会被转换成-)
     */
    public function setSaveName($saveName)
    {
        $this->_saveName = str_replace(['/', '\\'], '-', $saveName);
    }

    /**
     * 当做字符串输出
     * @return string
     */
    public function __toString()
    {
        return $this->name;
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
