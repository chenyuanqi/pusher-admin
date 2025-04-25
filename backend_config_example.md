# 后端配置示例

## 管理员配置文件示例 (admin.php)

以下是 Laravel 框架中 `config/admin.php` 文件的示例内容，用于配置管理员账号和系统设置：

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 管理员账号设置
    |--------------------------------------------------------------------------
    |
    | 此配置用于定义系统管理员账号。在生产环境中，建议修改默认密码
    | 并使用环境变量来存储敏感信息。
    |
    */
    'admins' => [
        [
            'username' => env('ADMIN_USERNAME', 'admin'),
            'password' => env('ADMIN_PASSWORD', 'admin123'), // 生产环境中务必更改
            'role' => 'super_admin',
            'name' => '超级管理员',
        ],
        [
            'username' => 'operator',
            'password' => 'operator123', // 生产环境中务必更改
            'role' => 'operator',
            'name' => '运营管理员',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | JWT 设置
    |--------------------------------------------------------------------------
    |
    | JWT 配置用于生成和验证令牌
    |
    */
    'jwt' => [
        'secret' => env('JWT_SECRET', 'your-secret-key'),
        'ttl' => env('JWT_TTL', 86400), // 默认24小时
        'refresh_ttl' => env('JWT_REFRESH_TTL', 604800), // 默认7天
    ],

    /*
    |--------------------------------------------------------------------------
    | 文案转换设置
    |--------------------------------------------------------------------------
    |
    | 配置文案转换相关参数
    |
    */
    'transform' => [
        'types' => [
            [
                'id' => 'emoji',
                'name' => '表情符号转换',
                'description' => '将文本转换为包含表情符号的风格',
                'options' => [
                    [
                        'name' => 'level',
                        'label' => '转换程度',
                        'type' => 'select',
                        'values' => [
                            ['value' => 1, 'label' => '轻度'],
                            ['value' => 2, 'label' => '中度'],
                            ['value' => 3, 'label' => '重度'],
                        ],
                        'default' => 1,
                    ],
                ],
            ],
            [
                'id' => 'formal',
                'name' => '正式文案转换',
                'description' => '将普通文本转换为正式场合使用的文案',
                'options' => [],
            ],
            [
                'id' => 'casual',
                'name' => '轻松文案转换',
                'description' => '将文本转换为轻松活泼的风格',
                'options' => [],
            ],
            [
                'id' => 'social',
                'name' => '社交媒体优化',
                'description' => '针对社交媒体平台优化文案',
                'options' => [
                    [
                        'name' => 'platform',
                        'label' => '平台',
                        'type' => 'select',
                        'values' => [
                            ['value' => 'weibo', 'label' => '微博'],
                            ['value' => 'wechat', 'label' => '微信'],
                            ['value' => 'douyin', 'label' => '抖音'],
                        ],
                        'default' => 'weibo',
                    ],
                ],
            ],
        ],
        'history' => [
            'enable' => true,
            'max_records' => 1000, // 最多保存的历史记录数
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 系统设置
    |--------------------------------------------------------------------------
    |
    | 其他系统相关设置
    |
    */
    'system' => [
        'name' => env('APP_NAME', '推客后台管理系统'),
        'logo' => env('APP_LOGO', '/images/logo.png'),
        'api_rate_limit' => env('API_RATE_LIMIT', 100), // 每分钟API请求限制
    ],
];
```

## 环境变量配置示例 (.env)

以下是 `.env` 文件的示例内容：

```
APP_NAME=推客后台管理系统
APP_ENV=local
APP_KEY=base64:your-app-key
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pusher_admin
DB_USERNAME=root
DB_PASSWORD=

# 管理员配置
ADMIN_USERNAME=admin
ADMIN_PASSWORD=secure_password_here

# JWT配置
JWT_SECRET=your-secret-key-here
JWT_TTL=86400
```

## 用户认证实现流程

### 1. 基于文件配置的认证流程

```php
// AuthController.php
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
```

### 2. JWT 验证中间件

```php
// JwtAuthMiddleware.php
public function handle($request, Closure $next)
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
        $decoded = JWT::decode($token, config('admin.jwt.secret'), ['HS256']);
        
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
    } catch (\Exception $e) {
        return response()->json([
            'code' => 401,
            'message' => '无效的令牌',
            'data' => null,
        ], 401);
    }
}
```

## 文案转换服务示例实现

```php
// TransformService.php
class TransformService
{
    /**
     * 执行文案转换
     *
     * @param string $content 原始内容
     * @param string $type 转换类型
     * @param array $options 附加选项
     * @return array 转换结果
     */
    public function transform($content, $type, $options = [])
    {
        // 获取所有支持的转换类型
        $types = config('admin.transform.types');
        
        // 检查类型是否支持
        $typeConfig = null;
        foreach ($types as $t) {
            if ($t['id'] === $type) {
                $typeConfig = $t;
                break;
            }
        }
        
        if (!$typeConfig) {
            throw new \Exception('不支持的转换类型');
        }
        
        // 根据类型执行不同的转换逻辑
        switch ($type) {
            case 'emoji':
                $result = $this->transformEmoji($content, $options);
                break;
            case 'formal':
                $result = $this->transformFormal($content);
                break;
            case 'casual':
                $result = $this->transformCasual($content);
                break;
            case 'social':
                $result = $this->transformSocial($content, $options);
                break;
            default:
                throw new \Exception('未实现的转换类型');
        }
        
        // 保存历史记录
        if (config('admin.transform.history.enable')) {
            $this->saveHistory($content, $result, $type, $options);
        }
        
        return [
            'original_content' => $content,
            'transformed_content' => $result,
            'type' => $type,
            'transform_id' => time() . rand(1000, 9999), // 简单示例，实际应使用数据库ID
        ];
    }
    
    /**
     * 表情符号转换
     */
    private function transformEmoji($content, $options)
    {
        $level = $options['level'] ?? 1;
        
        // 这里是示例实现，实际项目中应有更复杂的转换逻辑
        $emojiMappings = [
            // 轻度转换
            1 => [
                '好' => '好👍',
                '喜欢' => '喜欢❤️',
                '开心' => '开心😄',
                '是的' => '是的✅',
            ],
            // 中度转换
            2 => [
                '好' => '好👍😊',
                '喜欢' => '超喜欢❤️❤️',
                '开心' => '太开心了😄😆',
                '是的' => '是的没错✅✅',
            ],
            // 重度转换
            3 => [
                '好' => '非常好👍😊👏',
                '喜欢' => '超级喜欢❤️❤️❤️',
                '开心' => '太太太开心了😄😆🎉',
                '是的' => '是的没错绝对的✅✅✅',
            ],
        ];
        
        // 使用当前级别的映射替换内容
        $currentMapping = $emojiMappings[$level] ?? $emojiMappings[1];
        $result = $content;
        
        foreach ($currentMapping as $word => $replacement) {
            $result = str_replace($word, $replacement, $result);
        }
        
        return $result;
    }
    
    /**
     * 正式文案转换
     */
    private function transformFormal($content)
    {
        // 这里是示例实现，实际项目中应有更复杂的转换逻辑
        $formalMappings = [
            '嗨' => '您好',
            '行' => '可以',
            '不行' => '恐怕不太方便',
            '搞定' => '已完成',
            '谢谢' => '非常感谢',
        ];
        
        $result = $content;
        
        foreach ($formalMappings as $word => $replacement) {
            $result = str_replace($word, $replacement, $result);
        }
        
        return $result;
    }
    
    /**
     * 轻松文案转换
     */
    private function transformCasual($content)
    {
        // 示例实现
        $casualMappings = [
            '您好' => '嗨',
            '可以' => '行',
            '不太方便' => '不行',
            '已完成' => '搞定',
            '非常感谢' => '谢啦',
        ];
        
        $result = $content;
        
        foreach ($casualMappings as $word => $replacement) {
            $result = str_replace($word, $replacement, $result);
        }
        
        return $result;
    }
    
    /**
     * 社交媒体文案优化
     */
    private function transformSocial($content, $options)
    {
        $platform = $options['platform'] ?? 'weibo';
        
        // 根据平台应用不同的优化规则
        switch ($platform) {
            case 'weibo':
                // 微博优化：添加标签，表情等
                $result = $content;
                // 添加一些热门标签
                $tags = ['#热门话题#', '#每日分享#'];
                $result .= "\n\n" . implode(' ', $tags);
                break;
                
            case 'wechat':
                // 微信优化：保持简洁，添加阅读提示
                $result = $content;
                $result .= "\n\n👉 点击「在看」支持一下吧";
                break;
                
            case 'douyin':
                // 抖音优化：简短、有节奏感
                $result = $content;
                // 添加抖音常用结尾
                $result .= "\n\n记得点赞关注 @账号名称";
                break;
                
            default:
                $result = $content;
        }
        
        return $result;
    }
    
    /**
     * 保存转换历史
     * 在实际应用中，这应该将数据保存到数据库
     */
    private function saveHistory($originalContent, $transformedContent, $type, $options)
    {
        // 这里应该实现将历史记录保存到数据库的逻辑
        // 简化示例，实际项目中应使用数据库存储
        \Log::info('保存转换历史', [
            'original' => $originalContent,
            'transformed' => $transformedContent,
            'type' => $type,
            'options' => $options,
            'time' => now()->toDateTimeString(),
        ]);
    }
}
``` 