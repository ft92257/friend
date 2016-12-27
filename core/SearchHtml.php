<?php

/**
 * 废弃 列表搜索html类
 * @author zhaojunhui
 *
 */
class SearchHtml
{

    protected static function selectChild($field, $config, $options, $pi, $level)
    {
        $html = '<span style="display:none;" id="' . $field . '_L' . ($level - 1) . '_' . $pi . '">';
        if (!isset($config['params']['no_all'])) {
            $html .= '<option value="">全部</option>';
        }

        $value = Func::request($field . '_L' . $level);
        $cChild = '';
        foreach ($options as $i => $option) {
            $child = '';
            if (is_array($option)) {
                $child = $option;
                unset($child[0]);

                $cChild .= self::selectChild($field, $config, $child, $i, $level + 1);

                $option = $option[0];
            }
            $selected = $value !== '' && $value == $i ? ' selected="selected"' : '';
            $html .= '<option ' . $selected . 'value="' . $i . '">' . $option . '</option>';
        }
        $html .= '</span>';

        return $html . $cChild;
    }

    public static function getHtml($field, $config, $options)
    {
        $br = isset($config['params']['br']) ? '<br>' : '';

        $s = '';
        $subject = $config[0];
        switch ($config[1]) {
            case 'exact_text':
            case 'text':
                $s .= $br . '<div class="input-prepend"><span class="add-on">' . $subject . '</span>';
                $s .= '<input type="text" name="' . $field . '" value="' . Func::request($field) . '" class="form-control span2">';
                $s .= '</div>&nbsp;&nbsp;';
                break;
            case 'submit':
                $s .= $br . '<input type="submit" class="btn btn-primary" value="' . $subject . '">&nbsp;';
                break;
            case 'reset':
                $s .= $br . '<input type="reset" class="btn" value="' . $subject . '">&nbsp;';
                break;
            case 'select':
                $s .= $br . '<div class="input-prepend"><span class="add-on">' . $subject . '</span>';
                $val = Func::request($field);
                $checked = $val === '' ? ' selected="selected"' : '';
                $s .= '<select class="span2" name="' . $field . '">';
                if (!isset($config['params']['no_all'])) {
                    $s .= '<option value="">全部</option>';
                }
                foreach ($options as $i => $option) {
                    $checked = $val !== '' && $val == $i ? ' selected="selected"' : '';
                    $s .= '<option ' . $checked . ' value="' . $i . '">' . $option . '</option>';
                }
                $s .= '</select></div>&nbsp;&nbsp;';
                break;
            case 'select_level':
                $s .= $br . '<div class="input-prepend"><span class="add-on">' . $subject . '</span>';
                $val = Func::request($field . '_L1');
                $checked = $val === '' ? ' selected="selected"' : '';
                $s .= '<select class="span2" name="' . $field . '_L1" onchange="showChildren(this, 1)">';
                //if (!isset($config['params']['no_all'])) {
                //$s .= '<option value="">全部</option>';
                //}
                $cChild = '';
                foreach ($options as $i => $option) {
                    if (is_array($option)) {
                        $child = $option;
                        unset($child[0]);

                        $cChild .= self::selectChild($field, $config, $child, $i, 2);

                        $option = $option[0];
                    }

                    $checked = $val !== '' && $val == $i ? ' selected="selected"' : '';
                    $s .= '<option ' . $checked . ' value="' . $i . '">' . $option . '</option>';
                }
                $s .= '</select>';

                if (Func::KV($config['params'], 'level') > 1) {
                    for ($i = 2; $i <= $config['params']['level']; $i++) {
                        $ochange = ($i == $config['params']['level']) ? '' : ' onchange="showChildren(this, 1)"';
                        $s .= '<select style="margin-left:5px;" class="span2" name="' . $field . '_L' . $i . '"' . $ochange . '>';
                        $s .= '</select>';
                    }
                }

                $s .= '<script>$(function(){';
                for ($i = 1; $i <= $config['params']['level']; $i++) {
                    if ($i != $config['params']['level']) {
                        $s .= 'showChildren($("[name=' . $field . '_L' . $i . ']", 0).get(0));';
                    }
                }
                $s .= '});</script>';

                $s .= $cChild . '</div>&nbsp;&nbsp;';
                break;
            case 'day'://日期格式,没有起始时间
                $s .= $br . '<div class="input-prepend"><span class="add-on">' . $subject . '</span>';
                $s .= '<input type="text" class="input-small" name="' . $field . '" value="' . Func::request($field) . '" onfocus="WdatePicker({startDate:\'%y-%M-01\',dateFmt:\'yyyy-MM-dd\'})" />';
                $s .= '</div>';
                $s .= '&nbsp;&nbsp;';
                break;
            case 'date':
                $s .= $br . '<div class="input-prepend"><span class="add-on">' . $subject . '</span>';
                $s .= '<input type="text" class="input-small" name="' . $field . '_BEGIN" value="' . Func::request($field . '_BEGIN') . '" onfocus="WdatePicker({startDate:\'%y-%M-01\',dateFmt:\'yyyy-MM-dd\'})" />';
                $s .= '</div>';
                $s .= ' <span class="wave">~</span> <input type="text" class="input-small" name="' . $field . '_END" value="' . Func::request($field . '_END') . '" onfocus="WdatePicker({startDate:\'%y-%M-01\',dateFmt:\'yyyy-MM-dd\'})" />&nbsp;&nbsp;';
                break;
            case 'datetime':
                $s .= $br . '<div class="input-prepend"><span class="add-on">' . $subject . '</span>';
                $s .= '<input type="text" class="input-medium" name="' . $field . '_BEGIN" value="' . Func::request($field . '_BEGIN') . '" onfocus="WdatePicker({startDate:\'%y-%M-01\',dateFmt:\'yyyy-MM-dd HH:mm:ss\'})" />';
                $s .= '</div>';
                $s .= ' <span class="wave">~</span> <input type="text" class="input-medium" name="' . $field . '_END" value="' . Func::request($field . '_END') . '" onfocus="WdatePicker({startDate:\'%y-%M-01\',dateFmt:\'yyyy-MM-dd HH:mm:ss\'})" />&nbsp;&nbsp;';
                break;

            case 'radio_list':
                $s .= $br;
                $s .= '<ul class="nav nav-pills radio_list">';
                $s .= '<li class="title"><span>' . $subject . '</span></li>';

                $val = Func::request($field);
                $params = array_merge($_GET, $_POST);
                unset($params[$field]);
                unset($params['c']);
                unset($params['p']);
                $url = Func::U('', '', $params);
                if ($val === '') {
                    $s .= '<li class="active"><a href="#">全部</a></li>';
                } else {
                    $s .= '<li><a href="' . $url . '">全部</a></li>';
                }
                foreach ($options as $i => $option) {
                    $params[$field] = $i;
                    $url = Func::U('', '', $params);
                    if ($val !== '' && $val == $i) {
                        $s .= '<li class="active"><a href="#">' . $option . '</a></li>';
                    } else {
                        $s .= '<li><a href="' . $url . '">' . $option . '</a></li>';
                    }
                }

                $s .= '</ul>';
                break;

            case 'radio':
                $val = Func::request($field);
                $checked = $val === '' ? ' checked="checked"' : '';
                $s .= '<input type="radio" ' . $checked . ' name="' . $field . '" value="" />全部&nbsp;&nbsp;';
                foreach ($options as $i => $option) {
                    $checked = $val !== '' && $val == $i ? ' checked="checked"' : '';
                    $s .= '<input type="radio" ' . $checked . ' name="' . $field . '" value="' . $i . '" />' . $option . '&nbsp;&nbsp;';
                }
                break;
            case 'checkbox':
                $val = Func::request($field);
                $val = empty($val) ? array() : $val;
                $checked = in_array('ALL', $val, true) ? ' checked="checked"' : '';
                $s .= '<label for="' . $field . '_ALL">全部</label><input type="checkbox" onclick="checkAll(this)"' . $checked . ' id="' . $field . '_ALL" name="' . $field . '[]" value="ALL" />&nbsp;&nbsp;';
                foreach ($options as $i => $option) {
                    $checked = in_array((string)$i, $val, true) ? ' checked="checked"' : '';
                    $s .= '<label for="' . $field . '_' . $i . '">' . $option . '</label>' . '<input type="checkbox" onclick="checkNotAll(this)"' . $checked . ' id="' . $field . '_' . $i . '" name="' . $field . '[]" value="' . $i . '" />&nbsp;&nbsp;';
                }
                break;
            default:
                $s .= $config[1];
                break;
        }

        return $s;
    }

    public static function getCondition($field, $config, &$search)
    {
        switch ($config[1]) {
            case 'text':
                $val = Func::request($field);
                if ($val !== '') {
                    if (isset($config['params']['concat'])) {
                        $search[] = 'concat(' . $field . ',' . $config['params']['concat'] . ') like ' . "'%" . $val . "%'";
                    } else {
                        $search[$field . ' like'] = '%' . $val . '%';
                    }
                }
                break;
            case 'day':
                $val = Func::request($field);
                if ($val !== '') {
                    $search[$field] = strtotime($val);
                }
                break;
            case 'date':
                $begin = Func::request($field . '_BEGIN');
                $end = Func::request($field . '_END');
                if ($begin || $end) {
                    $begin = (int)strtotime($begin);
                    if ($end) {
                        $end = strtotime($end . ' 23:59:59');
                    } else {
                        $end = strtotime(date('Y-m-d') . ' 23:59:59');
                    }
                    $search[$field . ' between'] = array($begin, $end);
                }
                break;
            case 'datetime':
                $begin = Func::request($field . '_BEGIN');
                $end = Func::request($field . '_END');
                if ($begin || $end) {
                    $begin = (int)strtotime($begin);
                    if ($end) {
                        $end = strtotime($end);
                    } else {
                        $end = strtotime(date('Y-m-d H:i:s'));
                    }
                    $search[$field . ' between'] = array($begin, $end);
                }
                break;
            case 'checkbox':
                $val = Func::request($field);
                $k = array_search('ALL', $val);
                if ($k !== false) {
                    unset($val[$k]);
                }
                if (!empty($val)) {
                    $search[$field . ' in'] = $val;
                }
                break;
            case 'select_level':
                $val = '';
                for ($i = 1; $i <= $config['params']['level']; $i++) {
                    $val .= Func::request($field . '_L' . $i);
                }
                if ($val !== '') {
                    $search[$field . ' like'] = $val . '%';
                }
                break;
            default:
                $val = Func::request($field);
                if ($val !== '') {
                    $search[$field] = $val;
                }
                break;
        }
    }

}