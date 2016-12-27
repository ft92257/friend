<?php

class RichtextForm extends FormBase
{

    private static $isFirst = true;//是否第一次调用

    public function _getValue()
    {
        return parent::_getValue();
    }

    public function _getHtml()
    {
        if (self::$isFirst) {
            $html = '<script charset="utf-8" src="' . Func::C('statics') . 'js/kindeditor/kindeditor.js"></script>';
            $html .= '<script charset="utf-8" src="' . Func::C('statics') . 'js/kindeditor/lang/zh_CN.js"></script>';
            self::$isFirst = false;
        } else {
            $html = '';
        }

        $this->setDefaultAttr('richtext');
        $html .= '<textarea ' . $this->_attrs . ' id="richtext_' . $this->_field . '" name="' . $this->_field . '" >' . $this->_value . '</textarea>';
        $html .= '
				<script>
				KindEditor.ready(function(K) {
					window.editor = K.create("#richtext_' . $this->_field . '", {
						"uploadJson" : "' . Func::U('upload', 'richtext') . '"
					});
				});
				</script>';

        return $html;
    }

}