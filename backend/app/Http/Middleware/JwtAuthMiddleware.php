<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Illuminate\Http\Request;

class JwtAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // 从请求头中获取令牌
            $token = $request->bearerToken();

            if (!$token) {
                return response()->json([
                    'code' => 401,
                    'message' => '未提供授权令牌',
                    'data' => null,
                ], 401);
            }

            // 解码并验证令牌
            $decoded = JWT::decode($token, new Key(config('admin.jwt.secret'), 'HS256'));

            // 检查令牌是否过期
            if (time() > $decoded->exp) {
                return response()->json([
                    'code' => 401,
                    'message' => '令牌已过期',
                    'data' => null,
                ], 401);
            }

            // 将用户信息添加到请求中
            $request->merge(['user' => [
                'username' => $decoded->sub,
                'role' => $decoded->role,
                'name' => $decoded->name,
            ]]);

            return $next($request);
        } catch (ExpiredException $e) {
            return response()->json([
                'code' => 401,
                'message' => '令牌已过期',
                'data' => null,
            ], 401);
        } catch (SignatureInvalidException $e) {
            return response()->json([
                'code' => 401,
                'message' => '令牌签名无效',
                'data' => null,
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 401,
                'message' => '无效的令牌',
                'data' => null,
            ], 401);
        }
    }
}
