<?php

namespace App\Services\System;

use App\Models\AccessLog\AccessLogErrorModel;
use App\Services\Service;
use App\Utils\ResultHelper;

class SysAccessLogErrorService extends Service
{
    use ResultHelper;
    protected $model;

    public function __construct(AccessLogErrorModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取自定义搜索的设置,默认是like
     * @return array
     */
    public function setSearchInfo() : array
    {
        //格式
        //1.如果说你想 title like "%123%"
        //$return['title'] = ['like' , '%&s%'];

        //2.如果说你想 title between 100 AND 200 , 传参必须是100,200
        //$return['title'] = ['between'];

        //3.如果说你想查询今天的内容 时间戳 传参 2021-01-23 和传参 2021-01-01,2021-01-30均可
        $return['created_at'] = ['datetime'];
        $return['path'] = ['like' , '%&s%'];


        return $return;
    }

    /**
     * list后置方法
     * @param $result
     * @return mixed
     */
    public function listAfter($result){
        $result['other']['method_all'] = AccessLogErrorModel::getMethodAll(null , 'app');
        return $result;
    }

}
