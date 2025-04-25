# 推客后台 API 接口规范

## 通用规范

### 基础URL
所有API请求都应该使用以下基础URL:
```
https://api.example.com/api/v1
```

### 请求头
- Content-Type: application/json
- Authorization: Bearer {token} (除登录接口外所有请求都需要)
- Accept: application/json

### 响应格式
所有API响应都采用JSON格式，结构如下：
```json
{
  "code": 200,
  "message": "操作成功",
  "data": {
    // 具体数据
  }
}
```

### 状态码
- 200: 成功
- 400: 请求参数错误
- 401: 未授权或token过期
- 403: 权限不足
- 404: 资源不存在
- 500: 服务器内部错误

### 分页
对于返回列表的接口，分页参数格式如下：
```json
{
  "code": 200,
  "message": "操作成功",
  "data": {
    "items": [],
    "total": 100,
    "page": 1,
    "limit": 10
  }
}
```

## 接口详细规范

### 1. 登录接口

#### 请求
- 方法: `POST`
- 路径: `/auth/login`
- 参数:
```json
{
  "username": "admin",
  "password": "password123"
}
```

#### 响应
- 成功响应 (200):
```json
{
  "code": 200,
  "message": "登录成功",
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "expires_in": 86400,
    "user": {
      "id": 1,
      "username": "admin",
      "role": "administrator",
      "last_login": "2023-10-01 10:00:00"
    }
  }
}
```

- 失败响应 (401):
```json
{
  "code": 401,
  "message": "用户名或密码错误",
  "data": null
}
```

### 2. 退出登录接口

#### 请求
- 方法: `POST`
- 路径: `/auth/logout`
- 头部: 需要包含Authorization令牌

#### 响应
- 成功响应 (200):
```json
{
  "code": 200,
  "message": "退出登录成功",
  "data": null
}
```

### 3. 获取当前用户信息

#### 请求
- 方法: `GET`
- 路径: `/auth/me`
- 头部: 需要包含Authorization令牌

#### 响应
- 成功响应 (200):
```json
{
  "code": 200,
  "message": "操作成功",
  "data": {
    "id": 1,
    "username": "admin",
    "role": "administrator",
    "last_login": "2023-10-01 10:00:00"
  }
}
```

### 4. 文案转换接口

#### 请求
- 方法: `POST`
- 路径: `/transform`
- 头部: 需要包含Authorization令牌
- 参数:
```json
{
  "content": "需要转换的文案内容",
  "type": "emoji", // 可选值: emoji, formal, casual, social
  "options": {
    // 转换的附加选项，根据type不同有不同的选项
    "level": 1 // 例如转换程度
  }
}
```

#### 响应
- 成功响应 (200):
```json
{
  "code": 200,
  "message": "转换成功",
  "data": {
    "original_content": "需要转换的文案内容",
    "transformed_content": "转换后的文案内容",
    "type": "emoji",
    "transform_id": 123 // 此次转换的ID，可用于历史记录查询
  }
}
```

- 失败响应 (400):
```json
{
  "code": 400,
  "message": "转换类型不支持",
  "data": null
}
```

### 5. 获取文案转换历史记录

#### 请求
- 方法: `GET`
- 路径: `/transform/history`
- 头部: 需要包含Authorization令牌
- 查询参数:
  - page: 页码，默认1
  - limit: 每页数量，默认10
  - type: 转换类型，可选
  - start_date: 开始日期，可选
  - end_date: 结束日期，可选

#### 响应
- 成功响应 (200):
```json
{
  "code": 200,
  "message": "操作成功",
  "data": {
    "items": [
      {
        "id": 123,
        "original_content": "原始内容...",
        "transformed_content": "转换后内容...",
        "type": "emoji",
        "created_at": "2023-10-01 15:30:00"
      },
      // 更多记录...
    ],
    "total": 50,
    "page": 1,
    "limit": 10
  }
}
```

### 6. 获取单条转换历史详情

#### 请求
- 方法: `GET`
- 路径: `/transform/history/{id}`
- 头部: 需要包含Authorization令牌
- 路径参数:
  - id: 转换记录ID

#### 响应
- 成功响应 (200):
```json
{
  "code": 200,
  "message": "操作成功",
  "data": {
    "id": 123,
    "original_content": "原始内容完整版...",
    "transformed_content": "转换后内容完整版...",
    "type": "emoji",
    "options": {
      "level": 1
    },
    "created_at": "2023-10-01 15:30:00",
    "user": {
      "id": 1,
      "username": "admin"
    }
  }
}
```

- 失败响应 (404):
```json
{
  "code": 404,
  "message": "记录不存在",
  "data": null
}
```

### 7. 删除转换历史记录

#### 请求
- 方法: `DELETE`
- 路径: `/transform/history/{id}`
- 头部: 需要包含Authorization令牌
- 路径参数:
  - id: 转换记录ID

#### 响应
- 成功响应 (200):
```json
{
  "code": 200,
  "message": "删除成功",
  "data": null
}
```

### 8. 批量删除转换历史记录

#### 请求
- 方法: `DELETE`
- 路径: `/transform/history/batch`
- 头部: 需要包含Authorization令牌
- 参数:
```json
{
  "ids": [123, 124, 125]
}
```

#### 响应
- 成功响应 (200):
```json
{
  "code": 200,
  "message": "批量删除成功",
  "data": {
    "deleted_count": 3
  }
}
```

### 9. 获取转换类型列表

#### 请求
- 方法: `GET`
- 路径: `/transform/types`
- 头部: 需要包含Authorization令牌

#### 响应
- 成功响应 (200):
```json
{
  "code": 200,
  "message": "操作成功",
  "data": [
    {
      "id": "emoji",
      "name": "表情符号转换",
      "description": "将文本转换为包含表情符号的风格",
      "options": [
        {
          "name": "level",
          "label": "转换程度",
          "type": "select",
          "values": [
            {"value": 1, "label": "轻度"},
            {"value": 2, "label": "中度"},
            {"value": 3, "label": "重度"}
          ],
          "default": 1
        }
      ]
    },
    {
      "id": "formal",
      "name": "正式文案转换",
      "description": "将普通文本转换为正式场合使用的文案",
      "options": []
    },
    // 更多类型...
  ]
}
```

## 错误处理

所有接口在遇到错误时，都应返回适当的HTTP状态码和一个包含错误信息的JSON响应。

### 请求参数验证错误
```json
{
  "code": 400,
  "message": "请求参数验证失败",
  "data": {
    "errors": {
      "content": ["文案内容不能为空"],
      "type": ["转换类型必须是有效的选项"]
    }
  }
}
```

### 未授权错误
```json
{
  "code": 401,
  "message": "未授权或令牌已过期",
  "data": null
}
```

### 服务器内部错误
```json
{
  "code": 500,
  "message": "服务器内部错误",
  "data": null
}
```

## API版本控制

API使用URL路径中的版本号进行版本控制。当前版本为v1，未来版本可能会是v2、v3等。

示例:
- v1: `https://api.example.com/api/v1/auth/login`
- v2: `https://api.example.com/api/v2/auth/login`

## 速率限制

为防止API滥用，我们实施了速率限制。默认情况下，每个API密钥每分钟可以发出100个请求。超过此限制将返回429状态码。

响应头包含以下字段：
- X-RateLimit-Limit: 每个时间窗口允许的最大请求数
- X-RateLimit-Remaining: 当前时间窗口内剩余的请求数
- X-RateLimit-Reset: 当前时间窗口重置时间（UNIX时间戳）

## 开发环境与测试

测试环境API基础URL:
```
https://api-dev.example.com/api/v1
```

测试账号:
- 用户名: test_admin
- 密码: test123456 