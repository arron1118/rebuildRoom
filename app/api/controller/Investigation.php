<?php
declare (strict_types = 1);

namespace app\api\controller;

use think\Request;
use app\common\controller\ApiController;
use app\common\model\Investigation as InvestigationModel;
use app\common\model\Area;
use app\common\model\House;

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
        $type = $this->params['type'] ?? 0;

        if ((int) $house_id <= 0) {
            $this->returnApiData('请提供房号ID: house_id');
        }

        if ((int) $areaId <= 0) {
            $this->returnApiData('请提供项目ID: area_id');
        }

        $investigation_times = getInvestigationTimes($areaId);

        $map = [
            ['house_id', '=', $house_id],
            ['investigation_times', '=', $investigation_times],
            ['type', '=', $type],
        ];

        $fields = 'id, house_id, type, investigation_times, create_time,';
        switch ((int) $type) {
            case 1:
                $fields .= 'crack_area, crack_sum, crack_images, crack_description, crack_image_time';
                break;

            case 2:
                $fields .= 'refuse_images, refuse_image_time, refuse_reason';
                break;

            default:
                $fields .= 'images, image_time, description';
                break;
        }

        $this->returnData['code'] = 1;
        $this->returnData['total'] = $this->model::where($map)->count();
        $this->returnData['data'] = $this->model::field($fields)
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
            $params = $request->only(['title', 'area_id', 'building_id', 'house_id', 'type', 'images', 'image_time', 'description', 'crack_area', 'crack_sum', 'crack_images', 'crack_description', 'crack_image_time', 'refuse_images', 'refuse_image_time', 'refuse_reason']);
            $params['investigation_times'] = getInvestigationTimes($params['area_id']);
            $params['user_id'] = $this->userInfo->id;
            $params['type'] = (int) $params['type'];

            if (isset($params['title']) && $params['title'] !== '') {
                $house = new House();
                $house->investigation_times = $params['investigation_times'];
                $house->area_id = $params['area_id'];
                $house->building_id = $params['building_id'];
                $house->user_id = $params['user_id'];
                $house->title = $params['title'];
                $house->save();

                $params['house_id'] = $house->id;
            }

            if (!$params['house_id']) {
                $this->returnApiData('未找到套房ID');
            }

            if ($params['type'] !== 3) {
                $house = House::find($params['house_id']);
                $type = $params['type'] === 2 ? 2 : 1;
                switch ($params['investigation_times']) {
                    case 2:
                        $house->investigation_times_two_status = $type;
                        break;

                    case 3:
                        $house->investigation_times_three_status = $type;
                        break;

                    default:
                        $house->investigation_times_one_status = $type;
                        break;
                }
                $house->save();

                switch ($params['type']) {
                    case 1:
                        $params['crack_images'] = implode(',', $this->upload('crack_images'));
                        if ($params['crack_images']) {
                            $params['crack_image_time'] = isset($params['crack_image_time']) ? strtotime($params['crack_image_time']) : time();
                        }
                        break;

                    case 2:
                        $params['refuse_images'] = implode(',', $this->upload('refuse_images'));
                        if ($params['refuse_images']) {
                            $params['refuse_image_time'] = isset($params['refuse_image_time']) ? strtotime($params['refuse_image_time']) : time();
                        }
                        break;

                    default:
                        $params['images'] = implode(',', $this->upload('images'));
                        if ($params['images']) {
                            $params['image_time'] = isset($params['image_time']) ? strtotime($params['image_time']) : time();
                        }
                        break;
                }

                (new $this->model)->save($params);
            }

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
        $this->returnData['data'] = $this->model::find($id);
        $this->returnData['code'] = 1;
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
        if ($request->isPost()) {
            $params = $request->only(['title', 'area_id', 'building_id', 'house_id', 'type', 'images', 'image_time', 'description', 'crack_area', 'crack_sum', 'crack_images', 'crack_description', 'crack_image_time', 'refuse_images', 'refuse_image_time', 'refuse_reason']);
            $params['investigation_times'] = getInvestigationTimes($params['area_id']);
            $params['user_id'] = $this->userInfo->id;
            $params['type'] = (int) $params['type'];

            if (!$id) {
                $this->returnApiData('未找到套房排查ID：id');
            }
            $investigation = $this->model::find($id);
            if (!$investigation) {
                $this->returnApiData('未找到相关排查数据');
            }

            $params['refuse_images'] = implode(',', $this->upload('refuse_images'));
            if ($params['refuse_images']) {
                $params['refuse_image_time'] = isset($params['refuse_image_time']) ? strtotime($params['refuse_image_time']) : time();
            }

            $params['crack_images'] = implode(',', $this->upload('crack_images'));
            if ($params['crack_images']) {
                $params['crack_image_time'] = isset($params['crack_image_time']) ? strtotime($params['crack_image_time']) : time();
            }

            $params['images'] = implode(',', $this->upload('images'));
            if ($params['images']) {
                $params['image_time'] = isset($params['image_time']) ? strtotime($params['image_time']) : time();
            }

            $investigation->save($params);
            $this->returnData['code'] = 1;
            $this->returnApiData(lang('Done'));
        }

        $this->returnApiData();
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
