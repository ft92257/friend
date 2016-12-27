<?php

class Controller extends AdminController
{

    public function __construct()
    {
        //加载用户数据
        if ($this->_authlevel >= 1 && empty($_SESSION['user'])) {
            $this->noLoginError();
        }

        if (!empty($_SESSION['user'])) {
            //权限处理
            $allowApps = [
                'knowledge' => ['knowledge', 'login', 'richtext'],
            ];
            if (isset($allowApps[$_SESSION['user']['user_name']])) {
                $apps = $allowApps[$_SESSION['user']['user_name']];
                if (!in_array(CONTROLLER, $apps)) {
                    $this->error('没有操作权限！');
                }
            }

            $this->assign('tabs', $this->getTabs());
        }
    }

    protected function getTabs()
    {
        if ($_SESSION['user']['user_name'] == 'knowledge') {
            return '<li><div><a href="/knowledge/index" data-match="/knowledge/" data-opt=""><span>知识库管理</span></a></div></li>';
        }

        return '<li><div><a href="/user/index" data-match="/user/" class="" data-opt=""><span>用户管理</span></a></div></li>
                <li><div><a href="/report/index" data-match="/report/" class="" data-opt=""><span>举报管理</span></a></div></li>
                <li><div><a href="/dynamic/index?status=0" data-match="/dynamic/" class="" data-opt=""><span>动态管理</span></a></div></li>
                <li><div><a href="/feedback/index" data-match="/feedback/" class="" data-opt=""><span>问题反馈</span></a></div></li>
                <li><div><a href="/group/index" data-match="/group/" class="" data-opt=""><span>圈儿管理</span></a></div></li>
                <li><div><a href="/version/index" data-match="/version/" class="" data-opt=""><span>版本管理</span></a></div></li>
                <li><div><a href="/company/index" data-match="/company/" class="" data-opt=""><span>公司管理</span></a></div></li>
                <li><div><a href="/sku/index" data-match="/sku/" class="" data-opt=""><span>核心SKU</span></a></div></li>
                <li><div><a href="/message/add" data-match="/message/" class="" data-opt=""><span>系统消息</span></a></div></li>
                <li><div><a href="/invite/index" data-match="/invite/" class="" data-opt=""><span>下载统计</span></a></div></li>
                <li><div><a href="/banner/index" data-match="/banner/" class="" data-opt=""><span>广告管理</span></a></div></li>
                <li><div><a href="javascript:;"><i class="system-folder-collapse"></i><span>文章管理</span></a></div>
                    <ul>
                        <li><div><a href="/article/about" data-match="/article/about" data-opt=""><span>关于我们</span></a></div></li>
                        <li><div><a href="/article/service" data-match="/article/service" data-opt=""><span>服务条款</span></a></div></li>
                        <li><div><a href="/article/law" data-match="/article/law" data-opt=""><span>法律申明</span></a></div></li>
                    </ul>
                </li>
                <li><div><a href="/knowledge/index" data-match="/knowledge/" data-opt=""><span>知识库管理</span></a></div></li>';
    }

    public function noLoginError()
    {
        $script = '<script>top.location.href = "/login/login";</script>';
        die($script);
        //$this->redirect(Func::U('login', 'login'));
        //$this->error('请先登录!', Func::U('login', 'login'));
    }

    public function isLogin()
    {
        return !empty($_SESSION['user']);
    }

    public function sendPush($data, $uids = [], $log = true, $msgType = 1)
    {
        if ($log) {
            $newid = \PushLog::m()->addData([
                'uid' => 0,
                'users' => join(',', $uids),
                'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $data['id'] = strval($newid);
        } else {
            $data['id'] = mt_rand();
        }

        if (\Func::$_cacheOpen) {
            $params = [
                'data' => $data,
                'users' => $uids,
                'uid' => 0,
                'msgType' => $msgType,
            ];
            $this->listPush($params);

        }

        return true;
    }

    public function listPush($params)
    {
        return Func::listPush('push_list', $params);
    }

}