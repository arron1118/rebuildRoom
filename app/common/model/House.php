<?php
declare (strict_types = 1);

namespace app\common\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class House extends Model
{
    public static function onAfterInsert($house): void
    {
        Building::where('id', $house->building_id)->inc('house_total')->update();
    }

    public function investigation()
    {
        return $this->hasMany(Investigation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function Building()
    {
        return $this->belongsTo(Building::class)->bind(['building_title' => 'title']);
    }

    public function area()
    {
        return $this->belongsTo(Area::class)->bind(['area_title' => 'title']);
    }
}
