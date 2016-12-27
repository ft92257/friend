<?php

/**
 * 提交按钮，暂时只用于搜索
 * Class SubmitForm
 */
class SubmitForm extends FormBase
{

    public function _getSearchValue()
    {
        return [];
    }

    public function _getSearchHtml()
    {
        $word = isset($this->_params['word']) ? $this->_params['word'] : '查询';

        return '<button class="btn btn-default" type="submit">'.$word.'</button>';
    }
}