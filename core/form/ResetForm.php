<?php

/**
 * 撤销按钮，暂时只用于搜索
 * Class SubmitForm
 */
class ResetForm extends FormBase
{

    public function _getSearchValue()
    {
        return [];
    }

    public function _getSearchHtml()
    {
        $word = isset($this->_params['word']) ? $this->_params['word'] : '重置';

        return '<button class="btn btn-default" type="button" onclick="var host = window.location.href;window.location.href = host.substring(0, host.indexOf(\'?\'));">'.$word.'</button>';
    }
}