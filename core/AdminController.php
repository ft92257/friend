<?php

class AdminController
{
    //当前模型
    public $model;
    //返回函数
    protected $_ret_func = '_ret_alert';
    //权限验证级别 2:需验证权限, 1:只需验证登录, 0不需验证
    protected $_authlevel = 1;
    protected $_success_status = 20000;
    protected $uid = 0;
    protected $layout = 'admin';
    protected $_assign = [];

    public function redirect($url)
    {
        header("Location:" . $url);
        exit;
    }

    public function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }

    public function success($msg = 'Success!', $url = '', $data = array())
    {
        $this->showMessage($msg, 20000, $data, $url);
    }

    public function error($msg = 'Fail!', $url = '', $data = array())
    {
        $this->showMessage($msg, 50000, $data, $url);
    }

    public function multiAssign($arr)
    {
        $this->_assign = array_merge($this->_assign, $arr);
    }

    public function assign($key, $value)
    {
        $this->_assign[$key] = $value;
    }

    public function display($template = '', $app = '')
    {
        //$view = ($app ? $app : CONTROLLER) . '.' . ($template ? $template : ACTION);
        //echo view($view, $this->_assign);

        include_once(Cf::getRootPath() . '/core/BaseView.php');
        $view = ($app ? $app : $this->app) . '/' . ($template ? $template : $this->action);
        BaseView::$cacheOpen = Cf::C('template_cache');

        BaseView::display($view, $this->_assign, $this->layout);
    }

    protected function showMessage($msg, $status, $data = array(), $url = '')
    {
        if ($this->_ret_func != '') {
            $ret = $this->_ret_func;

            return $this->$ret($msg, $status, $data, $url);
        }

        $this->layout = 'message';
        $this->assign('msg', $msg);
        $this->assign('url', $url);
        $this->assign('url_link', $url ? $url : 'javascript:history.back(-1)');

        if ($status == $this->_success_status) {
            $this->display('success', 'public');
        } else {
            $this->display('error', 'public');
        }
        exit;
    }

    /**
     * 获取数据列表，不分页
     */
    protected function _getList($params = array())
    {
        $params['templete'] = isset($params['templete']) ? $params['templete'] : '';
        $params['where'] = isset($params['where']) ? $params['where'] : array();
        $where = $this->model->getSearchCondition();
        $params['where'] = array_merge($where, $params['where']);

        $data = $this->model->getAll($params, true);
        $data = array_merge($data, Func::KV($params, 'merge', []));

        $listHtml = $this->model->getListHtml($data);
        $this->assign('listHtml', $listHtml);

        $searchHtml = $this->model->getSearchHtml();
        $this->assign('searchHtml', $searchHtml);

        $this->display($params['templete']);
    }

    /**
     * 获取数据分页列表
     */
    protected function _getPageList($params = array())
    {
        $params['templete'] = isset($params['templete']) ? $params['templete'] : '';
        $params['pagesize'] = isset($params['pagesize']) ? $params['pagesize'] : 10;

        $params['where'] = isset($params['where']) ? $params['where'] : array();
        $where = $this->model->getSearchCondition();
        $params['where'] = array_merge($where, $params['where']);

        if ($this->isPost()) {
            unset($_GET['p']);
            unset($_GET['_c']);
        }
        if (isset($params['not_save_count'])) {
            $count = $this->model->getCount($params['where']);
        } else {
            if (isset($_GET['_c'])) {
                $count = (int)$_GET['_c'];
                $count = max(1, $count);
            } else {
                $count = $this->model->getCount($params['where']);
                $_GET['_c'] = $count;
            }
        }
        $Page = new Page($count, $params['pagesize']);
        if ($Page->totalPages > 1) {
            $this->assign('page', $Page->show());
        } else {
            $this->assign('page', '');
        }
        $params['limit'] = $Page->firstRow . ',' . $Page->listRows;

        $data = $this->model->getAll($params, true);

        $listHtml = $this->model->getListHtml($data);
        $this->assign('listHtml', $listHtml);

        $searchHtml = $this->model->getSearchHtml();
        $this->assign('searchHtml', $searchHtml);

        $this->display($params['templete']);
    }

    /**
     * 检查token
     * @return boolean
     */
    protected function _checkToken()
    {
        return true;
        /*
        if ($this->_authlevel == 0) {
            return true;
        }

        return Func::M('Session')->checkToken($this->oUser, Func::request('_token'));*/
    }

    /**
     * 添加操作
     */
    protected function _add($dataBase = array(), $returl = '')
    {
        if (!$this->_checkToken()) {
            $this->error('表单已过期,请刷新重试!', $returl);
        }

        $data = $this->model->getFormData();
        if (!empty($dataBase)) {
            $data = array_merge($data, $dataBase);
        }

        if (!$this->model->errorMessage() && $this->model->checkData($data)) {
            $newid = $this->model->addData($data);
            if ($newid) {
                $data['id'] = $newid;
                $this->_after_add($data);
                $this->success('添加成功！', $returl, $data);
            } else {
                $this->error('数据库添加失败!', $returl);
            }
        } else {
            $this->error($this->model->errorMessage(), $returl);
        }
    }

    protected function _after_add($data)
    {

    }

    /**
     * 显示详情
     */
    protected function _detail($data = array(), $template = '')
    {
        $this->assign('data', $data);
        $this->assign('detailHtml', $this->model->getDetailHtml($data));
        $this->display($template);
    }

    /**
     * 编辑,自定义返回函数
     * @param  $where
     * @param  $dataBase
     * @param  $ret_func
     */
    protected function _edit($where, $dataBase = array(), $returl = '')
    {
        $returl = $returl ? $returl : Func::KV($_SERVER, 'HTTP_REFERER');

        //if (!$this->_checkToken()) {
        //    $this->error('表单已过期,请刷新重试!', $returl);
        //}

        $data = $this->model->getFormData(true);
        if (is_array($dataBase) && !empty($dataBase)) {
            $data = array_merge($data, $dataBase);
        }
        if (empty($data)) {
            $this->success('更新成功！');
        }

        if (!$this->model->errorMessage() && $this->model->checkData($data, false)) {
            if ($this->model->updateData($data, $where)) {
                $this->_after_edit($data);
            }

            return $this->success('更新成功！', $returl, $data);
        } else {
            $this->error($this->model->errorMessage(), $returl);
        }
    }

    protected function _after_edit($data)
    {

    }

    /**
     * 删除记录
     */
    protected function _delete($where, $real = false)
    {
        /*
         if (!$this->_checkToken()) {
        return $this->showMessage(-3, '表单已过期,请刷新重试!', array(), $_SERVER["HTTP_REFERER"]);
        }*/

        if ($this->model->deleteData($where, $real)) {
            $this->_after_delete($where);
            $this->success('删除成功!', Func::KV($_SERVER, 'HTTP_REFERER'));
        } else {
            $this->error('删除失败!', Func::KV($_SERVER, 'HTTP_REFERER'));
        }
    }

    protected function _after_delete($where)
    {

    }

    /**
     * 输出表单模版
     */
    protected function _display_form($data = array(), $template = '')
    {
        $this->assign('data', $data);
        $this->assign('formHtml', $this->model->getFormHtml($data));
        $this->display($template);
    }

    /**
     * 返回函数  不刷新页面提示
     * @param  $status
     * @param  $msg
     * @param  $data
     * @param  $returl
     */
    protected function _ret_iframe($msg, $status, $data, $returl)
    {
        echo "<script>if(parent.document.getElementById('btn_submit')) parent.document.getElementById('btn_submit').disabled=false;</script>";
        if ($status == $this->_success_status) {
            if ($returl) {
                die("<script>alert('$msg');parent.location.href='$returl';</script>");
            } else {
                die("<script>alert('$msg');</script>");
            }
        } else {
            die("<script>alert('$msg');</script>");
        }
    }

    /**
     * 返回函数
     * @param  $status
     * @param  $msg
     * @param  $data
     * @param  $returl
     */
    protected function _ret_alert($msg, $status, $data, $returl)
    {
        $ret = "<script>";
        $ret .= "alert('" . $msg . "');";
        if ($returl && $status == $this->_success_status) {
            $ret .= "window.location.href = '$returl';";
        } else {
            $ret .= "history.back(-1);";
        }

        die($ret . "</script>");
    }

    protected function _ret_notip($msg, $status, $data, $returl)
    {
        $ret = "<script>";
        if ($returl) {
            $ret .= "window.location.href = '$returl';";
        } else {
            $ret .= "history.back(-1);";
        }

        die($ret . "</script>");
    }

    /**
     * 返回函数 jsonp格式
     * @param  $status
     * @param string $msg
     * @param  $data
     * @param string $returl
     */
    protected function _ret_jsonp($msg, $status, $data, $returl)
    {
        $ret = array(
            'status' => $status,
            'msg'    => $msg,
            'data'   => $data,
        );
        $func = R('callback');
        die($func . '(' . json_encode($ret) . ')');
    }

    /**
     * 返回函数 json格式
     * @param string $msg
     * @param int $status
     * @param array $data
     * @param string $returl
     */
    protected function _ret_ajax($msg, $status, $data, $returl)
    {
        $ret = array(
            'status' => $status,
            'msg'    => $msg,
            'data'   => $data,
        );
        die(json_encode($ret));
    }

    /**
     * ajax验证接口
     * @param _NAME 验证单个字段是否合法，验证规则在model层$aValidate设置
     * html用法： <input type="text" name="account" onblur="ajaxValidate(this)" />
     */
    public function autoValidateAction()
    {
        $field = Func::request('FIELD');
        $value = Func::request('VALUE');

        $ret = $this->model->autoValidate(array($field => $value));
        $this->_ret_ajax($ret['msg'], $ret['status'], [], '');
    }

    /**
     * 文件上传
     */
    public function ajaxUploadAction()
    {
        $this->model->ajaxUpload();
    }
}