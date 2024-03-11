<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\System\MenuModel
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property int|null $menu_level
 * @property string|null $parent_id 父菜单ID
 * @property string|null $path 路由path
 * @property string|null $name 路由name
 * @property array $meta meta
 * @property string|null $icon 附加属性
 * @property bool|null $hidden 是否在列表隐藏
 * @property string|null $component 对应前端文件路径
 * @property int|null $sort 排序标记
 * @property array|null $parameters 额外参数
 * @method static \Illuminate\Database\Eloquent\Builder|MenuModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MenuModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MenuModel query()
 * @method static \Illuminate\Database\Eloquent\Builder|MenuModel whereComponent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MenuModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MenuModel whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MenuModel whereHidden($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MenuModel whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MenuModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MenuModel whereMenuLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MenuModel whereMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MenuModel whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MenuModel whereParameters($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MenuModel whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MenuModel wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MenuModel whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MenuModel whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MenuModel extends \App\Models\BaseModel
{
    use HasFactory;
    protected $table = "sys_base_menus";
    protected $primaryKey = "id";

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        "id", "created_at", "updated_at", "deleted_at", "menu_level", "parent_id",
        "path", "name", "hidden", "component", "sort", "meta",'parameters'
    ];

    protected $casts = [
        'parameters' => 'array',
        'hidden' => 'boolean',
    ];

    public function setMetaAttribute($value) // Mutator 存進資料庫前作用
    {
        $this->attributes['meta'] = json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    public function getMetaAttribute($value) // Accessor 從資料庫取出後作用
    {
        return  json_decode($value,true);
    }
}
