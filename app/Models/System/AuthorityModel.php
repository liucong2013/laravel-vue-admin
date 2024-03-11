<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\System\AuthorityModel
 *
 * @property int $authority_id 角色ID
 * @property string $authority_name 角色名
 * @property int $parent_id 父角色ID
 * @property array $menu_ids 菜单IDS
 * @property \Illuminate\Support\Carbon $created_at 创建时间
 * @property \Illuminate\Support\Carbon $updated_at 更新时间
 * @property int $delete_time 删除时间
 * @method static \Illuminate\Database\Eloquent\Builder|AuthorityModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AuthorityModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AuthorityModel query()
 * @method static \Illuminate\Database\Eloquent\Builder|AuthorityModel whereAuthorityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuthorityModel whereAuthorityName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuthorityModel whereMenuIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuthorityModel whereParentId($value)
 * @mixin \Eloquent
 */
class AuthorityModel extends \App\Models\BaseModel
{
    use HasFactory;
    protected $table = "sys_authorities";
    protected $primaryKey = "authority_id";


    protected $fillable = [
        "authority_id", "authority_name", "parent_id", "menu_ids", "created_at", "updated_at", "deleted_at"
    ];

    protected $casts = [
        'menu_ids' => 'array',
    ];

    protected $attributes = [
        'parent_id' => 0,
    ];

}
