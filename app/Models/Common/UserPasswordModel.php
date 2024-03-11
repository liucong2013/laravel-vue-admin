<?php


namespace App\Models\Common;


use App\Http\Controllers\System\UserModel;

/**
 * 用户密码模型
 * Class UserPasswordModel
 *
 * @package App\Models\App
 * @property int $id
 * @property int $uid 用户id
 * @property string $password 密码
 * @property int $type 用户类型：1(app客户端) 2(管理后台)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPasswrodModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPasswrodModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPasswrodModel query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPasswrodModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPasswrodModel wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPasswrodModel whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPasswrodModel whereUid($value)
 * @mixin \Eloquent
 */
class UserPasswordModel extends \App\Models\BaseModel
{
    protected $table = "users_password";
    protected $primaryKey = "id";

    //是否管理orm的时间戳
    public $timestamps = false;
    //create白名单
    protected $fillable = [
        'id',
        'uid',
        'password',
        'type'
    ];

    public function User(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(UserModel::class);
    }

    const TYPE_APP = 1;
    const TYPE_ADMIN = 2;
    const targetText = [
        self::TYPE_APP => 'APP',
        self::TYPE_ADMIN => '管理后台'
    ];
}
