<?php

/**
 * Session模型
 *
 */
class Session extends ApiBaseModel
{

    protected $_expire = 2592000;//有效期 一个月

    protected $_tableName = 'tb_session';

    protected function getKey()
    {
        return Func::R('SN_API');
    }

    /*
     * 验证key是否有效，有效则返回uid，否则返回false
     */
    public function check()
    {
        $key = $this->getKey();
        if (empty($key)) {
            return false;
        }

        $data = Func::getCache('api_session', $key);
        if (!$data) {
            $data = $this->getById($key, 'key');
            $updateExpire = true;
        } else {
            $updateExpire = false;
        }

        if (empty($data)) {
            return false;
        }

        if (($data['expire'] + $this->_expire) < time()) {
            return false;
        }
        //ip变化
        /*
        $ips = explode(',', $data['ip']);
        if (!in_array(Func::get_client_ip(),  $ips)) {
            return false;
        }*/

        //更新有效期
        if ($updateExpire) {
            $set = ['expire' => time() + $this->_expire];
            $this->updateData($set, array('id' => $data['id']));
            Func::setCache('api_session', $key, array_merge($data, $set), 604800, 20000);//缓存7天，不能大于$_expire值
        }

        return $data['uid'];
    }

    /*
     * 生成sessionKey
     */
    public function setKey($uid)
    {
        $key = $uid . time() . mt_rand();
        $key = md5($key);

        $data = array(
            'key'    => $key,
            'expire' => time() + $this->_expire,
            'ip'     => Func::get_client_ip(),
        );

        /*
        //支持多点登录
        $data['uid'] = $uid;
        $this->addData($data);*/

        $session = $this->getById($uid, 'uid');
        if (empty($session)) {
            $data['uid'] = $uid;
            $data['token'] = '';
            $this->addData($data);
        } else {
            /*
            $ips = explode(',', $session['ip']);
            if (!in_array($data['ip'], $ips)) {
                if (count($ips) >= 5) {
                    array_unshift($ips, $data['ip']);
                    array_pop($ips);
                    $data['ip'] = join(',', $ips);
                } else {
                    $data['ip'] = $data['ip'] . ',' . $session['ip'];
                }
            } else {
                unset($data['ip']);
            }*/
            //已存在且未过期，则直接使用旧的key
            if (($session['expire'] + $this->_expire) > time()) {
                $key = $session['key'];
                unset($data['key']);
            }

            $this->updateData($data, array('uid' => $uid));
        }

        return $key;
    }

    /*
     * 退出登录
     */
    public function destroy()
    {
        $key = $this->getKey();
        $this->updateData(array('expire' => 0), array('key' => $key));
    }

}


?>