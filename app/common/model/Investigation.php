<?php
declare (strict_types = 1);

namespace app\common\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class Investigation extends Model
{
    public static function onBeforeInsert($Investigation)
    {
    }

    public function getImagesAttr($value)
    {
        return $value ? explode(',', $value) : [];
    }

    public function getImageTimeAttr($value)
    {
        return $value ? date($this->getDateFormat(), $value) : '-';
    }
}
