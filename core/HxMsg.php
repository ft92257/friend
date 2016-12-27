<?php
/**
 * 环信消息发送类
 * Class HxMsg
 */
class HxMsg
{
    /**
     * 发送消息
     * @param string $action 动作
     * @param array $bodyArr 数组
     * @return bool
     * @throws Exception
     */
    public static function send($action, $bodyArr)
    {
        $curl = new Curl();
        $curl->init();
        $curl->setOption(CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $url = 'https://a1.easemob.com' . Cf::C('HX_PATH') . '/token';
        $data = [
            'grant_type'    => 'client_credentials',
            'client_id'     => Cf::C('HX_CLIENT_ID'),
            'client_secret' => Cf::C('HX_CLIENT_SECRET'),
        ];

        $result = $curl->post($url, json_encode($data));
        $arr = json_decode($result, true);
        if (isset($arr['access_token'])) {
            $token = $arr['access_token'];
        } else {
            $curl->close();

            return false;
        }

        $url = 'https://a1.easemob.com' . Cf::C('HX_PATH') . '/' . $action;
        $curl->init();
        $curl->setOption(CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
        ]);
        $result = $curl->post($url, json_encode($bodyArr));
        $arr = json_decode($result, true);
        //print_r($arr);
        $curl->close();
        if (isset($arr['entities'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 发送命令行消息
     * @param $uids
     * @param $data
     * @return bool
     */
    public static function sendCmd($uids, $data)
    {
        if (!is_array($uids)) {
            $uids = [$uids];
        }

        foreach ($uids as &$uid) {
            $uid = 'renmai' . $uid;
        }

        $action = 'messages';
        $bodyArr = [
            'target_type' => 'users',
            'target' => $uids,
            'msg' => [
                'type' => 'cmd',
                'action' => 'action1',
            ],
            'ext' => Func::formatReturnData($data),
        ];

        return self::send($action, $bodyArr);
    }
}
