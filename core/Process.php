<?php

/**
 * 处理数据类库
 */

class Process
{
    public static function int($value, $edit)
    {
        return (int) $value;
    }

    public static function noValueKeep($value, $edit)
    {
        if ($edit && !$value) {
            return false;
        } else {
            return $value;
        }
    }

}

?>