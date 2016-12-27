<?php

class TextareaForm extends FormBase
{

    public function _getValue()
    {
        return parent::_getValue();
    }

    public function _getHtml()
    {
        $this->setDefaultAttr('bigtextarea');

        return '<textarea ' . $this->_attrs . ' name="' . $this->_field . '">' . $this->_value . '</textarea>';
    }

}