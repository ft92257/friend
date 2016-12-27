<?php

class Controller extends ApiController
{

    public function __construct()
    {
        //加密判断，上线时恢复
        /*
        if (!($_GET['c'] == 'other' && $_GET['a'] == 'begin')) {
            $token = strtolower(\Func::R('TOKEN'));
            $captcha = substr($token, 0, 32);
            $time = substr($token, 32);
            if (md5('hdkCMA!qxH8Qv&IVZHEn2ar#oqCW!%mM' . $time) != $captcha) {
                $this->error(\Cf::lang(50016));
            }
            if ($time < (time() - 60) || $time > (time() + 60)) {
                $this->error(\Cf::lang(50033));
            }
        }*/

        //加载用户数据
        $uid = \Session::m()->check();
        //$uid = 1;//test
        if ($this->_authlevel >= 1 && !$uid) {
            $this->noLoginError();
        }
        if ($uid) {
            $this->uid = $uid;
            \Func::setGlobal('uid', $this->uid);
        }
    }

    /**
     * 未登录处理
     */
    public function noLoginError()
    {
        $this->error('请先登录!', 1021);
    }

    /**
     * 是否登录
     * @return bool|int
     */
    public function isLogin()
    {
        return $this->uid;
    }

    /**
     * 加入PUSH队列
     * @param $params
     * @return int
     */
    public function listPush($params)
    {
        return \Func::listPush('push_list', $params);
    }

    /**
     * 发送消息
     * @param $data
     * @param array $uids
     * @param bool $log
     * @param int $msgType 1 自定义消息，2 通知（会显示在提示栏，后台唤醒）
     */
    public function sendPush($data, $uids = [], $log = true, $msgType = 1)
    {
        if ($log) {
            $newid = \PushLog::m()->addData([
                'uid' => $this->uid,
                'users' => join(',', $uids),
                'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $data['id'] = strval($newid);
        } else {
            $data['id'] = mt_rand();
        }

        if (\Func::$_cacheOpen) {
            $params = [
                'data' => $data,
                'users' => $uids,
                'uid' => $this->uid,
                'msgType' => $msgType,
            ];
            $this->listPush($params);

        } else {
            \JgPush::send($data['title'], $data, $uids, $msgType);
        }

        return true;
    }

    /**
     * 发送系统消息
     * @param $msg
     * @param array $uids
     */
    public function sendSysMsg($msg, $uids = [])
    {
        return $this->sendPush([
            'title'   => $msg,
            'message' => $msg,
            'type'    => 9,
            'data'    => ['mid' => 0],
        ], $uids, false);
    }

}
