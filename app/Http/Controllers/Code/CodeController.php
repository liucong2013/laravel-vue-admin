<?php

namespace App\Http\Controllers\Code;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CustomController;
use App\Services\Code\CodeService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CodeController extends CustomController
{
    protected $server;

    public function __construct(CodeService $server)
    {
        $this->server = $server;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public  function exportExcel(Request $request)
    {

        ini_set("memory_limit",'-1');
        // 对请求数据进行分流
        $params = $request->all();

        $return = $this->server->exportExcel($params);

        if(!empty($return['success'])){
            return Excel::download($return['data']['model'] , $return['data']['fileName'] , null , ['filename' => $return['data']['fileName']]);
        }

        return response()->json($return);



    }


}
