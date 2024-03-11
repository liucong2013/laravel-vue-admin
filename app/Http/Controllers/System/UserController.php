<?php

namespace App\Http\Controllers\System;


use App\Http\Controllers\Controller;
use App\Http\Controllers\CustomController;
use App\Services\System\UserService;
use App\Utils\Helper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\System\UserRequest;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;


class UserController extends CustomController
{


    protected $server;

    public function __construct(UserService $server)
    {
        $this->server = $server;
    }

    /**
     * 用户注册
     * @param UserRequest $request
     * @return Response
     */
    public function register(UserRequest $request)
    {

        $data = $request->all();
        $messages = [
            'username.required'     => trans('请填写用户名'),
            'password.required'     => trans('请填写密码'),
            'mobile.required'       => trans('请填写手机号'),
            'mobile.integer'        => trans('手机号格式错误'),
            'mobile.digits'         => trans('请输入11位数的手机号'),
            'authority_id.required' => trans('请选择角色'),
            'authority_id.integer'  => trans('角色格式错误'),
        ];
        $valiInfo = Validator::make($data, [
            'username'     => [
                'required',
                function ($attribute, $value, $fail) {
                    if (preg_match('/[\x7f-\xff]/', $value)) {
                        $fail(trans('userIdentity.authUserIdentity.wechat_id_can_not_chinese'));
                    }
                }
            ],
            'password'     => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!preg_match('/^(?![^a-zA-Z]+$)(?!\D+$).{8,}$/', $value)) {
                        $fail(trans('user.register.password_err'));
                    }
                }
            ],
            'mobile'       => [
                'required',
                'integer',
                'digits:11',
                function ($attribute, $value, $fail) {
                    if (!Helper::isMobile($value)) {
                        $fail(trans('common.phone_err'));
                    }
                }
            ],
            'authority_id' => 'required|integer',
        ], $messages);

        //返回错误验证信息
        if ($valiInfo->fails()) {
            return $this->failToJson(Response::HTTP_UNPROCESSABLE_ENTITY, $valiInfo->errors()->first());
        }

        $data['uuid'] = Uuid::uuid1();
        $result = $this->server->register($data);
        return response()->json($result);
    }

    /**
     * 用户登录
     * @param UserRequest $request
     * @return Response
     */
    public function login(UserRequest $request)
    {
        $data = $request->all();
        $result = $this->server->login($data);
        return response()->json($result);
    }

    /**
     * 用户登出
     */
    public function loginOut()
    {
        Auth::logout();
        $result = $this->success(Response::HTTP_OK, "登出成功");
        return response()->json($result);
    }

    /**
     * 后台管理员列表
     * @param Request $request
     * @return Response
     */
    public function userList(Request $request)
    {
        $pageInfo = $request->all();
        $result = $this->server->userList($pageInfo);
        return response()->json($result);
    }






    /**
     * 用户重置角色
     */
    public function setAuthority(Request $request, $uuid)
    {
        $data = $request->all();
        $result = $this->server->setAuthority($uuid, $data);
        return response()->json($result);
    }

    /**
     * 指定ID删除用户
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id)
    {
        if ($this->server->destroyAdmin($id)) {
            return $this->sucToJson(Response::HTTP_OK, '删除管理员成功');
        }
        return $this->failToJson(Response::HTTP_INTERNAL_SERVER_ERROR, '删除管理员失败');
    }





    /**
     * 设置管理员
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setAdmin(Request $request)
    {
        $data = $request->all();
        $messages = [
            'id.required'           => trans('请选择用户'),
            'id.integer'            => trans('用户id格式错误'),
            'password.required'     => trans('请填写密码'),
            'authority_id.required' => trans('请选择角色'),
            'authority_id.integer'  => trans('角色格式错误'),
        ];
        $valiInfo = Validator::make($data, [
            'id'           => 'required|integer',
            'authority_id' => 'required|integer',
            'password'     => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!preg_match('/^(?![^a-zA-Z]+$)(?!\D+$).{8,}$/', $value)) {
                        $fail(trans('user.register.password_err'));
                    }
                }
            ],
        ], $messages);

        //返回错误验证信息
        if ($valiInfo->fails()) {
            return $this->failToJson(Response::HTTP_UNPROCESSABLE_ENTITY, $valiInfo->errors()->first());
        }

        return response()->json($this->server->setAdmin($data));
    }




    /**
     * 修改管理员密码
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        $data = $request->all();
        $messages = [
            'newPassword.required' => trans('请输入新密码'),
            'newPassword.min'      => trans('新密码不能少于6位数'),
            'password.required'    => trans('请输入原密码')
        ];
        $valiInfo = Validator::make($data, [
            'newPassword' => 'required|min:6',
            'password'    => 'required',
        ], $messages);

        //返回错误验证信息
        if ($valiInfo->fails()) {
            return $this->failToJson(Response::HTTP_UNPROCESSABLE_ENTITY, $valiInfo->errors()->first());
        }

        $data['admin_id'] = (Auth::user())->id;
        return response()->json($this->server->changePassword($data));
    }



}
