<?php

class LoginController extends Controller
{
    protected $_authlevel = 0;

    public function __construct()
    {
        parent::__construct();

        $this->model = User::m();
    }


    /**
     * 登录
     */
    public function login()
    {
        $username = request('username');
        $password = request('password');
        if (!$username) {
            $this->error('用户名不能为空！');
        }

        $ret = $this->model->login($username, $password);
        if ($ret) {
            $this->success(lang(20001), $ret);
        } else {
            $this->error(lang($this->model->errorCode()), $this->model->errorCode());
        }
    }

    public function register()
    {
        $username = request('username');
        if ($this->model->isExists(['username' => $username])) {
            $this->error('该用户名已被人使用啦！');
        }
        $weichat = request('weichat');
        if ($this->model->isExists(['weichat' => $weichat])) {
            $this->error('该微信号已被人使用啦！');
        }

        if (\File::m()->checkUpload('avatar')) {
            $info = \File::m()->upload('avatar', 'image');
            if ($info['status'] != 0) {
                $this->error(lang(50051) . $info['msg']);
            }
            $avatar = $info['data']['url'];
        } else {
            $avatar = '';
        }

        if (\File::m()->checkUpload('imageFiles')) {
            $urls = \File::m()->multiUpload('imageFiles', 'image');
            $images = join(',', $urls);
        } else {
            $images = '';
        }

        $data = [
            'username' => $username,
            'password' => request('password'),
            'sex' => (int)request('sex'),
            'province' => (int)request('province'),
            'city' => (int)request('city'),
            'weichat' => $weichat,
            'nickname' => request('nickname'),
            'avatar' => $avatar,
            'birthday' => request('birthday'),
            'height' => (int)request('height'),
            'expend' => (int)request('expend'),
            'images' => $images,
            'info' => request('info'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $ret = $this->model->register($data);
        if ($ret) {
            $this->success(lang(20002), $ret);
        } else {
            $this->error(lang($this->model->errorCode()), $this->model->errorCode());
        }
    }

}
