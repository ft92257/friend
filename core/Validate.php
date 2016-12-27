<?php

/*
 * 验证类
 * 所有方法都带3个参数 字段名称$key, $data 键值对数组, 验证规则数组$aValidate
 * $aValidate[0] 字段名,string $aValidate[1] 方法名，string $aValidate[2] 错误提示消息
 * array $aValidate[3] 其他参数, array $aValidate[4] 功能控制参数
 */

class Validate
{

    public static $_params = [];

    /*
     * 不能为空
     */
    public static function required($value)
    {
        if (empty($value)) {
            if (isset(self::$_params['zero']) && $value === '0') {
                //0 为 true
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * 正则
     * @param $value
     * @return bool
     */
    public static function preg($value)
    {
        if (preg_match("/" . self::$_params['preg'] . "/", $value)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 英文字符
     * @param $value
     * @return bool
     */
    public static function enword($value)
    {
        $len = isset(self::$_params['len']) ? self::$_params['len'] : '4,32';
        if (preg_match("/^[a-zA-Z0-9_@\.]{" . $len . "}$/", $value)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 非空字符
     * @param $value
     * @return bool
     */
    public static function word($value)
    {
        if (preg_match("/^[\w]{4,50}$/", $value)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 中文字符
     * @param $value
     * @return bool
     */
    public static function cnword($value)
    {
        $len = isset(self::$_params['len']) ? self::$_params['len'] : '1,32';
        if (preg_match("/^[\x{4e00}-\x{9fa5}A-Za-z0-9]{" . $len . "}$/u", $value)) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * email
     */
    public static function email($value)
    {
        if (preg_match("/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/", $value)) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * 手机号
     */
    public static function mobile($value)
    {
        if (preg_match("/^1[34587]\d{9}$/", $value)) {
            return true;
        } else {
            return false;
        }
    }

}

?>