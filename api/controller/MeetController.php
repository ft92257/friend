<?php

class MeetController extends Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->model = Meet::m();
    }

    public function getOptions()
    {
        //TODO
    }

    public function add()
    {
        $data = [
            'uid' => $this->uid,
            'title' => request('title'),
            'time_limit' => request('time_limit'),
            'day_expend' => request('day_expend'),
            'pay_type' => request('pay_type'),
            'love_type' => request('love_type'),
            'info' => request('info'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $this->model->addData($data);

        $this->success('发布成功');
    }

    public function edit()
    {
        $id = (int) request('id');
        $data = [
            'title' => request('title'),
            'time_limit' => request('time_limit'),
            'day_expend' => request('day_expend'),
            'pay_type' => request('pay_type'),
            'love_type' => request('love_type'),
            'info' => request('info'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $this->model->updateData($data, ['id' => $id, 'uid' => $this->uid]);

        $this->success('修改成功');
    }

    public function delete()
    {
        $id = (int) request('id');
        $this->model->deleteData(['id' => $id, 'uid' => $this->uid]);

        $this->success('删除成功');
    }

    /**
     * 我发的邀请
     */
    public function my()
    {
        $data = $this->model->getAll([
            'where' => ['uid' => $this->uid, 'status' => 0],
            'order' => 'created_at DESC',
            'limit' => Func::getPageLimit(request('page'), 10),
        ]);

        $this->success(lang(20000), $data);
    }

}
