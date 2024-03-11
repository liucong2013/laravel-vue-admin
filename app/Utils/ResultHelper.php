<?php

/**
 * 结果返回 方法
 */

namespace App\Utils;


trait ResultHelper
{

    /**
     * 返回格式为信息
     * [
     *   'code' => '',
     *   'msg' => '',
     *   'data' => []
     * ]
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    public static function sendAutoResponse(int $code,string $msg = '',$data=[]) : \Illuminate\Http\JsonResponse
    {
        if(!empty($code)){
            if($code == 200 || $code == 226 ){
                if(empty($msg)){
                    $msg = trans('common.operation_success');
                }
                $result= self::setSuccess($code ,  $msg ,$data);
            }else{
                $result= self::setFailed( $code ,  $msg , $data);
            }
        }else if(!empty($msg)){
            $result= self::setFailed(500 , $msg);
        }else{
            $result= self::setFailed(500 , trans('内部错误,请联系开发人员'));
        }

        return response()->json($result);
    }

    /**
     * 成功返回
     * @param $code
     * @param $msg
     * @param array|boolean $data
     * @return array
     */
    public function success($code, $msg, $data = []): array
    {
        return self::setSuccess($code, $msg, $data);
    }

    /**
     * 成功返回
     * @param $code
     * @param $msg
     * @param array|boolean $data
     * @return array
     */
    public static function setSuccess($code, $msg = '', $data = []): array
    {
        if (is_string($code)) {
            if (!is_string($msg)) {
                $data = (array) $msg;
            }
            $msg = $code;
            $code = 200;
        } else if (is_array($code)) {
            $data = $code;
            $code = 200;
            $msg = 'suc';
        }
        return [
            'success' => true,
            'code' => $code,
            'msg' => $msg,
            'data' => $data ?:  [],
        ];
    }

    /**
     * 失败返回
     * @param string $code
     * @param string $msg
     * @param array $data
     * @return array
     */
    public function failed($code, $msg = '', $data = []): array
    {
        return self::setFailed($code, $msg, $data);
    }

    /**
     * 失败返回
     * @param string $code
     * @param string $msg
     * @param array $data
     * @return array
     */
    public static function setFailed($code, $msg, $data = []): array
    {
        if (is_string($code)) {
            if (!is_string($msg)) {
                $data = (array) $msg;
            }
            $msg = $code;
            $code = 40000;
        } else if (is_array($code)) {
            $data = $code;
            $code = 40000;
            $msg = 'fail';
        }
        return [
            'success' => false,
            'code' => $code,
            'msg' => $msg,
            'data' => $data ?:  [],
        ];
    }

    /**
     * 简单返回表格数据组合
     * @param int $code
     * @param string $msg
     * @param array $data
     * @return array
     */
    public function tableData(int $code, string $msg = '', array $data = []): array
    {
        // dd($data);
        return [
            'success' => true,
            'code' => $code,
            'msg' => $msg,
            'data' => self::dataMany($data),
        ];
    }


    /**
     * 分页数据统一格式化
     * @return array
     */
    public static function dataMany($data = []): array
    {
        if (!empty($data) && !is_array($data)) {
            $data = $data->toArray();
        }

        return [
            'list' => isset($data['data']) ? (array)$data['data'] : [],
            'page' => isset($data['current_page']) ? (int)$data['current_page'] : 0,
            'pageSize' => isset($data['per_page']) ? (int)$data['per_page'] : 0,
            'total' => isset($data['total']) ? (int)$data['total'] : 0,
            'other' => isset($data['other']) ? (array)$data['other'] : [],
        ];
    }

    /**
     * 读取文件，返回二进制流
     * @param string $file_dir
     * @return void
     */
    public function blobData(string $file_dir)
    {
        // 清除缓冲区
        ob_end_clean();
        ob_start();

        // 打开文件
        $handler            = fopen($file_dir, 'r+b');
        $file_size          = filesize($file_dir);

        // 声明头信息
        header("success: true");  // 关闭前端响应拦截
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length: " . $file_size);
        Header("Content-Disposition: attachment; filename=" . basename($file_dir));

        // 输出文件内容
        echo fread($handler, $file_size);
        fclose($handler);
        ob_end_flush();
        exit;
    }


    /**
     * 成功返回，返回json格式结果
     * @param $code
     * @param $msg
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function sucToJson($code, $msg = '', array $data = [])
    {
        return response()->json($this->success($code, $msg, $data));
    }

    /**
     * 失败返回，返回json格式结果
     * @param $code
     * @param $msg
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function failToJson($code, $msg = '', array $data = [])
    {
        return response()->json($this->failed($code, $msg, $data));
    }
}
