<?php

/**
 * 只显示内容，不能修改
 * Class SpanForm
 */
class SpanForm extends FormBase
{

    public function _getValue()
    {
        return false;
    }

    public function _getHtml()
    {
        return '<span>' . $this->_value . '</span>';
    }

}