<?php

class JgPush
{
    protected static $instance = null;

    protected static function getInstance()
    {
        if (self::$instance === null) {
            require_once \Cf::getRootPath() . '/vendor/jpush/jpush/src/JPush/core/JPush.php';
            self::$instance = new JPush(Cf::C('JPUSH_KEY'), Cf::C('JPUSH_SECRET'), null);
        }

        return self::$instance;
    }

    /**
     * 广播
     * @param string $title 标题
     * @param array $data 附带数据
     * @return array|object
     */
    protected static function broadcast($title, $data = [], $type = 1)
    {
        $client = self::getInstance();
        if ($type == 1) {
            $result = $client->push()
                ->setPlatform(['ios', 'android'])
                ->addAllAudience()
                //->setNotificationAlert($title)
                ->setMessage($title, null, null, $data)
                ->setOptions(null, null, null, Cf::C('JPUSH_PRODUCTION'))
                ->send();
        } else {
            $result = $client->push()
                ->setPlatform(['ios', 'android'])
                ->addAllAudience()
                ->setNotificationAlert($title)
                ->setMessage($title, null, null, $data)
                ->setOptions(null, null, null, Cf::C('JPUSH_PRODUCTION'))
                ->send();
        }

        return $result;
    }

    /**
     * 发送单/多个用户消息 不能大于20个用户
     * @param $title
     * @param array $data
     * @param $uids
     * @return array|object
     */
    protected static function unicast($title, $data, $uids, $type = 1)
    {
        $client = self::getInstance();
        if ($type == 1) {
            $result = $client->push()
                ->setPlatform(['ios', 'android'])
                ->addTag($uids)
                //->addAndroidNotification($title, null, null, $data)
                //->addIosNotification($title, null, '+1', true, null, $data)
                ->setMessage($title, null, null, $data)
                ->setOptions(null, null, null, Cf::C('JPUSH_PRODUCTION'))
                ->send();
        } else {
            $result = $client->push()
                ->setPlatform(['ios', 'android'])
                ->addTag($uids)
                ->addAndroidNotification($title, null, null, $data)
                ->addIosNotification($title, null, '+1', true, null, $data)
                ->setMessage($title, null, null, $data)
                ->setOptions(null, null, null, Cf::C('JPUSH_PRODUCTION'))
                ->send();
        }

        return $result;
    }

    /**
     * 发送消息
     * @param $title
     * @param $data
     * @param array $uids
     * @param int $type 1 自定义消息，2 通知（会显示在提示栏，后台唤醒）
     * @return array|object
     */
    public static function send($title, $data, $uids = [], $type = 1)
    {
        try {
            $data['time'] = time();
            if (!empty($uids)) {
                foreach ($uids as &$uid) {
                    $uid = strval($uid);
                }

                $ret = self::unicast($title, $data, $uids, $type);
            } else {
                $ret = self::broadcast($title, $data, $type);
            }

            return $ret;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
