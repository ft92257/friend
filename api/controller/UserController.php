<?php

class UserController extends Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->model = User::m();
    }

    public function getOptions()
    {
        //TODO
    }

    public function detail()
    {
        $data = $this->model->getById($this->uid);
        unset($data['password']);

        $this->success(lang(20000), $data);
    }

    public function edit()
    {
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
            'province' => request('province'),
            'city' => request('city'),
            'nickname' => request('nickname'),
            'avatar' => $avatar,
            'age' => request('age'),
            'height' => request('height'),
            'expend' => request('expend'),
            'images' => $images,
            'info' => request('info'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($avatar) {
            $data['avatar'] = $avatar;
        }
        if ($images) {
            $data['images'] = $images;
        }

        $this->model->updateData($data, ['id' => $this->uid]);

        $this->success('修改成功！');
    }


}
