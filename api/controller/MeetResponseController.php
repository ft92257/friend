<?php

class MeetResponseController extends Controller
{
    protected $_authlevel = 0;//TODO 删除

    public function __construct()
    {
        parent::__construct();

        $this->model = MeetResponse::m();
    }

    protected function _add()
    {
        $this->assign('title', '回应');
        $form = new WepForm();
        $formHtml = '';
        $formHtml .= $form->getTextareaHtml('备注', 'info');

        $this->assign('formHtml', $formHtml);
        $this->display();
    }

    public function add()
    {
        if (!$this->isPost()) {
            $this->_add();
        }

        $meetId = (int) request('meet_id');
        $data = [
            'uid' => $this->uid,
            'meet_id' => $meetId,
            'day_expend' => request('day_expend'),
            'pay_type' => request('pay_type'),
            'love_type' => request('love_type'),
            'info' => request('info'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $this->model->addData($data);

        Meet::m()->updateCount(['id' => $meetId], 'response_count');

        $this->success('发送成功');
    }

    /**
     * 收到的回应
     */
    public function lists()
    {
        $meetId = (int) request('meet_id');
        $meet = Meet::m()->getById($meetId);
        if (empty($meet)) {
            $this->error('记录不存在！');
        }
        if ($meet['uid'] != $this->uid) {
            $this->error('非法操作！');
        }

        $data = $this->model->getAll([
            'where' => ['meet_id' => $meetId],
            'order' => 'created_at DESC',
            'limit' => Func::getPageLimit(request('page'), 10),
        ]);

        $this->success(lang(20000), $data);
    }

    public function detail()
    {
        $id = (int) request('id');
        $data = $this->model->getById($id);
        if (empty($data)) {
            $this->error('记录不存在！');
        }

        $meet = Meet::m()->getById($data['meet_id']);
        if ($meet['uid'] != $this->uid && $data['uid'] != $this->uid) {
            $this->error('非法操作！');
        }

        if ($data['is_accept']) {
            $fields = 'nickname,age,height,expend,province,city,info,avatar,images,weichat';
        } else {
            $fields = 'nickname,age,height,expend,province,city,info';
        }
        $user = User::m()->getById($data['uid'], 'id', $fields);

        $ret = [
            'data' => $data,
            'user' => $user,
        ];

        $this->success(lang(20000), $ret);
    }

    public function accept()
    {
        $id = (int) request('id');
        $data = Meet::m()->getById($id);
        if (empty($data)) {
            $this->error('记录不存在！');
        }
        $meet = Meet::m()->getById($data['meet_id']);
        if ($meet['uid'] != $this->uid) {
            $this->error('非法操作！');
        }

        $where = [
            'meet_id' => $data['meet_id'],
            'is_accept' => 1,
        ];
        if ($this->model->getCount($where) >= ceil($meet['response_count'] / 3)) {
            $this->error('只能翻看1/3的信息！');
        }

        $this->model->updateData(['is_accept' => 1], ['id' => $id]);

        $this->success('操作成功');
    }

    /**
     * 我发送的回应
     */
    public function my()
    {
        $data = $this->model->getAll([
            'where' => ['uid' => $this->uid, 'status' => 0],
            'order' => 'created_at DESC',
            'limit' => Func::getPageLimit(request('page'), 10),
        ], '_format_my');

        $this->success(lang(20000), $data);
    }

}
