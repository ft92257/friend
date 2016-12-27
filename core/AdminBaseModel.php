<?php

/**
 * 后台基础模型类.
 *
 * @author zhaojunhui
 */
class AdminBaseModel extends ApiBaseModel
{

    //表单配置
    protected $formConfig = array();

    /*
     * 列表配置
     * 格式1) 纯字符串 '字段名'
     * 格式2) array('字段名', 'func' => '方法名', 'params' => array(), 'string' => '字符串{字段}[自定义参数]');
     */
    protected $listConfig = array();

    //搜索配置
    protected $searchConfig = array();

    /*
     * 验证规则，由$this->checkData调用
     * $aValidate[0] 字段名,string $aValidate[1] 方法名，string $aValidate[2] 错误提示消息
     * array $aValidate[3] 其他参数, array $aValidate[4] 功能控制参数
     */
    protected $aValidate = array(//例：array('target', 'unique', '对不起，您已经评过分了！', array('uid' => '{uid}', 'type' => '{type}'), array('replace')),
    );

    protected $_data = array();

    public function setData($data)
    {
        $this->_data = $data;
    }

    public function getData()
    {
        return $this->_data;
    }

    /**
     * 获取列表html
     * @param $data 数据列表
     * @param $vars 自定义参数值
     */
    public function getListHtml($multidata)
    {
        $s = '<tbody><tr>';
        foreach ($this->listConfig as $field => $config) {
            if (!is_array($config)) {
                $s .= '<th>' . $config . '</th>';
            } else {
                $s .= '<th>' . $config[0] . '</th>';
            }
        }
        $s .= '</tr>';

        foreach ($multidata as $data) {
            $s .= '<tr>';
            foreach ($this->listConfig as $field => $config) {
                if (is_array($config)) {
                    $s .= '<td>' . $this->_operate($config, $data) . '</td>';
                } else {
                    $s .= '<td>' . $data[$field] . '</td>';
                }
            }

            $s .= '</tr>' . "\n";
        }

        return $s . "</tbody>";
    }

    /**
     * 操作项内容，可以在子类重新该方法进行处理
     * @param $config
     * @param $data
     * @return string
     */
    protected function _operate($config, $data)
    {
        $ret = '';
        if (isset($config['func'])) {
            $opts = explode(',', $config['func']);
            foreach ($opts as $opt) {
                $func = '_operate_' . $opt;
                if (method_exists($this, $func)) {
                    $ret .= $this->$func($data);
                } else {
                    Func::_replaceValue($opt, $data, '{', '}');
                    $ret .= $opt;
                }
            }
        }

        return $ret;
    }

    protected function _operate_edit($data, $method = 'edit', $pk = 'id', $tag = '编辑')
    {
        $url = Func::U($method, '', array($pk => $data[$pk]));

        return '&nbsp;<a href="' . $url . '">' . $tag . '</a>&nbsp;';
    }

    protected function _operate_delete($data, $method = 'delete', $pk = 'id')
    {
        $url = Func::U($method, '', array($pk => $data[$pk]));

        return '&nbsp;<a href="' . $url . '" onclick="if(!confirm(\'确定要删除该记录吗？\')){return false;}">删除</a>&nbsp;';
    }

    /**
     * 获取表单html
     */
    public function getFormHtml($data = array())
    {
        $s = '';
        //$s .= '<input type="hidden" name="_token" value="'.Func::M('Session')->getToken($this->uid).'" />';

        foreach ($this->formConfig as $field => $aValue) {
            if (is_array($aValue)) {
                $children = '';
                if (isset($aValue['children'])) {
                    foreach ($aValue['children'] as $k => $child) {
                        $children .= FormBase::getChildHtml($child, $this->getOptions($k), $k, Func::KV($data, $k));
                    }
                }
                $s .= FormBase::getHtml($aValue, $this->getOptions($field), $field, Func::KV($data, $field), $children);
            } else {
                $s .= $aValue;
            }
        }

        return $s;
    }

    /**
     * 获取所有需验证的字段
     */
    public function getValidateFields()
    {
        $validateFields = array();
        foreach ($this->aValidate as $aValue) {
            $validateFields[] = $aValue[0];
        }

        return array_unique($validateFields);
    }


    public function getCheckboxOptions($option_name, $key = null, $default = '')
    {
        $options = $this->aOptions;
        $arr = isset($options[$option_name]) ? $options[$option_name] : array();
        if ($key === null) {
            return $arr;
        } else {
            $aKeys = explode(',', $key);
            foreach ($aKeys as &$k) {
                $k = isset($arr[$k]) ? $arr[$k] : $default;
            }

            return join(', ', $aKeys);
        }
    }

    public function getLevelOptions($option_name, $key = null)
    {
        $options = $this->aOptions;
        $arr = isset($options[$option_name]) ? $options[$option_name] : array();
        if ($key === null) {
            return $arr;
        } else {
            $k = substr($key, 0, 2);

            $option = Func::KV($arr, $k);
            if (is_array($option)) {
                $ret = $option[0];
                unset($option[0]);

                $ret .= $this->getChildOptions($option, $key, 2);
            } else {
                $ret = $option;
            }

            return $ret;
        }
    }

    protected function getChildOptions($options, $key, $level)
    {
        $k = substr($key, ($level - 1) * 2, 2);
        if ($k == '') {
            return '';
        }
        $option = Func::KV($options, $k);
        if (is_array($option)) {
            $ret = $option[0];
            unset($option[0]);

            $ret .= $this->getChildOptions($option, $key, $level + 1);
        } else {
            $ret = $option;
        }

        return '\\' . $ret;
    }

    /**
     * 获取表单提交的数据
     */
    public function getFormData($edit = false)
    {
        $this->_errorMessage = '';//清空错误信息
        $data = array();
        //$FormHtml = new FormHtml($this);
        foreach ($this->formConfig as $field => $aValue) {
            if (is_numeric($field)) {
                continue;
            }

            //$value = $FormHtml->getValue($aValue, $field, $edit);
            $value = FormBase::getValue($aValue, $field, $edit);
            if ($value !== false) {
                $data[$field] = $value;
            }
            if (isset($aValue['children'])) {
                foreach ($aValue['children'] as $childField => $childConfig) {
                    //$value = $FormHtml->getValue($childConfig, $childField, $edit);
                    $value = FormBase::getValue($childConfig, $childField, $edit);
                    if ($value !== false) {
                        $data[$childField] = $value;
                    }
                }
            }
        }

        return $this->_after_submit($data, $edit);
    }

    /**
     * 表单数据获取的处理
     * @param array $data
     * @param boolean $edit 是否修改操作
     * @return array
     */
    protected function _after_submit($data, $edit)
    {
        return $data;
    }

    /**
     * 验证单个字段
     */
    public function autoValidate($data)
    {
        $ret = $this->checkData($data, false);
        if (!$ret) {
            return array(
                'status' => -1,
                'msg'    => $this->errorMessage(),
            );
        } else {
            return array(
                'status' => 0,
                'msg'    => '',//填写正确!
            );
        }
    }

    /**
     * @param $checkall true 验证所有规则，false只验证存在的字段
     */
    public function checkData($data, $checkall = true)
    {
        foreach ($this->aValidate as $key => &$aValue) {
            if (!$checkall && !in_array($aValue[0], array_keys($data))) {
                //如果只验证存在的字段，则删除无关的规则
                unset($this->aValidate[$key]);
            } else {
                //验证规则
                $func = $aValue[1];
                Validate::$_params = isset($aValue['params']) ? $aValue['params'] : [];
                if (!Validate::$func(Func::KV($data, $aValue[0]))) {
                    $this->errorMessage($aValue[2]);

                    return false;
                }
            }
        }

        return true;
    }

    /**
     * 获取列表的搜索栏html
     */
    public function getSearchHtml()
    {
        $s = '';
        foreach ($this->searchConfig as $field => $config) {
            if (!is_array($config) || !isset($config[1])) {
                throw new Exception('请设置控件类型');
            }
            //$s .= SearchHtml::getHtml($field, $config, $this->getOptions($field));
            $s .= FormBase::getSearchHtml($config, $this->getOptions($field), $field);
        }

        return $s;
    }

    /**
     * 获取列表搜索条件
     */
    public function getSearchCondition()
    {
        $search = array();
        foreach ($this->searchConfig as $field => $config) {
            if (!is_array($config) || !isset($config[1])) {
                throw new Exception('请设置控件类型');
            }
            /*
            if (!isset($config['params']['custom'])) {
                SearchHtml::getCondition($field, $config, $search);
            }*/
            $search = array_merge($search, FormBase::getSearchValue($config, $field));
        }

        $search = $this->_after_search($search);

        return $search;
    }

    protected function _after_search($search)
    {
        return $search;
    }

    public function getFormConfig($field)
    {
        if (isset($this->formConfig[$field])) {
            return $this->formConfig[$field];
        } else {
            foreach ($this->formConfig as $aValue) {
                if (isset($aValue['children'])) {
                    if (isset($aValue['children'][$field])) {
                        return $aValue['children'][$field];
                    }
                }
            }

            return array();
        }
    }

    public function ajaxUpload()
    {
        $field = Func::request('AJAX_UPLOAD_FIELD');

        if (!Func::checkUpload($field)) {
            die('{"error":"没有选择文件!"}');
        }

        $column = substr($field, 12 + strlen(Func::request('i')));
        $config = $this->getFormConfig($column);
        if (empty($config)) {
            die('{"error":"没有相关配置!"}');
        }

        $thumbs = Func::KV($config['params'], 'thumbs');
        if ($thumbs) {
            $thumbs .= ',80x80';
        } else {
            $thumbs = '80x80';
        }

        $image_type = isset($config['params']['type']) ? $config['params']['type'] : 'image';

        $info = Func::M('File')->upload($field, $image_type, $thumbs);
        if ($info['status'] != 0) {
            die('{"error":"' . $info['msg'] . '"}');
        } else {
            $ret = array(
                'error' => '',
                'fid'   => $info['data']['id'],
                'url'   => Func::getThumbUrl($info['data']['url'], '80x80'),
            );
            die(json_encode($ret));
        }
    }

    /**
     * 自动处理数据   用于列表数据处理, 默认自动处理带options配置的字段, 字段_o为原始值
     */
    protected function _format(&$row)
    {
        foreach ($row as $key => $value) {
            $arr = $this->getOptions($key);
            if (!empty($arr)) {
                $row[$key] = isset($arr[$value]) ? $arr[$value] : '';
                $row[$key . '_o'] = $value;
            }
        }
    }

}
