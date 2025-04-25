# 推客后台系统架构设计

## 项目概述
推客后台是一个管理推客相关业务的后台系统，主要功能包括管理员登录和文案转换。系统采用前后端分离架构，后端使用PHP，前端使用Vue.js。

## 技术栈

### 后端
- 编程语言：PHP 8.0+
- 框架：Laravel 10
- 数据库：MySQL 8.0
- API：RESTful API

### 前端
- 框架：Vue.js 3
- UI组件库：Element Plus
- 构建工具：Vite
- HTTP客户端：Axios
- 状态管理：Pinia

## 系统架构

### 目录结构

```
pusher-admin/
├── backend/                  # 后端代码
│   ├── app/                  # 应用核心代码
│   │   ├── Http/             # HTTP相关代码
│   │   │   ├── Controllers/  # 控制器
│   │   │   ├── Middleware/   # 中间件
│   │   │   └── Requests/     # 表单验证
│   │   ├── Models/           # 数据模型
│   │   └── Services/         # 业务逻辑服务
│   ├── config/               # 配置文件
│   │   └── admin.php         # 管理员配置文件
│   ├── database/             # 数据库相关
│   ├── routes/               # 路由定义
│   └── ...
├── frontend/                 # 前端代码
│   ├── public/               # 静态资源
│   ├── src/                  # 源代码
│   │   ├── api/              # API接口封装
│   │   ├── assets/           # 静态资源
│   │   ├── components/       # 公共组件
│   │   ├── router/           # 路由配置
│   │   ├── stores/           # 状态管理
│   │   ├── views/            # 页面视图
│   │   │   ├── login/        # 登录页面
│   │   │   └── transform/    # 转换文案页面
│   │   ├── App.vue           # 根组件
│   │   └── main.js           # 入口文件
│   ├── index.html            # HTML模板
│   ├── package.json          # 依赖配置
│   └── vite.config.js        # Vite配置
└── README.md                 # 项目说明
```

## 功能模块设计

### 1. 登录模块

#### 后端设计
- 登录验证通过配置文件方式实现，无需数据库
- 配置文件定义管理员账号和密码
- 使用JWT进行身份验证，生成令牌返回给前端

#### 前端设计
- 登录页面包含用户名和密码输入框
- 表单验证（必填项、长度限制等）
- 登录成功后将JWT令牌存储在本地，并跳转到主页
- 全局路由守卫进行登录状态检查

### 2. 文案转换模块

#### 后端设计
- 提供文案转换API接口
- 支持不同类型的文案转换规则
- 提供历史记录功能

#### 前端设计
- 文案输入区域
- 转换类型选择
- 转换按钮
- 结果展示区域
- 历史记录列表

## API接口设计

### 1. 登录接口
- 请求方式：POST
- 路径：/api/login
- 参数：
  - username: 用户名
  - password: 密码
- 返回：
  - token: JWT令牌
  - expires_in: 过期时间

### 2. 文案转换接口
- 请求方式：POST
- 路径：/api/transform
- 参数：
  - content: 需要转换的内容
  - type: 转换类型
- 返回：
  - transformed_content: 转换后的内容

### 3. 获取文案转换历史
- 请求方式：GET
- 路径：/api/transform/history
- 参数：
  - page: 页码
  - limit: 每页数量
- 返回：
  - items: 历史记录列表
  - total: 总数
  - page: 当前页
  - limit: 每页数量

## 安全设计
- 使用JWT进行身份验证
- API接口鉴权
- 表单验证防止恶意输入
- CSRF防护
- XSS防护

## 部署方案
- 后端: Nginx + PHP-FPM
- 前端: 静态文件部署在Nginx
- 数据库: MySQL独立部署

## 开发流程
1. 环境搭建
2. 后端API开发
3. 前端页面开发
4. 接口联调
5. 功能测试
6. 上线部署 