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
     * @var string 当前指向文件路径
     */
    private $_file;

    /**
     * @var string 错误信息
     */
    private $_error = '';

    /**
     * @inheritdoc
     */
    protected function init()
    {
        parent::init();
        $this->_file = $this->tmp_name;
    }

    /**
     * 获取私有属性的值
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->$name;
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
        return pathinfo($this->name, PATHINFO_EXTENSION);
    }

    /**
     * 获取客户端文件文件名
     * @return string
     */
    public function getClientFilename()
    {
        return pathinfo($this->name, PATHINFO_FILENAME);
    }

    /**
     * 根据文件内容获取mime类型
     * @return bool|string
     */
    public function getMimeType()
    {
        return (new \finfo(FILEINFO_MIME_TYPE))->file($this->_file);
    }

    /**
     * 获取文件的MD5
     * @return string
     */
    public function getMd5()
    {
        return md5_file($this->_file);
    }

    /**
     * 获取文件的sha1
     * @return string
     */
    public function getSha1()
    {
        return sha1_file($this->_file);
    }

    /**
     * 判断文件是否为图片类型
     * @return bool
     */
    public function isImage()
    {
        return in_array(strtolower($this->getClientExtension()), ['gif', 'jpg', 'jpeg', 'bmp', 'png']) && in_array($this->getImageType(), [1, 2, 3, 6]);
    }

    /**
     * 返回图像类型
     * @return bool|int
     */
    protected function getImageType()
    {
        if (function_exists('exif_imagetype')) {
            return exif_imagetype($this->_file);
        } else {
            $info = @getimagesize($this->_file);
            return $info ? $info[2] : false;
        }
    }

    /**
     * 返回是否是合法的上传文件
     * @return bool
     */
    public function isValid()
    {
        return is_uploaded_file($this->_file);
    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getError()
    {
        return $this->_error;
    }

    /**
     * 获取文件的绝对路径
     * @return string
     */
    public function getRealPath()
    {
        return realpath($this->_file);
    }

    /**
     * 移动上传的文件
     * @param string $directory 目标文件夹
     * @param string $name 保存的文件名
     * @return bool|string 成功返回保存后的文件路径,失败返回false
     */
    public function move($directory, $name = null)
    {
        if ($this->error) {
            switch ($this->error) {
                case UPLOAD_ERR_INI_SIZE:
                    $this->_error = 'The file exceeds your upload_max_filesize ini directive.';
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $this->_error = 'The file exceeds the upload limit defined in your form.';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $this->_error = 'The file was only partially uploaded.';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $this->_error = 'No file was uploaded.';
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $this->_error = 'The file could not be written on disk.';
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $this->_error = 'File could not be uploaded: missing temporary directory.';
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $this->_error = 'File upload was stopped by a PHP extension.';
                    break;
                default:
                    $this->_error = 'The file was not uploaded due to an unknown error.';
            }
            return false;
        } else if (!$this->isValid()) {
            $this->_error = 'The file is not a valid upload file.';
            return false;
        } else if (!is_dir($directory) && !\Lying::$maker->helper->mkdir($directory)) {
            $this->_error = "Unable to create the directory: {$directory}.";
            return false;
        } else if (!is_writable($directory)) {
            $this->_error = "Unable to write in the directory: {$directory}.";
            return false;
        }

        if ($name === null) {
            $name = $this->getMd5() . '.' . $this->getClientExtension();
        } else {
            $name = str_replace('\\', '/', $name);
            $pos = strrpos($name, '/');
            $name = false === $pos ? $name : substr($name, $pos + 1);
        }

        $target = rtrim($directory, '/\\') . DS . $name;

        try {
            return move_uploaded_file($this->_file, $target) ? ($this->_file = $target) : false;
        } catch (\Exception $e) {
            $this->_error = "Could not move the file to {$target}.";
            return false;
        }
    }
}
