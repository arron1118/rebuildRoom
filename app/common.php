<?php
// 应用公共文件


function createToken($password)
{
    return password_hash($password, PASSWORD_BCRYPT);
}

function getInvestigationTimes($areaId)
{
    $area = \app\common\model\Area::find($areaId);
    if ($area->investigation_times_one === 1) {
        return 1;
    }

    if ($area->investigation_times_two === 1) {
        return 2;
    }

    if ($area->investigation_times_three === 1) {
        return 3;
    }
}

