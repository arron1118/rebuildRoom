<?php
declare (strict_types = 1);

namespace app\common\controller;

use app\common\model\Admin;
use app\common\library\Attachment;
use think\Request;
use app\BaseController;
use app\common\middleware\Check;

class AdminController extends BaseController
{
    protected $middleware = [Check::class];

    protected $model = null;

    protected $userInfo = null;

    protected $userType = 'admin';

    protected $returnData = [];

    protected function initialize()
    {
        parent::initialize();

        $this->returnData = [
            'code' => 0,
            'msg' => lang('Unknown error'),
            'data' => []
        ];

        $token = $this->request->cookie('RB_TOKEN');
        if ($token) {
            $this->userInfo = Admin::where('token', $token)->find();
        }
        $this->view->assign('userInfo', $this->userInfo);
    }

    public function error($msg = '')
    {
        if ($msg) {
            $this->returnData['msg'] = $msg;
        }

        $this->returnData['data']['token'] = $this->request->buildToken();

        response($this->returnData, 200, [], 'json')->send();
        exit;
    }

    public function success($msg = '')
    {
        if ($msg) {
            $this->returnData['msg'] = $msg;
        } else {
            $this->returnData['msg'] = lang('Done');
        }

        response($this->returnData, 200, [], 'json')->send();
        exit;
    }

    public function upload()
    {
        $upload = (new Attachment())->upload('file');

        if (!$upload) {
            $this->error('上传失败: 未找到文件');
        }

        $this->returnData['code'] = 1;
        $this->returnData['data']['savePath'] = $upload['savePath'];
        $this->success(lang('Done'));
    }
}
