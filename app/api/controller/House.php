<?php
declare (strict_types = 1);

namespace app\api\controller;

use think\Request;
use app\common\controller\ApiController;
use app\common\model\House as HouseModel;

class House extends ApiController
{
    public function initialize()
    {
        parent::initialize();

        $this->model = HouseModel::class;
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $page = $this->params['page'] ?? 1;
        $limit = $this->params['limit'] ?? 10;
        $title = $this->params['title'] ?? '';
        $areaId = $this->params['area_id'] ?? 0;
        $buildingId = $this->params['building_id'] ?? 0;
        $investigation_times = $this->params['investigation_times'] ?? 1;
        $map = [];

        if ($title) {
            $map[] = ['title', 'like', '%' . $title . '%'];
        }

        if ($areaId) {
            $map[] = ['area_id', '=', $areaId];
        }

        if ($buildingId) {
            $map[] = ['building_id', '=', $buildingId];
        }

        $this->returnData['code'] = 1;
        $this->returnData['total'] = $this->model::where($map)->count();
        $this->returnData['data'] = $this->model::field('id, title, area_id, building_id, investigation_times, investigation_times_one_status, investigation_times_two_status, investigation_times_three_status, create_time')->withCount(['investigation'])
            ->where($map)
            ->order('id desc')
            ->limit(($page - 1) * $limit, $limit)
            ->select();

        $this->returnApiData(lang('Done'));
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
            $params = $request->only(['title', 'area_id', 'building_id']);
            (new $this->model)->save($params);
            $this->returnData['code'] = 1;
            $this->returnApiData(lang('Done'));
        }

        $this->returnApiData();
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
        if ($this->request->isPost()) {
            $house = $this->model::find($id);
            $house->delete();
            $this->returnData['code'] = 1;
            $this->returnApiData(lang('Done'));
        }

        $this->returnApiData();
    }
}
