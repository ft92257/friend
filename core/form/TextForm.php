<?php

class TextForm extends FormBase
{

    public function _getValue()
    {
        return parent::_getValue();
    }

    public function _getHtml()
    {
        return parent::_getHtml();
    }

    public function _getSearchHtml()
    {
        return '<input type="text" name="' . $this->_field . '" value="' . Func::request($this->_field) . '">';
    }

    public function _getSearchValue()
    {
        $val = Func::request($this->_field);
        $search = [];
        if ($val !== '') {
            if (isset($this->_params['exact'])) {
                //精确查找
                $search[$this->_field] = $val;
            } elseif (isset($this->_params['concat'])) {
                //多字段连接搜索，concat值不包含自己
                $search[] = 'concat(' . $this->_field . ',' . $this->_params['concat'] . ') like ' . "'%" . $val . "%'";
            } else {
                $search[$this->_field . ' like'] = '%' . $val . '%';
            }
        }

        return $search;
    }
}