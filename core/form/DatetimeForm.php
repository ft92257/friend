<?php

class DatetimeForm extends FormBase
{

    public function _getValue()
    {
        return strtotime(Func::R($this->_field));
    }

    /**
     * params.type date 日期，默认日期+时间格式
     * params.startDate 开始时间
     * params.minDate 最小可选的日期
     * @return string
     */
    public function _getHtml()
    {
        if (Func::KV($this->_params, 'type') == 'date') {
            if (is_numeric($this->_value)) {
                $this->_value = date('Y-m-d', $this->_value);
            }

            $startDate = isset($this->_params['startDate']) ? $this->_params['startDate'] : '%y-%M-%d';
            $minDate = isset($this->_params['minDate']) ? $this->_params['minDate'] : '1900-01-01';
            $this->setDefaultAttr('input-small');
            $html = '<input type="text" ' . $this->_attrs . ' name="' . $this->_field . '" value="' . $this->_value . '" onfocus="WdatePicker({minDate:\'' . $minDate . '\',startDate:\'' . $startDate . '\',dateFmt:\'yyyy-MM-dd\'})" />';
        } else {
            if (is_numeric($this->_value)) {
                $this->_value = date('Y-m-d H:i:s', $this->_value);
            }

            $startDate = isset($this->_params['startDate']) ? $this->_params['startDate'] : '%y-%M-%d %H:%i:%s';
            $minDate = isset($this->_params['minDate']) ? $this->_params['minDate'] : '1900-01-01 00:00:00';
            $this->setDefaultAttr('input-medium');
            $html = '<input type="text" ' . $this->_attrs . ' name="' . $this->_field . '" value="' . $this->_value . '" onfocus="WdatePicker({minDate:\'' . $minDate . '\',startDate:\'' . $startDate . '\',dateFmt:\'yyyy-MM-dd HH:mm:ss\'})" />';
        }

        return $html;
    }

    public function _getSearchHtml()
    {
        if (Func::KV($this->_params, 'type') == 'date') {
            $startDate = isset($this->_params['startDate']) ? $this->_params['startDate'] : '%y-%M-%d';
            $minDate = isset($this->_params['minDate']) ? $this->_params['minDate'] : '1900-01-01';

            $html = '<input type="text" style="width:100px;" name="' . $this->_field . '_BEGIN" value="' . Func::request($this->_field . '_BEGIN') . '" onfocus="WdatePicker({minDate:\'' . $minDate . '\',startDate:\'' . $startDate . '\',dateFmt:\'yyyy-MM-dd\'})" />';
            $html .= ' ~ <input type="text" style="width:100px;" name="' . $this->_field . '_END" value="' . Func::request($this->_field . '_END') . '" onfocus="WdatePicker({minDate:\'' . $minDate . '\',startDate:\'' . $startDate . '\',dateFmt:\'yyyy-MM-dd\'})" />';
        } else {
            $startDate = isset($this->_params['startDate']) ? $this->_params['startDate'] : '%y-%M-%d %H:%i:%s';
            $minDate = isset($this->_params['minDate']) ? $this->_params['minDate'] : '1900-01-01 00:00:00';

            $html = '<input type="text" name="' . $this->_field . '_BEGIN" value="' . Func::request($this->_field . '_BEGIN') . '" onfocus="WdatePicker({minDate:\'' . $minDate . '\',startDate:\'' . $startDate . '\',dateFmt:\'yyyy-MM-dd HH:mm:ss\'})" />';
            $html .= ' ~ <input type="text" name="' . $this->_field . '_END" value="' . Func::request($this->_field . '_END') . '" onfocus="WdatePicker({minDate:\'' . $minDate . '\',startDate:\'' . $startDate . '\',dateFmt:\'yyyy-MM-dd HH:mm:ss\'})" />';
        }

        return $html;
    }

    public function _getSearchValue()
    {
        $field = $this->_field;
        $search = [];
        if (Func::KV($this->_params, 'type') == 'date') {
            $begin = Func::request($field . '_BEGIN');
            $end = Func::request($field . '_END');
            if ($begin || $end) {
                if ($end) {
                    $end = $end . ' 23:59:59';
                } else {
                    $end = date('Y-m-d') . ' 23:59:59';
                }
                if (Func::KV($this->_params, 'strtotime')) {
                    $begin = strtotime($begin);
                    $end = strtotime($end);
                }
                $search[$field . ' >='] = $begin;
                $search[$field . ' <='] = $end;
            }
        } else {
            $begin = Func::request($field . '_BEGIN');
            $end = Func::request($field . '_END');
            if ($begin || $end) {
                if (!$end) {
                    $end = date('Y-m-d H:i:s');
                }
                if (Func::KV($this->_params, 'strtotime')) {
                    $begin = strtotime($begin);
                    $end = strtotime($end);
                }
                $search[$field . ' >='] = $begin;
                $search[$field . ' <='] = $end;
            }
        }

        return $search;
    }

}