<?php

namespace App\Services;

use App\Utils\ResultHelper;
// use Illuminate\Contracts\Database\ModelIdentifier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class Service
{
    use ResultHelper;
    protected $model;
    /**
     * 模糊查询Key
     */
    protected $likeSearch = "";

    /**
     * 排序规则[]
     * 第一个值为排序字段
     * 第二个值为排序规则 ASC|DESC
     */
    protected $sort = [];

    public $simplePaginate = false;
    public $total = 1000;

    /**
     * 查询结果前的model 只有在list前有效
     * @var \App\Models\BaseModel
     */
    public $endModel;

    public $searchInfo;

    /**
     * 获取所有数据
     * @param array $data
     * @return ResultHelper
     */
    public function all(array $data)
    {
        try {
            $result = $this->model->where($data)->get()->toArray();
            $result = $this->success(Response::HTTP_OK, '获取全部数据成功！', ["menus" => $result]);
        } catch (\Exception $ex) {
            report($ex);
            $result = $this->failed(Response::HTTP_INTERNAL_SERVER_ERROR, $ex->getMessage());
        }
        return $result;
    }

    /**
     * 这里直接自定义了搜索内容
     * 返回内容于$this->where一致,方便在自定义搜索
     * @return array
     */
    public function setCustomSearchInfo($searchInfo): array
    {
        return [];
    }

    /**
     * 获取搜索内容
     * @param array $searchInfo
     * @return array
     */
    public function getSearchInfo(array $searchInfo): array
    {
        //这里封装了 = , 当天时间搜索 , like
        $return = [];
        if (!empty($searchInfo)) {
            $setSearchInfo = $this->setSearchInfo();
            foreach ($searchInfo as $key => $val) {
                if ((is_numeric($val) && $val == 0) || !empty($val)) {
                    if (isset($setSearchInfo[$key]) && !empty($setSearchInfo[$key])) {
                        $next = $this->createSearchInfo($key, $setSearchInfo[$key], $val);
                        if (!empty($next)) {
                            $return[] = $next;
                        }
                    } else {
                        $return[] = [$key, '=', $val];
                    }
                }
            }
        }


        return $return;
    }


    /**
     * 由于laravel orm 无法实现 between 只能用 whereBetween,所以这里加了一层过滤
     * @param Model $model
     * @param array $searchInfo
     * @return mixed
     */
    public function getSearchInfoWhere($model, array $searchInfo)
    {

        foreach ($searchInfo as $val) {

            $tmpColumnNameArr  = explode('.', $val[0]);
            if (count($tmpColumnNameArr) == 1) {
                $val[0] = $this->model->getTable() . '.' . $val[0];
            }

            if ($val[1] == 'between') {
                $model = $model->whereBetween($val[0], $val[2]);
            }else if($val[1] == 'in'){
                $model = $model->whereIn($val[0] , $val[2]);
            } else {
                $model = $model->where(...array_values($val));
            }


        }

        return $model;
    }


    /**
     * @param string $name 查询名称
     * @param array $info 查询条件
     * @param $val //查询值
     * @return array
     */
    public function createSearchInfo(string $name, array $info, $val): array
    {
        $return = [];
        $condition = 'like'; //默认使用like处理
        if (!empty($info) && !empty($val) && isset($info[0]) && !empty($info[0])) {
            switch ($info[0]) {
                case '=':
                    $condition = '=';
                    break;
                case 'in':
                    $condition = 'in';
                    if(!is_array($val)){
                        $val = (array)$val;
                    }
                    break;
                case 'like':
                    if (isset($info[1]) && !empty($info[1])) {
                        $val = str_replace('&s', $val, $info[1]);
                    }
                    break;

                case 'between':
                    $nextVal = explode(',', $val);
                    if (count($nextVal) == 2) {
                        $condition = 'between';
                        $val = $nextVal;
                    }
                    break;

                case 'time':
                    if(!is_array($val)){
                        $nextVal = explode(',', $val);
                    }else{
                        $nextVal = $val;
                    }

                    if (count($nextVal) == 2) {
                        $condition = 'between';
                        $nextVal[0] = strtotime($nextVal[0]);
                        $nextVal[1] = strtotime($nextVal[1]) + 86400 - 1;
                        $val = $nextVal;
                    } else if (count($nextVal) == 1) {
                        $condition = 'between';
                        $nextVal[0] = strtotime($nextVal[0]);
                        $nextVal[1] = $nextVal[0] + 86400 - 1;
                        $val = $nextVal;
                    }
                    break;

                case 'datetime':
                    if(!is_array($val)){
                        $nextVal = explode(',', $val);
                    }else{
                        $nextVal = $val;
                    }
                    if (count($nextVal) == 2) {
                        $condition = 'between';
                        $nextValReturn[0] =  $nextVal[0] . " 00:00:00";
                        $nextValReturn[1] = $nextVal[1] . " 23:59:59";
                        $val = $nextVal;
                    } else if (count($nextVal) == 1) {
                        $condition = 'between';
                        $nextValReturn[0] =  $nextVal[0] . " 00:00:00";
                        $nextValReturn[1] =  $nextVal[0] . " 23:59:59";
                        $val = $nextValReturn;
                    }
                    break;
            }

            //尾部还有一个or,如果是or那么加上,想要支持其他自己加
            $endVal = end($info);
            if (in_array($endVal, ['OR', 'or'])) {

                $return = [
                    $name,
                    $condition,
                    $val,
                    $endVal
                ];
            } else {
                $return = [
                    $name,
                    $condition,
                    $val
                ];
            }
        }



        return $return;
    }


    /**
     * 获取自定义搜索的设置,默认是like
     * @return array
     */
    public function setSearchInfo(): array
    {
        //格式
        //1.如果说你想 title like "%123%"
        //$return['title'] = ['like' , '%&s%'];

        //2.如果说你想 title between 100 AND 200 , 传参必须是100,200
        //$return['title'] = ['between'];

        //3.如果说你想查询今天的内容 存储类型是时间戳 传参 2021-01-23 和传参 2021-01-01,2021-01-30均可
        //$return['created_at'] = ['time'];

        //3.如果说你想查询今天的内容 存储类型是datatime 传参 2021-01-23 和传参 2021-01-01,2021-01-30均可
        //$return['created_at'] = ['datetime'];

        return [];
    }

    /**
     * 用于配置联表查询时,排序表头的问题
     * @return array
     */
    public function getSortConfig(): array
    {
        return [];
    }

    /**
     * 排序简单处理
     * @param $sort
     * @return array
     */
    public function getSort($sort): array
    {

        //检查第一位是不是负数
        $return = [];
        if (is_string($sort) && !empty($sort)) {
            //检查第一位是不是负数
            $first = substr($sort, 0, 1);
            if ($first === '-') {
                $return[1] = 'DESC';
                $return[0] = substr($sort, 1);
            } else {
                $return[1] = 'ASC';
                $return[0] = $sort;
            }

            $sortConfig = $this->getSortConfig();
            $return[0] = $sortConfig[$return[0]] ?? $return[0];

            if(is_array($return[0])){
                $return[0] = $return[0][0];
            }else{
                $sortNameArr = explode('.', $return[0]);
                if (count($sortNameArr) == 1) {
                    $return[0] = $this->model->getTable() . '.' . $return[0];
                }
            }


        }

        return $return;
    }

    /**
     * 获取默认排序
     * @return string[]
     */
    public function getDefaultSort(): array
    {
        return [$this->model->getTable() . '.' . 'id', 'DESC'];
    }

    /**
     * 获取查询model
     * @return DB |  Model | mixed
     */
    public function getQueryModel()
    {
        return $this->model;
    }

    /**
     * 格式化搜索条件
     * @param $searchInfo
     * @return mixed
     */
    public function formatSearchInfo($searchInfo)
    {
        return $searchInfo;
    }

    /**
     * 获取除分页外的sqlmodel
     */
    public function getEndQueryModel(array $searchInfo)
    {
        //自定义查询
        $model = $this->getQueryModel();


        //自定义搜索
        //自带的默认搜索
        $searchInfo = $searchInfo['customSearch'] ?? [];
        $this->searchInfo = $searchInfo;

        $customSearchInfo = $this->setCustomSearchInfo($searchInfo);
        if (!empty($customSearchInfo)) {
            $model = $model->where($customSearchInfo);
        }


        //自定义搜索格式
        $searchInfo = $this->formatSearchInfo($searchInfo);
        //使用搜索
        $searchInfoReturn = $this->getSearchInfo($searchInfo);

        if (!empty($searchInfoReturn)) {
            $model = $this->getSearchInfoWhere($model, $searchInfoReturn);
        }
        //  $model->dd();

        $this->endModel = clone $model;

        return $model;
    }

    /**
     * 获取所有数据
     * @param array $pageInfo
     * @param array $searchInfo
     * @return ResultHelper
     */
    public function list(array $pageInfo, array $searchInfo )
    {



        try {

            // 备注：这里的模糊查询不能命中索引，适合小数量数据查询,大数量查询请覆盖自己实现
            //            if ($this->likeSearch != "" && isset($searchInfo[$this->likeSearch])) {
            //                $like = $searchInfo[$this->likeSearch];
            //                unset($searchInfo[$this->likeSearch]);
            //                $result = $this->model->where($searchInfo)->where($this->likeSearch, "like", "%" . $like . "%")
            //                    ->orderBy('id')->paginate($pageInfo['pageSize'])->toArray();
            //            } else if ($this->sort) {
            //                $result = $this->model->where($searchInfo)->orderBy(($this->sort)[0], ($this->sort)[1])->paginate($pageInfo['pageSize'])->toArray();
            //            } else {
            //                $result = $this->model->where($searchInfo)->orderBy('id')->paginate($pageInfo['pageSize'])->toArray();
            //            }


            $model = $this->getEndQueryModel($searchInfo);

            //自定义排序
            $needDefaultSort = true;
            if (isset($searchInfo['customSort'])) {
                $sort = $this->getSort($searchInfo['customSort']);

                if (!empty($sort)) {
                    $needDefaultSort = false;
                    $model = $model->orderBy($sort[0], $sort[1]);
                    unset($searchInfo['customSort']);
                }
            }

            //默认排序
            if ($needDefaultSort === true) {
                $defaultSort = $this->getDefaultSort();
                $model = $model->orderBy($defaultSort[0], $defaultSort[1]);
            }

            if($this->simplePaginate){
                $result = $model->simplePaginate($pageInfo['pageSize'])->toArray();
                $result['total'] = $this->total;
            }else{
                $result = $model->paginate($pageInfo['pageSize'])->toArray();
            }

            $result = $this->listAfter($result);


            $result = $this->tableData(Response::HTTP_OK, '获取分页数据成功！', $result);
        } catch (\Exception $ex) {
            report($ex);
            $result = $this->failed(Response::HTTP_INTERNAL_SERVER_ERROR, $ex->getMessage());
        }
        return $result;
    }

    /**
     * 获取所有数据model
     * @param array $searchInfo
     * @return ResultHelper
     */
    public function listModel(array $searchInfo)
    {
        try {

            // 备注：这里的模糊查询不能命中索引，适合小数量数据查询,大数量查询请覆盖自己实现
            //            if ($this->likeSearch != "" && isset($searchInfo[$this->likeSearch])) {
            //                $like = $searchInfo[$this->likeSearch];
            //                unset($searchInfo[$this->likeSearch]);
            //                $result = $this->model->where($searchInfo)->where($this->likeSearch, "like", "%" . $like . "%")
            //                    ->orderBy('id')->paginate($pageInfo['pageSize'])->toArray();
            //            } else if ($this->sort) {
            //                $result = $this->model->where($searchInfo)->orderBy(($this->sort)[0], ($this->sort)[1])->paginate($pageInfo['pageSize'])->toArray();
            //            } else {
            //                $result = $this->model->where($searchInfo)->orderBy('id')->paginate($pageInfo['pageSize'])->toArray();
            //            }
            //自定义查询
            $model = $this->getQueryModel();

            //自定义排序
            $needDefaultSort = true;
            if (isset($searchInfo['customSort'])) {
                $sort = $this->getSort($searchInfo['customSort']);
                if (!empty($sort)) {
                    $needDefaultSort = false;
                    $model = $model->orderBy($sort[0], $sort[1]);
                    unset($searchInfo['customSort']);
                }
            }
            //默认排序
            if ($needDefaultSort === true) {
                $defaultSort = $this->getDefaultSort();
                $model = $model->orderBy($defaultSort[0], $defaultSort[1]);
            }

            //自定义搜索
            //自带的默认搜索
            $searchInfo = $searchInfo['customSearch'] ?? [];

            $customSearchInfo = $this->setCustomSearchInfo($searchInfo);
            if (!empty($customSearchInfo)) {
                $model = $model->where($customSearchInfo);
            }


            //自定义搜索格式
            $searchInfo = $this->formatSearchInfo($searchInfo);
            //使用搜索
            $searchInfoReturn = $this->getSearchInfo($searchInfo);

            if (!empty($searchInfoReturn)) {
                $model = $this->getSearchInfoWhere($model, $searchInfoReturn);
            }

            return $model;
        } catch (\Exception $ex) {
            report($ex);
            $result = $this->failed(Response::HTTP_INTERNAL_SERVER_ERROR, $ex->getMessage());
        }
        return $result;
    }

    /**
     * list后置方法
     * @param $result
     * @return mixed
     */
    public function listAfter($result){
        return $result;
    }

    /**
     * 添加数据
     * @param array $data
     * @return ResultHelper
     */
    public function create(array $data)
    {
        try {
            $result = $this->model->fill($data)->save();
            $result = $this->success(Response::HTTP_OK, '添加数据成功！', $result);
        } catch (\Exception $ex) {
            report($ex);
            $result = $this->failed(Response::HTTP_INTERNAL_SERVER_ERROR, $ex->getMessage());
        }
        return $result;
    }

    /**
     * 指定ID删除数据
     * @param string $id
     * @return ResultHelper
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $result = $this->model->destroy($id);
            $result = $this->success(Response::HTTP_OK, '删除数据成功', $result);
            DB::commit();
        } catch (\Exception $ex) {
            report($ex);
            $result = $this->failed(Response::HTTP_INTERNAL_SERVER_ERROR, $ex->getMessage());
            DB::rollBack();
        }
        return $result;
    }

    /**
     * 指定ID查询数据
     * @param string $id
     * @return ResultHelper
     */
    public function find($id)
    {
        try {
            $model = $this->getQueryModel();
            $where = [$this->model->getTable() . '.' . $this->model->getKeyName() => $id];
            $result = $model->where($where)->first();
            $result = $this->success(Response::HTTP_OK, '查询数据成功', $result);
        } catch (\Exception $ex) {
            report($ex);
            $result = $this->failed(Response::HTTP_INTERNAL_SERVER_ERROR, $ex->getMessage());
        }
        return $result;
    }



    /**
     * 指定ID更新数据
     */
    public function update($id, $data)
    {
        try {
            $model = $this->model->find($id);
            $result = $model->update($data);
            $result = $this->success(Response::HTTP_OK, '编辑数据成功', $result);
        } catch (\Exception $ex) {
            report($ex);
            $result = $this->failed(Response::HTTP_INTERNAL_SERVER_ERROR, $ex->getMessage());
        }
        return $result;
    }
}
