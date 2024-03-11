<?php

namespace App\Http\Controllers;

use App\Utils\ResultHelper;
use Illuminate\Http\Request;

class CustomController  extends Controller
{
    use ResultHelper;
    /**
     * 公共方法
     */
    protected  $server;

    /**
     * 获取所有数据
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function all(Request $request)
    {
        $data = $request->all();
        $result = $this->server->all($data);
        return response()->json($result);
    }

    /**
     * 获取所有分页数据
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        ini_set("memory_limit",'-1');
        // 对请求数据进行分流
        $params = $request->all();
        $this->pageInfo['page'] = $params['page'] ?? 1;
        $this->pageInfo['pageSize'] = $params['pageSize'] ?? 10;
        unset($params['page']);
        unset($params['pageSize']);
        $this->searchInfo = $params;
        $result = $this->server->list($this->pageInfo, $this->searchInfo);
        return response()->json($result);
    }

    /**
     * 增加
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $data = $request->all();
        $result = $this->server->create($data);
        return response()->json($result);
    }

    /**
     * 指定ID删除
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        // 判断如果是数组
        if (substr($id, 0, 1) == "[" && substr($id, -1, 1) == "]") {
            $id = substr($id, 1, strlen($id) - 2);
            $id = explode(",", $id);
        }
        $result = $this->server->destroy($id);
        return response()->json($result);
    }

    /**
     * 指定ID查找
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function find(string $id)
    {
        @ini_set("memory_limit",'-1');
        $result = $this->server->find($id);
        return response()->json($result);
    }

    /**
     * 指定ID更新
     * @param string $id
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id)
    {
        $data = $request->all();
        $result = $this->server->update($id, $data);
        return response()->json($result);
    }
}
