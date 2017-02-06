<?php

class IndexController extends Controller
{
    protected $_authlevel = 0;

    public function __construct()
    {
        parent::__construct();

        $this->model = Meet::m();
    }

    /**
     * 首页 TODO
     */
    public function index()
    {
        $banner = [];//广告图片
        $users = [];//推荐示范用户
        $ret = [
            'banner' => $banner,
            'users' => $users,
        ];

        $this->success(lang(20000), $ret);
    }

    public function meetList()
    {
        $user = \User::m()->getById($this->uid);
        //TODO 筛选条件
        $data = $this->model->getAll([
            'where' => ['status' => 0, 'find_sex' => $user['sex']],
            'order' => 'created_at DESC',
            'limit' => Func::getPageLimit(request('page'), 10),
        ]);

        $this->success(lang(20000), $data);
    }

    public function meetDetail()
    {
        $id = (int) request('id');
        $data = $this->model->getById($id);
        if (empty($data)) {
            $this->error('没有该记录！');
        }
        $data['user'] = User::m()->getById($data['uid'], 'id', 'nickname,age,height,expend,province,city,info');

        $this->success(lang(20000), $data);
    }
}
