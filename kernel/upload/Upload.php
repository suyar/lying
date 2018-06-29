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
                    $this->setError('上传文件超出INI配置大小');
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $this->setError('上传文件超出表单配置大小');
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $this->setError('上传的文件不完整');
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $this->setError('没有文件被上传');
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $this->setError('找不到临时文件夹');
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $this->setError('文件写入失败');
                    break;
            }
            return false;
        } elseif ($ext && !in_array($file->getExtension(), $ext)) {
            $this->setError('不支持的文件扩展名');
            return false;
        } elseif ($size && $file->size > $size) {
            $this->setError('上传的文件超出配置大小');
            return false;
        } elseif ($type && !in_array($file->type, $type)) {
            $this->setError('不支持的文件类型');
            return false;
        } elseif (!is_uploaded_file($file->tmp_name)) {
            $this->setError('非上传文件');
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
                $this->setError('文件夹创建失败');
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
