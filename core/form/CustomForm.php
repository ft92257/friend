<?php

/**
 * 自定义内容
 * Class CustomForm
 */
class CustomForm extends FormBase
{

    public function _getValue()
    {
        return Func::request($this->_field);
    }

    public function _getHtml()
    {
        return $this->_value;
    }

}