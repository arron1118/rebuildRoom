<?php
declare (strict_types = 1);

namespace app\api\controller;

use think\Request;
use app\common\controller\ApiController;
use app\common\model\Building as BuildingModel;

class Building extends ApiController
{
    public function initialize()
    {
        parent::initialize();

        $this->model = BuildingModel::class;
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

        if ((int) $areaId <= 0) {
            $this->returnApiData('请提供项目ID: area_id');
        }

        $investigation_times = getInvestigationTimes($areaId);

        $map = [
            ['area_id', '=', $areaId]
        ];

        if ($title) {
            $map[] = ['title', 'like', '%' . $title . '%'];
        }

        $this->returnData['total'] = $this->model::where($map)->count();
        $this->returnData['data'] = $this->model::field('id, title, area_id, house_total')
            ->withCount(['house' => function ($query, &$alias) use ($investigation_times) {
                switch ($investigation_times) {
                    case 2:
                        $query->where('investigation_times_two_status', 1);
                        break;

                    case 3:
                        $query->where('investigation_times_three_status', 1);
                        break;

                    default:
                        $query->where('investigation_times_one_status', 1);
                        break;
                }

                $alias = 'finish_total';
            }])
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
