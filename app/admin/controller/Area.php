<?php
declare (strict_types = 1);

namespace app\admin\controller;

use think\Request;
use app\common\controller\AdminController;
use app\common\model\Area as AreaModel;

class Area extends AdminController
{
    protected function initialize()
    {
        parent::initialize();

        $this->model = AreaModel::class;
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        return $this->view->fetch();
    }

    public function getAreaList(Request $request)
    {
        if ($request->isAjax()) {
            $page = (int) $request->param('page', 1);
            $limit = (int) $request->param('limit', 10);
            $title = $request->param('title', '');
            $map = [];

            if ($title) {
                $map[] = ['title', 'like', '%' . $title . '%'];
            }

            $this->returnData['total'] = $this->model::where($map)->count();
            $this->returnData['data'] = $this->model::withCount(['building', 'house'])
                ->where($map)
                ->order('id desc')
                ->limit(($page - 1) * $limit, $limit)
                ->select();

            $this->success();
        }

        $this->error();
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        return $this->view->fetch();
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        if ($request->isPost()) {
            $params = $request->only(['title']);
            $params['admin_id'] = $this->userInfo->id;
            (new $this->model)->save($params);
            $this->returnData['code'] = 1;
            $this->success(lang('Done'));
        }

        $this->error();
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        if ($request->isPost()) {
            $params = $request->only(['title', 'investigation_times_one', 'investigation_times_two', 'investigation_times_three']);
            $area = $this->model::find($id);
            if (isset($params['investigation_times_two']) && (int) $params['investigation_times_two'] === 1) {
                $params['investigation_times_one'] = 2;
            }

            if (isset($params['investigation_times_three']) && (int) $params['investigation_times_three'] === 1) {
                $params['investigation_times_two'] = 2;
            }

            $area->save($params);
            $this->returnData['code'] = 1;
            $this->success(lang('Done'));
        }

        $this->error();
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if ($this->request->isPost()) {
            $area = $this->model::find($id);
            $area->delete();
            $this->returnData['code'] = 1;
            $this->success(lang('Done'));
        }

        $this->error();
    }
}
