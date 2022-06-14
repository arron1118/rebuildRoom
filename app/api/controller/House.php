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
        $buildingId = (int) ($this->params['building_id'] ?? 0);
        $investigation_status = $this->params['investigation_status'] ?? null;

        if ((int) $areaId <= 0) {
            $this->returnApiData('请提供项目ID: area_id');
        }

        if ($buildingId <= 0) {
            $this->returnApiData('请提供房号ID: building_id');
        }
        $investigation_times = getInvestigationTimes($areaId);

        $map = [
            ['area_id', '=', $areaId],
            ['building_id', '=', $buildingId],
            ['investigation_times', '=', $investigation_times]
        ];

        if ($title) {
            $map[] = ['title', 'like', '%' . $title . '%'];
        }

        $finish_map = [];
        switch ($investigation_times) {
            case 2:
                if (!is_null($investigation_status)) {
                    $map[] = ['investigation_times_two_status', '=', $investigation_status];
                }
                $finish_map[] = ['investigation_times_two_status', '=', 1];
                break;

            case 3:
                if (!is_null($investigation_status)) {
                    $map[] = ['investigation_times_three_status', '=', $investigation_status];
                }
                $finish_map[] = ['investigation_times_three_status', '=', 1];
                break;

            default:
                if (!is_null($investigation_status)) {
                    $map[] = ['investigation_times_one_status', '=', $investigation_status];
                }
                $finish_map[] = ['investigation_times_one_status', '=', 1];
                break;
        }

        $this->returnData['code'] = 1;
        $this->returnData['total'] = $this->model::where($map)->count();
        $this->returnData['finish_total'] = $this->model::where($finish_map)->count();
        $this->returnData['data'] = $this->model::field('id, title, area_id, building_id, investigation_times, investigation_times_one_status, investigation_times_two_status, investigation_times_three_status, create_time')
            ->withCount(['investigation'])
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

            if ((int) $params['area_id'] <= 0) {
                $this->returnApiData('请提供项目ID: area_id');
            }
            $params['investigation_times'] = getInvestigationTimes($params['area_id']);
            $params['user_id'] = $this->userInfo->id;
            (new $this->model)->save($params);

            $this->returnData['code'] = 1;
            $this->returnApiData(lang('Done'));
        }

        $this->returnApiData('添加失败');
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        $this->returnData['data'] = $this->model::with(['investigation'])->findOrEmpty($id);
        if ($this->returnData['data']->isEmpty()) {
            $this->returnApiData(lang('No data was found'));
        }
        $this->returnApiData(lang('Done'));
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
