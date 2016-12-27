<?php

class CheckboxForm extends FormBase
{

    public function _getValue()
    {
        return Func::joinArray(Func::R($this->_field));
    }

    /**
     * params.inline 是否同一行展示
     * @return string
     */
    public function _getHtml()
    {
        $inline = Func::KV($this->_params, 'inline', true) ? 'inline' : '';
        $aVals = explode(',', $this->_value);
        $html = '';
        foreach ($this->_options as $i => $option) {
            $html .= '<label class="checkbox ' . $inline . '">';
            $checked = $this->_value !== '' && in_array($i, $aVals) ? ' checked="checked"' : '';

            $html .= '<input type="checkbox" ' . $checked . ' name="' . $this->_field . '[]" value="' . $i . '" />' . $option;
            $html .= '</label>';
        }

        return $html;
    }

}