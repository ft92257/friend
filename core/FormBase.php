<?php

abstract class FormBase
{
    public $_field;//字段名
    public $_value = '';//自定义变量值
    public $_params = array();//相关参数
    public $_attrs = '';//属性
    public $_options = array();//配置项
    private static $_objs = [];
    public static $_errorMessage = '';

    /**
     * 根据类型字段获取实例
     * @param $type
     * @return mixed
     */
    protected static function getInstance($type)
    {
        if (!isset(self::$_objs[$type])) {
            $className = ucfirst($type) . 'Form';
            include_once(dirname(__FILE__) . '/form/' . $className . '.php');
            self::$_objs[$type] = new $className();
        }

        return self::$_objs[$type];
    }

    /**
     * 获取表单提交的值
     * @param $config
     * @param $field
     * @param bool|false $edit
     * @return mixed
     */
    public static function getValue($config, $field, $edit = false)
    {
        $obj = self::getInstance($config[1]);
        $obj->_field = $field;
        $obj->_params = Func::KV($config, 'params', []);

        if (isset($config['process'])) {
            return call_user_func('Process::' . $config['process'], $obj->_getValue(), $edit);
        } else {
            return $obj->_getValue();
        }
    }

    /**
     * 初始化属性值
     * @param $config
     * @param $options
     * @param $field
     * @param $value
     * @return mixed
     */
    protected static function init($config, $options, $field, $value)
    {
        $obj = self::getInstance($config[1]);
        $obj->_field = $field;
        $obj->_value = $value;
        $obj->_options = $options;
        $obj->_params = Func::KV($config, 'params', []);
        $obj->_attrs = Func::KV($config, 'attrs', '');
        $obj->setDefaultAttr('ajaxValidate(this)', 'onblur');

        return $obj;
    }

    /**
     * 获取表单子项html
     * @param $config
     * @param $options
     * @param $field
     * @param $value
     * @return string
     */
    public static function getChildHtml($config, $options, $field, $value)
    {
        $obj = self::init($config, $options, $field, $value);

        return '&nbsp;&nbsp;&nbsp;&nbsp;' . $config[0] . '&nbsp;&nbsp;' . $obj->_getHtml();
    }

    /**
     * 获取表单html
     * @param $config
     * @param $options
     * @param $field
     * @param $value
     * @param string $children
     * @return mixed
     */
    public static function getHtml($config, $options, $field, $value, $children = '')
    {
        $obj = self::init($config, $options, $field, $value);

        return $obj->getDiv($config, $obj->_getHtml() . $children);
    }

    /**
     * 表单样式框架
     * @param $config
     * @param $input
     * @return string
     */
    public function getDiv($config, $input)
    {
        $s = '<dl>';
        $s .= '<dt><label>' . $config[0] . '：</label></dt>';
        $s .= '<dd>' . $input;
        if (isset($config[2])) {
            $s .= '<span class="help-block" data-default="' . $config[2] . '" default="' . $config[2] . '" >' . $config[2] . '</span>';
        } else {
            $s .= '<span class="help-block" data-default="" default="" style="display: none;"></span>';
        }
        $s .= '</dd></dl>';

        return $s;
    }

    /**
     * 表单获取值的默认方法
     * @param $field
     * @return string
     */
    public function _getValue()
    {
        return Func::R($this->_field);
    }

    /**
     * 获取表单默认html
     * @return string
     */
    public function _getHtml()
    {
        $this->setDefaultAttr('input-xlarge');

        return '<input type="text" ' . $this->_attrs . ' name="' . $this->_field . '" value="' . $this->_value . '" />';
    }

    /**
     * 设置控件属性
     * @param $class
     * @param string $cssName
     */
    public function setDefaultAttr($class, $cssName = 'class')
    {
        if (stripos($this->_attrs, ' ' . $cssName . '=') === false) {
            //attrs未定义class
            $this->_attrs .= ' ' . $cssName . '="' . $class . '"';
        }
    }

    /**
     * 获取搜索html
     * @param $config
     * @param $options
     * @param $field
     * @param string $value
     * @param string $children
     * @return mixed
     */
    public static function getSearchHtml($config, $options, $field, $value = '', $children = '')
    {
        $obj = self::init($config, $options, $field, $value);

        return $obj->getSearchDiv($config, $obj->_getSearchHtml() . $children);
    }

    /**
     * 搜索html模板
     * @param $config 配置项值
     * @param $input  子类对应控件类型生成的html
     * @return string
     */
    public function getSearchDiv($config, $input)
    {
        $html = '<dl>
					<dt><label>' . $config[0] . '</label></dt>
					<dd>' . $input . '</dd>
				 </dl>';

        return $html;
    }

    /**
     * 获取搜索提交值，where数组形式
     * @param $config
     * @param $field
     * @return mixed
     */
    public static function getSearchValue($config, $field)
    {
        $obj = self::getInstance($config[1]);
        $obj->_field = $field;
        $obj->_params = Func::KV($config, 'params', []);

        return $obj->_getSearchValue();
    }

    /**
     * 默认获取搜索值方法
     */
    public function _getSearchValue()
    {
        $value = Func::request($this->_field);

        return $value !== '' ? [$this->_field => $value] : [];
    }
}