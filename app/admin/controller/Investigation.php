<?php
declare (strict_types = 1);

namespace app\admin\controller;

use think\Request;
use app\common\controller\AdminController;
use app\common\model\Investigation as InvestigationModel;

class Investigation extends AdminController
{
    public function initialize()
    {
        parent::initialize();

        $this->model = InvestigationModel::class;
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $area_id = $this->request->param('area_id');
        $house_id = $this->request->param('house_id');
        $type = $this->request->param('type', 1);
        if ($this->request->isAjax()) {
            $investigation_times = getInvestigationTimes($area_id);
            $this->returnData['data'] = $this->model::where([
                'investigation_times' => $investigation_times,
                'house_id' => $house_id,
                ])->select();
            $this->returnData['code'] = 1;
            $this->success();
        }

        $this->view->assign([
            'type' => $type,
            'area_id' => $area_id,
            'house_id' => $house_id,
            'typeList' => (new $this->model)->getTypeList()
        ]);
        return $this->view->fetch();
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //
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
        //
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
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }
}
