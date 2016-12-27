<?php

/**
 * 导出按钮
 * Class SubmitForm
 */
class ExportForm extends FormBase
{

    public function _getSearchValue()
    {
        return [];
    }

    public function _getSearchHtml()
    {
        $url = $this->_params['url'] . '?';
        foreach ($_GET as $key => $value) {
            $url .= $key . '=' . $value . '&';
        }
        $url = substr($url, 0, -1);

        return '<a href="'. $url .'"><button class="btn btn-default" type="button">导出</button></a>';
    }
}