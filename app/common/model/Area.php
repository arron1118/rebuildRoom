<?php
declare (strict_types = 1);

namespace app\common\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class Area extends Model
{

    public function building()
    {
        return $this->hasMany(Building::class);
    }

    public function house()
    {
        return $this->hasMany(House::class);
    }

    public function investigation()
    {
        return $this->hasMany(Investigation::class);
    }
}
