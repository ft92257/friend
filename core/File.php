<?php
/**
 * 文件上传类
 */
require_once \Cf::getRootPath() . '/vendor/qiniu/php-sdk/autoload.php';
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use Qiniu\Processing\Operation;

class File extends \ApiBaseModel
{
    protected $_tableName = 'tb_file';

    public $localFiles = null;//设置成空数组[]用来接收本地文件

    protected static function getConfig()
    {
        return Func::C('upload');
    }

    protected static function getRootDir()
    {
        return Cf::getRootPath() . '/public/';
    }

    public static function checkUpload($field)
    {
        return Func::checkUpload($field);
    }

    public static function getFileUrl($file_path, $thumb = '')
    {
        $base = 'http://' . Func::C('QINIU_DOMAIN') . '/';

        if ($thumb == '') {
            return $base . $file_path;
        } else {
            return $base . str_replace('.', '_' . $thumb . '.', $file_path);
        }
    }

    public function multiUpload($filename = 'filename', $type = 'image', $thumbs = '') {
        $config = self::getConfig();
        if (!isset($config[$type])) {
            return [];
            //return $this->_result(-3, '无效的配置！');
        }

        if (!self::checkUpload($filename)) {
            return [];
            //return $this->_result(-4, '没有找到上传的文件！');
        }

        $fileDatas = [];
        foreach ($_FILES[$filename] as $attr => $values) {
            foreach ($values as $i => $val) {
                $fileDatas[$i][$attr] = $val;
            }
        }

        $urls = [];
        foreach ($fileDatas as $fileData) {
            $info = $this->upload($fileData, $type, $thumbs);
            if ($info['status'] == 0) {
                $urls[] = $info['data']['url'];
            }
        }

        return $urls;
    }

    /**
     * 上传文件
     * @param Array $filename 表单file控件的name属性
     * @param String $type 上传文件类型
     * @return Array      上传成功返回文件保存信息，失败返回错误信息
     */
    public function upload($filename = 'filename', $type = 'image', $thumbs = '')
    {
        //上传文件配置
        $config = self::getConfig();
        if (!isset($config[$type])) {
            return $this->_result(-3, '无效的配置！');
        }

        //支持数组
        if (is_array($filename)) {
            $fileData = $filename;
        } else {
            if (!self::checkUpload($filename)) {
                return $this->_result(-4, '没有找到上传的文件！');
            }

            $fileData = $_FILES[$filename];
        }

        $config = $config[$type];

        $this->parseThumbConfig($config, $thumbs);

        $upload = new UploadFile($config);
        //执行上传
        $info = $upload->uploadOne($fileData, self::getRootDir() . $config['savePath']);
        //执行上传操作
        if (!$info) {                        // 上传错误提示错误信息
            return $this->_result(-1, $upload->getErrorMsg());
        } else {// 上传成功 获取上传文件信息
            $info = $info[0];

            //图片地址保存宽高信息
            if ($type == 'image') {
                $filename = $info['savename'];
                $size = getimagesize(self::getRootDir() . $config['savePath'] . $filename);
                $filename = Func::getThumbUrl($filename, $size['0'] . 'x' . $size['1']);
                rename(self::getRootDir() . $config['savePath'] . $info['savename'], self::getRootDir() . $config['savePath'] . $filename);
                $info['savename'] = $filename;
            }

            //上传到七牛
            $ret = $this->qiniuUpload($config['savePath'] . $info['savename'], $thumbs);
            if (!$ret) {
                return $this->_result(-3, '上传失败！');
            }

            //添加数据库记录
            $data = [
                'uid'        => $this->uid,
                'type'       => $info['type'],
                'thumbs'     => $thumbs,
                'size'       => $info['size'],
                'path'       => $config['savePath'] . $info['savename'],
                'created_at' => date('Y-m-d H:i:s'),
            ];

            $data['id'] = $this->addData($data);
            $data['url'] = self::getFileUrl($data['path']);
            if (!$data['id']) {
                return $this->_result(-2, '数据库添加失败！');
            } else {

                return $this->_result(0, '上传成功！', $data);
            }
        }
    }


    /**
     * 返回结果
     * @param number $status
     * @param string $msg
     * @param array $data
     * @return array:number string unknown
     */
    protected function _result($status = 0, $msg = '', $data = array())
    {
        return array(
            'status' => $status,
            'msg'    => $msg,
            'data'   => $data,
        );
    }

    /**
     * 解析缩略图配置 thumbs字段格式：120x120,40x60
     * @param array $aConfig
     * @param string $thumbs
     */
    protected function parseThumbConfig(&$aConfig, $thumbs)
    {
        if ($thumbs) {
            $aSuffix = array();
            $aWidth = array();
            $aHeight = array();

            $aThumbs = explode(',', $thumbs);
            foreach ($aThumbs as $value) {
                $aSuffix[] = '_' . $value;
                $aT = explode('x', $value);
                $aWidth[] = $aT[0];
                $aHeight[] = $aT[1];
            }
            $aConfig['thumb'] = true;
            $aConfig['thumbMaxWidth'] = join(',', $aWidth);
            $aConfig['thumbMaxHeight'] = join(',', $aHeight);
            $aConfig['thumbSuffix'] = join(',', $aSuffix);
        }
    }

    /**
     * 上传文件到七牛
     * @param $pathname
     * @param $thumbs
     * @return bool
     * @throws Exception
     */
    public function qiniuUpload($pathname, $thumbs)
    {
        $auth = new Auth(Cf::C('QINIU_KEY'), Cf::C('QINIU_SECRET'));

        $upToken = $auth->uploadToken(Cf::C('QINIU_BUCKET'));

        $localfile = self::getRootDir() . $pathname;

        // 初始化 UploadManager 对象并进行文件的上传。
        $uploadMgr = new UploadManager();
        list($ret, $err) = $uploadMgr->putFile($upToken, $pathname, $localfile);
        //上传缩略图
        /**
        if ($thumbs) {
            $aThumbs = explode(',', $thumbs);
            foreach ($aThumbs as $thumb) {
                $thumbfile = Func::getThumbUrl($localfile, $thumb);
                $uploadMgr->putFile($upToken, Func::getThumbUrl($pathname, $thumb), $thumbfile);
                unlink($thumbfile);
            }
        }*/

        //echo "\n====> putFile result: \n";
        if ($err !== null) {
            //var_dump($err);
            return false;
        } else {
            if (is_array($this->localFiles)) {
                $this->localFiles[] = $localfile;
            } else {
                unlink($localfile);
            }

            return true;
            //var_dump($ret);
        }
    }

}