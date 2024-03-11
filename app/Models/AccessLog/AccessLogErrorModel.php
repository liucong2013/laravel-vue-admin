<?php

namespace App\Models\AccessLog;

use App\Utils\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;

/**
 * App\Models\System\AccessLogErrorModel
 *
 * @property int $id
 * @property \datetime|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property string|null $ip 请求ip
 * @property string|null $method 请求方法
 * @property string|null $path 请求路径
 * @property int|null $status 请求状态
 * @property float|null $latency 延迟（用时）
 * @property string|null $agent 代理
 * @property string|null $error_message 错误信息
 * @property string|null $body 请求Body
 * @property array|null $resp 响应Body
 * @property string|null $user_id 用户id
 * @property string|null $user_name 用户姓名
 * @method static \Illuminate\Database\Eloquent\Builder|AccessLogErrorModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AccessLogErrorModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AccessLogErrorModel query()
 * @method static \Illuminate\Database\Eloquent\Builder|AccessLogErrorModel whereAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccessLogErrorModel whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccessLogErrorModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccessLogErrorModel whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccessLogErrorModel whereErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccessLogErrorModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccessLogErrorModel whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccessLogErrorModel whereLatency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccessLogErrorModel whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccessLogErrorModel wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccessLogErrorModel whereResp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccessLogErrorModel whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccessLogErrorModel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccessLogErrorModel whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccessLogErrorModel whereUserName($value)
 * @mixin \Eloquent
 */
class AccessLogErrorModel extends \App\Models\BaseModel
{
    use HasFactory;

    protected $table = 'sys_access_log_error';
    protected $primaryKey = "id";
    protected $connection = "accessLog";


    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        "id",
        "created_at",
        "updated_at",
        "deleted_at",
        "ip",
        "method",
        "path",
        "status",
        "latency",
        "agent",
        "error_message",
        "body",
        "resp",
        "user_id",
        "user_name"
    ];

    protected $casts = [
        'resp'       => 'array',
        'created_at' => 'datetime:Y-m-d H:i:s',
    ];

    //请求状态
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';


    /**
     * 获取请求状态
     * @param null $key
     * @param string $format
     * @return array|string|string[]
     */
    public static function getMethodAll($key = null, $format = 'arr')
    {
        $array = [
            self::METHOD_GET  => 'GET',
            self::METHOD_POST => 'POST',
        ];

        return Helper::getSelectFormat($array, $key, '未设置', $format);
    }

    //从缓存中写入
    public function cacheSycDb()
    {
        //先复制一份
        Redis::sunionstore( 'set:logList:error:bak' , 'set:logList:error:source');
        Redis::del('set:logList:error:source');

        //循环写入数据库
        $data = Redis::smembers('set:logList:error:bak');
        $insertArr = [];
        if(!empty($data)){
            foreach ($data as $val){
                if(!Helper::isJson($val)){
                    continue;
                }
                $insertArr[] = json_decode($val , true);
            }

            if(!empty($insertArr)){
                self::insert($insertArr);
            }

        }

    }

}
