<?php

namespace App\Services\Code;

use App\Exports\CodeExport;
use App\Models\Code\CodeModel;
use App\Models\Code\CodeQueryLogModel;
use App\Services\Service;
use App\Utils\ResultHelper;
use Illuminate\Support\Facades\Storage;
use League\CommonMark\Extension\CommonMark\Node\Inline\Code;
use Symfony\Component\HttpFoundation\Response;

class CodeService extends Service
{
    use ResultHelper;

    protected $model;

    protected $sort = [
        'id' , 'DESC'
    ];

    public function __construct(CodeModel $model)
    {
        $this->model = $model;
    }

    //生成随机编码
    public function createRandCode($num = 100, $dbCode = [])
    {
        $start = '8630';
        $code = [];
        $whileNum = $num;
        while ($whileNum > 0) {
            $code[] = $start . rand(100000000000, 999999999999);
            $whileNum--;
        }
        //检查有没有重复的
        $code = array_unique($code);
        if (count($code) < $num) {
            $code = array_merge($code, $this->createRandCode($num - count($code)));
        }

        if (!empty($dbCode)) {
            $code = $this->diffCode($dbCode, $code, $num);
        }


        return $code;

    }

    //计算差集
    public function diffCode($dbCode, $randCode, $num)
    {
        //先计算差集
        $diff = array_diff($randCode, $dbCode);
        if (count($diff) < $num) {
            $code = array_merge($diff, $this->createRandCode($num - count($diff)));
            return $this->diffCode($dbCode, $code, $num);
        }

        return $diff;
    }

    //获取最后一个编码
    public function getLastBatch()
    {
        $batch = $this->model->orderBy('batch', 'DESC')->value('batch');
        if (empty($batch)) {
            $batch = 1;
        }else{
            $batch++;
        }
        return $batch;
    }


    //生成二维码
    public function create(array $data)
    {
        if (empty($data['num']) || $data['num'] > 1000) {
            return $this->failed(Response::HTTP_INTERNAL_SERVER_ERROR, trans('没有生成数量或生成数量大于1000'));
        }

        //获取下全部数据,计算出交集
        $dbCode = $this->model->pluck('code')->toArray();

        //生成随机数量
        $randCode = $this->createRandCode($data['num'], $dbCode);

        //获取批次
        $batch = $this->getLastBatch();

        //插入数据库
        $addResult = $this->createRunAdd($randCode, $batch);

        if (!empty($addResult)) {
            $result = $this->success(Response::HTTP_OK, '数据写入成功,请耐心等待二维码生成');
        } else {
            $result = $this->failed(Response::HTTP_INTERNAL_SERVER_ERROR, trans('插入失败,请重新操作'));
        }

        return $result;
    }

    public function createRunAdd($randCode, $batch)
    {
        $data = [];
        foreach ($randCode as $val) {
            $data[] = [
                'code' => $val,
                'batch' => $batch,
            ];

        }
        return $this->model->insert($data);
    }




    public function exportExcel($params){


        if(empty($params['batch'])){
            return $this->failed(Response::HTTP_INTERNAL_SERVER_ERROR, trans('请输入批次'));
        }


        $model = new CodeExport($this->model , $params);

        if(empty($model->data)){
            return $this->failed(Response::HTTP_INTERNAL_SERVER_ERROR, trans('没有数据可以导出'));
        }


        $fileName = urlencode($model->getTitleName());

        return $this->success(Response::HTTP_OK, '' , [
            'model' => $model,
            'fileName' => $fileName
        ]);

    }


}
