<?php

/**
 * 隐藏控件
 * Class HiddenForm
 */
class HiddenForm extends FormBase
{

    public function _getSearchHtml()
    {
        $this->_value = Func::request($this->_field);

        return '<input type="hidden" name="'.$this->_field.'" value="' . $this->_value . '" />';
    }

}