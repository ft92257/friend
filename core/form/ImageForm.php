<?php
require_once \Cf::getRootPath() . '/app/core/File.php';
use App\Core\File;

class ImageForm extends FormBase
{

    public function _getValue()
    {
        //html设置enctype
        if (!Func::checkUpload($this->_field)) {
            parent::$_errorMessage = '请上传文件';

            return '';
        }
        $thumbs = isset($this->_params['thumbs']) ? $this->_params['thumbs'] : '';
        $info = File::m()->upload($this->_field, 'image', $thumbs);
        if ($info['status'] != 0) {
            parent::$_errorMessage = $info['msg'];
            $value = '';
        } else {
            //$value = $info['data']['id'];
            $value = $info['data']['url'];
        }

        return $value;
    }

    public function _getHtml()
    {
        $html = '<div class="thumbnail">';
        if ($this->_value) {
            if (isset($this->_params['thumb'])) {
                $src = Func::getThumbUrl($this->_value, $this->_params['thumb']);
            } else {
                $src = $this->_value;
            }

            $html .= '<a href="' . $this->_value . '" target="_blank"><img src="' . $src . '" alt="" width="120"></a>';
        }
        $html .= '<div class="caption">
            <p><input class="input-file" type="file"' . $this->_attrs . ' name="' . $this->_field . '" /></p>
            </div>
        </div>';

        return $html;
    }

}