<?php

class SelectForm extends FormBase
{

    public function _getValue()
    {
        return parent::_getValue();
    }

    /**
     * params.default 默认选项文字显示
     * @return string
     */
    public function _getHtml()
    {
        $html = '<select' . $this->_attrs . ' name="' . $this->_field . '">';
        if (isset($this->_params['default'])) {
            $html .= '<option value="">' . $this->_params['default'] . '</option>';
        }
        foreach ($this->_options as $i => $option) {
            $selected = $this->_value !== '' && $this->_value == $i ? ' selected="selected"' : '';
            $html .= '<option ' . $selected . 'value="' . $i . '">' . $option . '</option>';
        }
        $html .= '</select>';

        return $html;
    }

    public function _getSearchHtml()
    {
        $this->_value = Func::request($this->_field);

        $html = '<select name="' . $this->_field . '">';
        if (isset($this->_params['default'])) {
            $html .= '<option value="">' . $this->_params['default'] . '</option>';
        }
        foreach ($this->_options as $i => $option) {
            $selected = $this->_value !== '' && $this->_value == $i ? ' selected="selected"' : '';
            $html .= '<option ' . $selected . 'value="' . $i . '">' . $option . '</option>';
        }
        $html .= '</select>';

        return $html;
    }

}