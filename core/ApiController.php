<?php

class ApiController
{
    //当前模型
    public $model;
    //返回函数
    protected $_ret_func = '_ret_api';
    //权限验证级别 2:需验证权限, 1:只需验证登录, 0不需验证
    protected $_authlevel = 1;
    protected $_success_status = 200;
    protected $uid = 0;

    /**
     * 是否POST
     * @return bool
     */
    public function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }

    /**
     * 返回正确结果
     * @param string $msg
     * @param array $data
     * @param array $extra 额外字段，可以在有新需求时不改动data的数据结构
     */
    public function success($msg = 'Success!', $data = array(), $extra = null)
    {
        $this->result(true, $msg, 20000, $data, $extra);
    }

    /**
     * 返回错误信息
     * @param string $msg
     * @param int $code
     * @param array $data
     */
    public function error($msg = 'Fail!', $code = 50000, $data = array())
    {
        if ($code == 50000 && isset($GLOBALS['RESULT_CODE']) && $GLOBALS['RESULT_CODE']) {
            $code = $GLOBALS['RESULT_CODE'];
        }
        $this->result(false, $msg, $code, $data);
    }

    /**
     * 返回结果
     * @param bool|false $done
     * @param string $msg
     * @param int $code
     * @param array $retval
     * @param array $extra 额外字段，可以在有新需求时不改动data的数据结构
     */
    protected function result($done = false, $msg = '', $code = 50000, $retval = null, $extra = null)
    {
        $ret = [
            'done'   => $done,
            'msg'    => $msg,
            'code'   => $code,
            'retval' => $retval,
            'extra'  => $extra,
        ];
        if (is_array($ret['retval'])) {
            if (empty($ret['retval'])) {
                $ret['retval'] = null;
            } else {
                $ret['retval'] = \Func::formatReturnData($ret['retval']);
            }
        }

        if (is_array($ret['extra'])) {
            $ret['extra'] = \Func::formatReturnData($ret['extra']);
        }

        header('Content-type: application/json');
        die(json_encode($ret, JSON_UNESCAPED_UNICODE));
    }

}
