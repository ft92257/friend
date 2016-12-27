<?php

class User extends Model
{
    protected $_tableName = 'tb_user';

    public function login($username, $password)
    {
        $user = $this->getById($username, 'username');
        if (empty($user)) {
            return $this->errorCode(50001);
        }

        if (!password_verify($password, $user['password'])) {
            return $this->errorCode(50002);
        }

        $key = Session::m()->setKey($user['id']);

        return $this->getReturnInfo($user['id'], $key);
    }

    public function register($data)
    {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        if (!$data['nickname']) {
            $data['nickname'] = ($data['sex'] == 1 ? 'boy' : 'girl') . '_' . mt_rand(100000, 999999);
        }

        $uid = $this->addData($data);
        $key = Session::m()->setKey($uid);

        return $this->getReturnInfo($uid, $key);
    }

    protected function getReturnInfo($uid, $key)
    {
        $ret = [
            'uid'      => $uid,
            'SN_API'   => $key,
        ];

        return $ret;
    }

}

?>