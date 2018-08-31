<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\upload;

use lying\service\Service;

/**
 * Class Upload
 * @package lying\upload
 */
class Upload extends Service
{
    /**
     * @var array 允许上传的扩展名
     */
    protected $ext = ['jpg', 'jpeg', 'png', 'gif', 'mp3', 'zip', 'rar'];

    /**
     * @var int 允许上传的文件大小
     */
    protected $size = 8388608;

    /**
     * @var array 允许上传的MIME
     */
    protected $type = [];

    /**
     * @var string 最后一次错误信息
     */
    private $_error = '';

    /**
     * 判断文件是否符合
     * @param UploadFile $file 上传的文件
     * @param array $options 额外的判断参数
     * @return bool
     */
    protected function validate(UploadFile $file, $options)
    {
        $ext = isset($options['ext']) ? (array)$options['ext'] : $this->ext;
        $size = isset($options['size']) ? intval($options['size']) : $this->size;
        $type = isset($options['type']) ? (array)$options['type'] : $this->type;
        if ($file->error !== UPLOAD_ERR_OK) {
            switch ($file->error) {
                case UPLOAD_ERR_INI_SIZE:
                    $this->setError('The uploaded file exceeds the upload_max_filesize directive in php.ini');
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $this->setError('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form');
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $this->setError('The uploaded file was only partially uploaded');
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $this->setError('No file was uploaded');
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $this->setError('Missing a temporary folder');
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $this->setError('Failed to write file to disk');
                    break;
            }
            return false;
        } elseif ($ext && !in_array($file->getExtension(), $ext)) {
            $this->setError('Unsupported file extension');
            return false;
        } elseif ($size && $file->size > $size) {
            $this->setError('Uploaded file exceeds size');
            return false;
        } elseif ($type && !in_array($file->type, $type)) {
            $this->setError('Unsupported file MIME');
            return false;
        } elseif (!is_uploaded_file($file->tmp_name)) {
            $this->setError('Non-uploaded file');
            return false;
        } else {
            $this->setError('');
            return true;
        }
    }

    /**
     * 保存上传的文件
     * @param UploadFile $file 上传的文件
     * @param string $dir 文件保存的路径
     * @param array $options 额外的选项
     * @return bool 成功返回true,失败返回false
     */
    public function save(UploadFile $file, $dir, $options = [])
    {
        if ($this->validate($file, $options)) {
            $dir = rtrim(str_replace(['/', '\\'], DS, $dir), '/\\');
            if (is_dir($dir) || \Lying::$maker->helper->mkdir($dir)) {
                $destination = $dir . DS . $file->getSaveName();
                return move_uploaded_file($file->tmp_name, $destination);
            } else {
                $this->setError('Failed to create directory');
                return false;
            }
        }
        return false;
    }

    /**
     * 设置错误信息
     * @param string $error
     */
    protected function setError($error)
    {
        $this->_error = $error;
    }

    /**
     * 获取最后一次上传发生的错误
     * @return string
     */
    public function getError()
    {
        return $this->_error;
    }
}
