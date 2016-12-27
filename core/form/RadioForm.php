<?php

class RadioForm extends FormBase
{

    public function _getValue()
    {
        return parent::_getValue();
    }

    /**
     * params.inline : inline 为单行模式
     * @return string
     */
    public function _getHtml()
    {
        $html = '';
        $inline = Func::KV($this->_params, 'inline', true) ? 'inline' : '';

        foreach ($this->_options as $i => $option) {
            //排除空值的选项
            if ($option == '') {
                continue;
            }

            $html .= '<label class="radio ' . $inline . '">';
            $checked = $this->_value !== '' && $this->_value == $i ? ' checked="checked"' : '';
            $html .= '<input type="radio" ' . $this->_attrs . $checked . ' name="' . $this->_field . '" value="' . $i . '" />' . $option;
            $html .= '</label>';
        }

        return $html;
    }

}