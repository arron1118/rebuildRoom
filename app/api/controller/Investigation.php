<?php
declare (strict_types = 1);

namespace app\api\controller;

use think\Request;
use app\common\controller\ApiController;
use app\common\model\Investigation as InvestigationModel;
use app\common\model\Area;

class Investigation extends ApiController
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
        $page = $this->params['page'] ?? 1;
        $limit = $this->params['limit'] ?? 10;
        $house_id = $this->params['house_id'] ?? 0;
        $areaId = $this->params['area_id'] ?? 0;

        if ((int) $house_id <= 0) {
            $this->returnApiData('请提供房号ID: house_id');
        }

        if ((int) $areaId <= 0) {
            $this->returnApiData('请提供项目ID: area_id');
        }

        $map = [
            ['house_id', '=', $house_id],
            ['investigation_times', '=', getInvestigationTimes($areaId)],
        ];


        $this->returnData['total'] = $this->model::where($map)->count();
        $this->returnData['data'] = $this->model::where($map)
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
            $params = $request->only(['title', 'area_id', 'building_id', 'house_id', 'type', 'crack_area', 'crack_sum', 'images', 'image_time', 'reason', 'description']);
            $params['investigation_times'] = getInvestigationTimes($params['area_id']);
            $params['user_id'] = $this->userInfo->id;

            if (isset($params['title']) && $params['title'] !== '') {
                $house = new \app\common\model\House;
                $house->investigation_times = $params['investigation_times'];
                $house->area_id = $params['area_id'];
                $house->building_id = $params['building_id'];
                $house->user_id = $params['user_id'];
                $house->title = $params['title'];
                $house->save();

                $params['house_id'] = $house->id;
            }

            $params['images'] = implode(',', $this->upload('images'));

            if (!isset($params['image_time'])) {
                $params['image_time'] = time();
            }

            (new $this->model)->save($params);
            $this->returnData['data'] = $params;
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
        //
    }
}
