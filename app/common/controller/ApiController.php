<?php
declare (strict_types = 1);

namespace app\common\controller;

use app\common\library\Aes;
use app\common\library\Attachment;
use app\common\model\Config as SiteConfig;
use app\common\model\User as UserModel;
use think\Request;
use app\BaseController;

class ApiController extends BaseController
{
    protected $noNeedLogin = ['login', 'getAesEncodeData', 'getAesDecodeData', 'getSiteInfo'];

    protected $userInfo = null;

    protected $UserModel = null;

    protected $model = null;

    protected $params = [];

    protected $token = null;

    protected $aes = null;

    protected $returnData = [
        'code' => 0,
        'msg' => '未知错误',
        'data' => [],
    ];

    protected function initialize()
    {
        parent::initialize();

        $this->aes = new Aes();
        $this->UserModel = UserModel::class;
        $this->params = $this->getRequestParams();
        $this->token = $this->params['token'] ?? null;
        $action = $this->request->action();

        if (!in_array($action, $this->noNeedLogin, true)) {
            $this->returnData['code'] = 5003;
            if (!$this->token) {
                $this->returnData['msg'] = '权限不足：未登录';
                $this->returnApiData();
            }

            $this->userInfo = UserModel::where('token', $this->token)->find();
            if (!$this->userInfo) {
                $this->returnData['msg'] = '用户不存在或未登录';
                $this->returnApiData();
            }

            if (!$this->userInfo->getData('status')) {
                $this->returnData['msg'] = lang('Account is locked');
                $this->returnApiData();
            }

            if ($this->userInfo->token_expire_time < time()) {
                $this->returnData['msg'] = '登录过期，请重新登录';
                $this->returnApiData();
            }
        }
    }

    public function getUserInfo()
    {
        return $this->userInfo->hidden(['password'])->toArray();
    }

    protected function isLogin()
    {
        return $this->userInfo;
    }

    /**
     * 输出结果集并退出程序
     */
    protected function returnApiData($msg = '')
    {
        if ($msg !== '') {
            $this->returnData['msg'] = $msg;
        }

        if ($this->returnData['data']) {
            $this->returnData['data'] = $this->aes->aesEncode(json_encode($this->returnData['data'], JSON_UNESCAPED_UNICODE));
        }

        response($this->returnData, 200, [], 'json')->send();
        exit;
    }

    /**
     * 获取加密的请求数据
     * @param string $param
     * @return mixed
     */
    protected function getRequestParams($param = 'params')
    {
        $data = $this->request->param($param);
//        if (!$data) {
//            $this->returnApiData('未提供正确的参数');
//        }

        if ($data) {
            return json_decode($this->aes->aesDecode($data), true);
        }

        return [];
    }

    /**
     * 获取加密后的用户数据
     */
    public function getAesEncodeData()
    {
        $this->returnData['data'] = $this->request->param();
        $this->returnData['code'] = 1;
        $this->returnData['msg'] = 'success';
        $this->returnApiData();
    }

    /**
     * 获取解密后的请求数据
     * @param string $param
     * @return \think\response\Json
     */
    public function getAesDecodeData($param = 'params')
    {
        $this->returnData['data'] = $this->getRequestParams($param);
        $this->returnData['msg'] = 'success';
        $this->returnData['code'] = 1;
        return json($this->returnData);
    }

    public function upload()
    {
        $site_watermark_engine = SiteConfig::getByKeyword('site_watermark_engine');
        $upload = (new Attachment())->upload('file', 'attachment', (bool)(int) $site_watermark_engine->value);

        if (!$upload) {
            $this->error('上传失败: 未找到文件');
        }

        $this->returnData['code'] = 1;
        $this->returnData['data']['savePath'] = $upload['savePath'];
        $this->returnData['msg'] = lang('Done');
        $this->returnApiData();
    }

    public function __call($method, $args)
    {
        $this->returnData['msg'] = '错误的请求[方法不存在]：' . $method;
        $this->returnApiData();
    }
}
