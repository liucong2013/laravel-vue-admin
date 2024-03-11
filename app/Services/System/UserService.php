<?php

namespace App\Services\System;


use App\Http\Controllers\System\UserModel;
use App\Models\Common\UserPasswordModel;
use App\Services\Service;
use App\Utils\Helper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class UserService extends Service
{

    const ADMIN_LOGIN_COUNT_CACHE_KEY = 'admin_login_count';


    public function __construct(UserModel $model)
    {
        $this->model = $model;
    }


    /**
     * 用户注册
     * @param $data
     * @return array
     */
    public function register($data)
    {
        DB::beginTransaction();
        try {
            // 1.username查重
            if ($this->model->where(['username' => $data['username']])->count()) {
                return $this->failed(Response::HTTP_INTERNAL_SERVER_ERROR, "用户名已存在，请更换", []);
            }
            $userData = [
                'username'     => $data['username'],
                'uuid'         => $data['uuid'],
                'header_img'   => $data['header_img'],
                'authority_id' => $data['authority_id'],
            ];
            // 2.保存数据
            $this->model->fill($userData)->save();

            //写入密码表
            $pwData['uid'] = $this->model->id;
            $pwData['type'] = UserPasswordModel::TYPE_ADMIN;
            $pwData['password'] = Helper::createPassword($data['password']);
            (new UserPasswordModel())->fill($pwData)->save();

            DB::commit();
            $result = $this->success(Response::HTTP_OK, '数据保存成功', $this->model);
        } catch (\Exception $ex) {
            report($ex);
            DB::rollBack();
            $result = $this->failed(Response::HTTP_INTERNAL_SERVER_ERROR, $ex->getMessage());
        }
        return $result;
    }

    /**
     * 用户登录
     * @param array $data
     * @return array $result
     */
    public function login(array $data)
    {
        try {
            // 1.查找用户
            $result = $this->model
                ->leftJoin('users_password', 'users_password.uid', 'sys_users.id')
                ->select([
                    'sys_users.id',
                    'sys_users.username',
                    'sys_users.nick_name',
                    'sys_users.header_img',
                    'sys_users.created_at',
                    'users_password.password'
                ])
                ->whereRaw("sys_users.username='{$data['username']}'")
                ->where('users_password.type', UserPasswordModel::TYPE_ADMIN)
                ->orderBy('sys_users.id', 'desc')
                ->first();
            // 2.校验密码
            if (empty($result) || empty($result->password)) {
                return $this->failed(Response::HTTP_BAD_REQUEST, '当前用户不存在,请使用验证码登录');
            }
            $key = self::ADMIN_LOGIN_COUNT_CACHE_KEY . $result->id;
            $loginCount = Cache::get($key);

            if (!empty($loginCount)) {
                $loginCount = json_decode($loginCount, true);
                if ($loginCount['count'] == 2) {
                    return $this->failed(Response::HTTP_INTERNAL_SERVER_ERROR, '您输入的密码不正确,请等待30分钟后才能输入');
                }
            }

            if ($result->password != Helper::createPassword($data['password'])) {
                if (empty($loginCount)) {
                    Cache::add($key, json_encode([
                        'count' => 1,
                        'time'  => time()
                    ]), 1800);
                    return $this->failed(Response::HTTP_INTERNAL_SERVER_ERROR, '您输入的密码不正确,您还有2次输入机会');
                } else {
                    if ($loginCount['count'] == 1) {
                        Cache::forget($key);
                        Cache::add($key, json_encode([
                            'count' => 2,
                            'time'  => time()
                        ]), 1800);
                        return $this->failed(Response::HTTP_INTERNAL_SERVER_ERROR, '您输入的密码不正确,您还有1次输入机会');
                    }
                }
                return $this->failed(Response::HTTP_INTERNAL_SERVER_ERROR, '您输入的密码不正确');
            }
            // 3.签发token
            unset($result->password);
            $result->token = Auth::login($result);
            $result = $this->success(Response::HTTP_OK, '登录成功', $result);
            Cache::forget($key);
        } catch (\Exception $ex) {
            report($ex);
            $result = $this->failed(Response::HTTP_INTERNAL_SERVER_ERROR, $ex->getMessage());
        }
        return $result;
    }


    /**
     * 删除管理员
     * @param $userId
     * @return mixed
     */
    public function destroyAdmin($userId)
    {
        //清除登录错误限制登录时间缓存
        $key = self::ADMIN_LOGIN_COUNT_CACHE_KEY . $userId;
        Cache::forget($key);
        return ($this->model->where('id', $userId)->update(['authority_id' => 0]) && UserPasswordModel::where([
                'uid'  => $userId,
                'type' => UserPasswordModel::TYPE_ADMIN
            ])->update(['password' => '']));
    }

    /**
     * 用户列表
     * @param array $pageInfo
     * @return array
     */
    public function userList($pageInfo)
    {
        try {
            $result = $this->model->with('authority')->where([
                [
                    'authority_id',
                    '>',
                    0
                ]
            ])->paginate($pageInfo['pageSize'])->toArray();
            $result = $this->tableData(Response::HTTP_OK, '获取成功', $result);
        } catch (\Exception $ex) {
            $result = $this->failed(Response::HTTP_INTERNAL_SERVER_ERROR, $ex->getMessage());
        }
        return $result;
    }


    /**
     * app用户列表联表
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|mixed
     */
    public function getQueryModel()
    {
        return $this->model->appAllUserListQueryModel();
    }

    /**
     * 格式化搜索条件
     * @param $searchInfo
     * @return mixed
     */
    public function formatSearchInfo($searchInfo)
    {
        if (isset($searchInfo['nick_name'])) {
            $searchInfo['sys_users.nick_name'] = $searchInfo['nick_name'];
            unset($searchInfo['nick_name']);
        }
        if (isset($searchInfo['identity_id'])) {
            $searchInfo['user_identity.id'] = $searchInfo['identity_id'];
            unset($searchInfo['identity_id']);
        }
        if (isset($searchInfo['created_at'])) {
            $searchInfo['sys_users.created_at'] = $searchInfo['created_at'];
            unset($searchInfo['created_at']);
        }
        return $searchInfo;
    }

    /**
     * 获取自定义搜索的设置,默认是like
     * @return array
     */
    public function setSearchInfo(): array
    {
        $return['mobile'] = [
            'like',
            '%&s%'
        ];
        $return['sys_users.nick_name'] = [
            'like',
            '%&s%'
        ];
        $return['sys_users.created_at'] = ['between'];
        return $return;
    }


    /**
     * 用户重置角色
     * @param string $uuid
     * @param array $data
     * @return array
     */
    public function setAuthority($uuid, $data)
    {
        try {
            $result = $this->model->where('uuid', $uuid)->update($data);
            $result = $this->success(Response::HTTP_OK, '重置角色成功', $result);
        } catch (\Exception $ex) {
            $result = $this->failed(Response::HTTP_INTERNAL_SERVER_ERROR, $ex->getMessage());
        }
        return $result;
    }


    /**
     * 设置管理员
     * @param $data
     * @return array
     */
    public function setAdmin($data)
    {
        //查找是否已经是管理员
        $info = $this->model->leftjoin('users_password', 'users_password.uid', 'sys_users.id')
            ->select([
                'sys_users.id',
                'sys_users.authority_id',
                'users_password.id as users_password_id'
            ])
            ->where([
                'sys_users.id'        => $data['id'],
                'users_password.type' => UserPasswordModel::TYPE_ADMIN
            ])
            ->first();

        if (!empty($info) && $info->authority_id > 0) {
            return $this->failed(Response::HTTP_INTERNAL_SERVER_ERROR, trans('用户已经是管理员，不需要重新设置'));
        }
        DB::beginTransaction();
        try {
            $userPasswordModel = new UserPasswordModel();
            $pwData['password'] = Helper::createPassword($data['password']);
            if (empty($info)) {
                $pwData['uid'] = $data['id'];
                $pwData['type'] = UserPasswordModel::TYPE_ADMIN;
                if (!$userPasswordModel->fill($pwData)->save()) {
                    throw new \Exception(trans('用户已经是管理员，不需要重新设置'));
                }
            } else {
                if (!$userPasswordModel->where('id', $info->users_password_id)->update(['password' => $pwData['password']])) {
                    throw new \Exception(trans('设置管理员密码失败'));
                }
            }

            if (!$this->model->where('id', $data['id'])->update(['authority_id' => $data['authority_id']])) {
                throw new \Exception(trans('角色绑定失败'));
            }
            DB::commit();
            return $this->success(Response::HTTP_OK, trans('设置管理员成功'));
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->failed(Response::HTTP_INTERNAL_SERVER_ERROR, $ex->getMessage());
        }

    }

    /**
     * 修改管理员密码
     * @param $data
     * @return array
     */
    public function changePassword($data)
    {
        //查找是否已经是管理员
        $info = UserPasswordModel::where([
            'users_password.uid'  => $data['admin_id'],
            'users_password.type' => UserPasswordModel::TYPE_ADMIN
        ])
            ->select([
                'users_password.id',
                'users_password.password'
            ])->first();

        if (empty($info)) {
            return $this->failed(Response::HTTP_INTERNAL_SERVER_ERROR, trans('管理员不存在'));
        }

        $password = Helper::createPassword($data['password']);
        if ($password != $info->password) {
            return $this->failed(Response::HTTP_INTERNAL_SERVER_ERROR, trans('原密码不正确'));
        }
        $newPassword = Helper::createPassword($data['newPassword']);
        if ($newPassword == $info->password) {
            return $this->failed(Response::HTTP_INTERNAL_SERVER_ERROR, trans('跟旧密码一样无需修改'));
        }

        if (!UserPasswordModel::where('id', $info->id)->update(['password' => $newPassword])) {
            return $this->failed(Response::HTTP_INTERNAL_SERVER_ERROR, trans('修改密码失败'));
        }
        return $this->success(Response::HTTP_OK, trans('修改密码成功'));
    }


}
