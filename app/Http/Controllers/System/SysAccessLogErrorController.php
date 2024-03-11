<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CustomController;
use App\Models\AccessLog\AccessLogErrorModel;
use App\Utils\ResultHelper;
use App\Services\System\SysAccessLogErrorService;
use Symfony\Component\HttpFoundation\Response;

class SysAccessLogErrorController extends CustomController
{
    use ResultHelper;
    protected $server;

    public function __construct(SysAccessLogErrorService $server)
    {
        $this->server = $server;
    }

    public function find(string $id)
    {
        try {
            $result = AccessLogErrorModel::where('id',$id)->first();
            $result = $this->sucToJson(Response::HTTP_OK, '查询数据成功', $result->toArray());
        } catch (\Exception $ex) {
            report($ex);
            $result = $this->failToJson(Response::HTTP_INTERNAL_SERVER_ERROR, $ex->getMessage());
        }
        return $result;
    }
}
