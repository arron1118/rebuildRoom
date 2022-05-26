<?php
declare (strict_types = 1);

namespace app\common\controller;

use app\common\library\Aes;
use app\common\model\User as UserModel;
use think\Request;
use app\BaseController;

class ApiController extends BaseController
{
    protected $noNeedLogin = ['login', 'getAesEncodeData', 'getAesDecodeData', 'getSiteInfo'];

    protected $userInfo = null;

    protected $userType = 'user';

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
        $this->userType = $this->params['userType'] ?? null;
        $this->token = $this->params['token'] ?? null;
        $action = $this->request->action();

        if (!in_array($action, $this->noNeedLogin, true)) {
            $this->returnData['code'] = 5003;
            if (!$this->token) {
                $this->returnData['msg'] = '权限不足：未登录';
                $this->returnApiData();
            }

            if (!$this->userType) {
                $this->returnData['code'] = 0;
                $this->returnData['msg'] = '未提供正确的参数：userType';
                $this->returnApiData();
            }

            if ($this->userType === 'user') {
                $this->userInfo = UserModel::where('token', $this->token)->find();

                if ($this->userInfo && $this->userInfo->getData('is_test') && $this->userInfo->getData('test_endtime') < time()) {
                    $this->userInfo->status = 0;
                    $this->userInfo->save();
                }
            } else if ($this->userType === 'company') {
                $this->userInfo = CompanyModel::where('token', $this->token)->find();
            }

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
        return $this->userInfo->hidden(['salt', 'password'])->toArray();
    }

    protected function isLogin()
    {
        return $this->userInfo;
    }

    /**
     * 输出结果集并退出程序
     */
    protected function returnApiData()
    {
        $this->returnData['data'] = $this->aes->aesEncode(json_encode($this->returnData['data'], JSON_UNESCAPED_UNICODE));
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
        return json_decode($this->aes->aesDecode($data), JSON_UNESCAPED_UNICODE);
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

    public function __call($method, $args)
    {
        $this->returnData['msg'] = '错误的请求[方法不存在]：' . $method;
        $this->returnApiData();
    }
}