<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Firebase\JWT\JWT;

class AuthController extends Controller
{
    /**
     * 用户登录
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');

        // 验证请求数据
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => '请求参数验证失败',
                'data' => [
                    'errors' => $validator->errors(),
                ],
            ], 400);
        }

        // 从配置文件获取管理员列表
        $admins = config('admin.admins');

        // 查找匹配的管理员
        $admin = null;
        foreach ($admins as $adminConfig) {
            if ($adminConfig['username'] === $username && $adminConfig['password'] === $password) {
                $admin = $adminConfig;
                break;
            }
        }

        // 如果未找到匹配的管理员，返回错误
        if (!$admin) {
            return response()->json([
                'code' => 401,
                'message' => '用户名或密码错误',
                'data' => null,
            ], 401);
        }

        // 生成JWT令牌
        $tokenPayload = [
            'sub' => $admin['username'],
            'role' => $admin['role'],
            'name' => $admin['name'],
            'iat' => time(),
            'exp' => time() + config('admin.jwt.ttl'),
        ];

        $token = JWT::encode($tokenPayload, config('admin.jwt.secret'), 'HS256');

        // 返回成功响应
        return response()->json([
            'code' => 200,
            'message' => '登录成功',
            'data' => [
                'token' => $token,
                'expires_in' => config('admin.jwt.ttl'),
                'user' => [
                    'username' => $admin['username'],
                    'role' => $admin['role'],
                    'name' => $admin['name'],
                    'last_login' => now()->toDateTimeString(),
                ],
            ],
        ]);
    }

    /**
     * 退出登录
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        // JWT是无状态的，客户端只需要删除token即可
        // 这里仅返回成功响应
        return response()->json([
            'code' => 200,
            'message' => '退出登录成功',
            'data' => null,
        ]);
    }

    /**
     * 获取当前用户信息
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request)
    {
        // 从请求中获取用户信息
        $user = $request->user;

        return response()->json([
            'code' => 200,
            'message' => '操作成功',
            'data' => [
                'username' => $user['username'],
                'role' => $user['role'],
                'name' => $user['name'],
            ],
        ]);
    }
}
