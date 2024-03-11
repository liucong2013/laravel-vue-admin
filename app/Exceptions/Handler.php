<?php

namespace App\Exceptions;

use App\Models\AccessLog\AccessLogErrorModel;
use App\Utils\ResultHelper;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{

    use ResultHelper;

    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function report(Throwable $e)
    {

        $request = request();
        $this->saveAccessLogError($request , $e);
        parent::report($e);
    }


    /**
     * 自定义错误处理
     * @param \Illuminate\Http\Request $request
     * @param Throwable $exception
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
     * @throws Throwable
     */
    public function render($request, Throwable $exception)
    {

        if ($exception instanceof UnauthorizedHttpException) {
            return response()->json($this->failed(401, "未授权登录，请先登录", ["reload" => true]));
        }

        if ($exception instanceof  NotFoundHttpException ) {
            return response()->json($this->failed(404, "没有找到对应接口"));
        }

        $this->saveAccessLogError($request , $exception);

        //自定义错误处理
        if ($exception instanceof CustomException) {
            return response()->json($this->failed($exception->customCode, $exception->customMessage));
        }

        //dd(parent::render($request, $exception));

        if(config('app.debug') !== true){
            return response()->json($this->failed(500, "系统错误,请联系客服,错误标识:".$request->getPathInfo()));
        }else{
            return parent::render($request, $exception);
        }


    }

    /**
     * 数据库存储错误日志
     * @param  \Illuminate\Http\Request $request
     * @throws \Throwable
     */
    public function saveAccessLogError($request , Throwable $exception )
    {

        //拒绝掉 storage 开头的记录
        $path = $request->getPathInfo();
        if(!empty($path)){
            $pathArr = explode('/' , $path);
            if(!empty($pathArr[1]) && $pathArr[1] == 'storage'){
                return true;
            }elseif(!empty($pathArr[1]) && !empty($pathArr[2]) && $pathArr[1] == 'image' && $pathArr[2] == 'thumb'){
                return true;
            }
        }

        if ($exception instanceof UnauthorizedHttpException) {
            return  true;
        }

        // 常规模式下记录增，改，删 用作审计记录
        $params = $request->all();
        $params['header'] = $request->header();

        //在这里对错误做一下处理,迫于无奈做了处理,有更好的处理方式请联系刘聪,原本想用json_encode((array)$exception)
        $errorArr = [
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'message' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ];



        $data = [
            'method' => $request->method(),
            'path' => $request->getPathInfo(),
            'ip' => $request->ip(),
            'body' => $params ? json_encode($params) : '',
            'agent' => $request->userAgent(),
            'latency' => round(microtime(true) - LARAVEL_START, 3),
            'user_id' => (Auth::user())->id ?? '',
            'user_name' => (Auth::user())->username ?? '',
            //            'resp' => $response->original,
            //            'status' => ($response->original)['code'] ?? 200,
            'resp' => '',
            'status' => 500,
            'error_message' => json_encode($errorArr) ,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        //这里写入缓存
        //Redis::sadd('set:logList:error:source',json_encode($data));
        (new AccessLogErrorModel())->fill($data)->save();

        return true;
    }

}

