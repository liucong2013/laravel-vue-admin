<?php

namespace App\Http\Controllers\System;

use App\Models\Common\UserPasswordModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * App\Models\System\UserModel
 *
 * @property int $id
 * @property string $uuid 用户UUID
 * @property string $username 用户登录名
 * @property string $nick_name 用户昵称
 * @property string $mobile 手机号码
 * @property string $header_img 用户头像
 * @property int $authority_id 用户角色ID，只有管理后台用户才有角色id
 * @property-read \App\Models\System\AuthorityModel|null $authority
 * @property-read UserPasswordModel|null $checkPw
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @method static \Illuminate\Database\Eloquent\Builder|UserModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserModel query()
 * @mixin \Eloquent
 */
class UserModel extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $table = "sys_users";
    protected $primaryKey = "id";
    protected $hidden = ['password'];

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $dateFormat = 'U';

    protected $fillable = [
        'id',
        'uuid',
        'username',
        'password',
        'nick_name',
        'created_at',
        'updated_at'
    ];


    protected function setPasswordAttribute($value)
    {
        $this->attributes['password'] = md5($value);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    // 关联角色表
    public function authority()
    {
        return $this->hasOne('App\Models\System\AuthorityModel', 'authority_id', 'authority_id');
    }

    /**
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function checkPw()
    {
        return $this->hasOne(UserPasswordModel::class, 'uid', 'id');
    }



    /**
     * app用户列表联表（管理后台）
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|mixed
     */
    public function appAllUserListQueryModel()
    {
        return DB::table('sys_users')
            //联表
            //查询内容展示
            ->select([
                'sys_users.id',
                'sys_users.uuid',
                'sys_users.username',
                'sys_users.nick_name',
                'sys_users.header_img',
                'sys_users.created_at',
                'sys_users.deleted_at',
            ]);
    }


    /**
     * 获取用户列表，用户id为下标
     * @param array $userIdArr
     * @return array
     */
    public function getUserList(array $userIdArr): array
    {
        $list = self::query()->select('id', 'nick_name')->whereIn('id', $userIdArr)->get();
        if (count($list) == 0) {
            return [];
        }
        $list = $list->toArray();
        $userList = [];
        foreach ($list as $item => $value) {
            $userList[$value['id']] = $value;
        }
        return $userList;
    }


    /**
     * 批量获取用户手机号
     * @param $userIdArr array 用户id数组
     * @return array
     */
    public static function getUserMobileAll(array $userIdArr): array
    {
        return self::whereIn('id', $userIdArr)->pluck('mobile', 'id')->toArray();
    }
}
