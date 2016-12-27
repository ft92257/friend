<?php

class LoginController extends Controller
{
    protected $_authlevel = 0;

    public function __construct()
    {
        parent::__construct();

        $this->model = \Login::m();
    }

    /**
     * 登录
     */
    public function login()
    {
        $username = request('username');
        $password = request('password');

        die('Hello World!');
        $this->success('admin ok');
    }


}
