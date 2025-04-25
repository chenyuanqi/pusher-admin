# 前端登录页面实现示例

## 登录页面 Vue 组件 (Login.vue)

```vue
<template>
  <div class="login-container">
    <!-- 左侧品牌区域 -->
    <div class="brand-area">
      <div class="logo-container">
        <img src="@/assets/logo.png" alt="推客后台" class="logo">
        <h2 class="brand-name">推客后台</h2>
      </div>
      <p class="slogan">助力您的推广业务</p>
    </div>

    <!-- 右侧登录表单区域 -->
    <div class="form-area">
      <div class="form-card">
        <h2 class="form-title">推客后台管理系统</h2>
        
        <!-- 登录表单 -->
        <el-form 
          ref="loginForm" 
          :model="loginForm" 
          :rules="loginRules" 
          @submit.prevent="handleSubmit"
        >
          <!-- 用户名输入框 -->
          <el-form-item prop="username">
            <el-input
              v-model="loginForm.username"
              placeholder="请输入管理员用户名"
              prefix-icon="el-icon-user"
            />
          </el-form-item>
          
          <!-- 密码输入框 -->
          <el-form-item prop="password">
            <el-input
              v-model="loginForm.password"
              type="password"
              placeholder="请输入密码"
              prefix-icon="el-icon-lock"
              show-password
            />
          </el-form-item>
          
          <!-- 记住我选项 -->
          <el-form-item>
            <el-checkbox v-model="loginForm.remember">记住我</el-checkbox>
          </el-form-item>
          
          <!-- 登录按钮 -->
          <el-form-item>
            <el-button 
              type="primary" 
              class="login-button" 
              native-type="submit" 
              :loading="loading"
            >
              {{ loading ? '登录中...' : '登录' }}
            </el-button>
          </el-form-item>
        </el-form>
        
        <!-- 错误提示 -->
        <div v-if="errorMessage" class="error-message">
          {{ errorMessage }}
        </div>
      </div>
      
      <!-- 版权信息 -->
      <div class="copyright">
        &copy; {{ new Date().getFullYear() }} 推客后台管理系统
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '@/stores/user'
import { ElMessage } from 'element-plus'

// 路由实例
const router = useRouter()

// 用户状态管理
const userStore = useUserStore()

// 响应式状态
const loginForm = reactive({
  username: '',
  password: '',
  remember: false
})

const loading = ref(false)
const errorMessage = ref('')

// 表单验证规则
const loginRules = {
  username: [
    { required: true, message: '请输入用户名', trigger: 'blur' },
    { min: 3, max: 20, message: '长度在 3 到 20 个字符', trigger: 'blur' }
  ],
  password: [
    { required: true, message: '请输入密码', trigger: 'blur' },
    { min: 6, max: 20, message: '长度在 6 到 20 个字符', trigger: 'blur' }
  ]
}

// 表单引用
const loginFormRef = ref(null)

// 表单提交处理
const handleSubmit = async () => {
  // 重置错误消息
  errorMessage.value = ''
  
  // 表单验证
  await loginFormRef.value.validate(async (valid) => {
    if (!valid) {
      return false
    }
    
    // 设置加载状态
    loading.value = true
    
    try {
      // 调用登录 API
      const result = await userStore.login({
        username: loginForm.username,
        password: loginForm.password
      })
      
      // 处理记住我选项
      if (loginForm.remember) {
        localStorage.setItem('remember_username', loginForm.username)
      } else {
        localStorage.removeItem('remember_username')
      }
      
      // 登录成功消息
      ElMessage.success('登录成功')
      
      // 路由跳转
      router.push({ name: 'Dashboard' })
    } catch (error) {
      // 处理登录错误
      console.error('登录失败:', error)
      errorMessage.value = error.message || '用户名或密码错误'
      ElMessage.error(errorMessage.value)
    } finally {
      // 重置加载状态
      loading.value = false
    }
  })
}

// 初始化时检查是否有记住的用户名
const initRememberedUser = () => {
  const rememberedUsername = localStorage.getItem('remember_username')
  if (rememberedUsername) {
    loginForm.username = rememberedUsername
    loginForm.remember = true
  }
}

// 组件挂载时执行初始化
initRememberedUser()
</script>

<style scoped>
.login-container {
  display: flex;
  height: 100vh;
}

/* 左侧品牌区域样式 */
.brand-area {
  flex: 1;
  background-color: #1e80ff;
  color: white;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 2rem;
}

.logo-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 2rem;
}

.logo {
  width: 120px;
  height: 120px;
  margin-bottom: 1rem;
}

.brand-name {
  font-size: 2.5rem;
  font-weight: bold;
  margin: 0;
}

.slogan {
  font-size: 1.5rem;
  opacity: 0.9;
}

/* 右侧表单区域样式 */
.form-area {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  background-color: #f5f7fa;
  padding: 2rem;
}

.form-card {
  width: 100%;
  max-width: 400px;
  background-color: white;
  border-radius: 8px;
  padding: 2rem;
  box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
}

.form-title {
  text-align: center;
  margin-bottom: 2rem;
  color: #303133;
}

.login-button {
  width: 100%;
  padding: 12px 0;
  font-size: 16px;
}

.error-message {
  color: #f56c6c;
  text-align: center;
  margin-top: 1rem;
}

.copyright {
  margin-top: 2rem;
  color: #909399;
  font-size: 0.8rem;
}

/* 响应式设计 */
@media (max-width: 768px) {
  .login-container {
    flex-direction: column;
  }
  
  .brand-area {
    height: 30vh;
    padding: 1rem;
  }
  
  .form-area {
    height: 70vh;
  }
}
</style>
```

## 用户状态管理 (Pinia Store)

```js
// src/stores/user.js
import { defineStore } from 'pinia'
import { ref } from 'vue'
import { login as apiLogin, logout as apiLogout, getUserInfo } from '@/api/auth'

export const useUserStore = defineStore('user', () => {
  // 状态
  const token = ref(localStorage.getItem('token') || '')
  const userInfo = ref(null)
  
  // 登录方法
  const login = async (credentials) => {
    try {
      const response = await apiLogin(credentials)
      
      // 提取令牌和用户信息
      const { token: authToken, user } = response.data
      
      // 存储令牌和用户信息
      token.value = authToken
      userInfo.value = user
      
      // 保存令牌到本地存储
      localStorage.setItem('token', authToken)
      
      return response
    } catch (error) {
      console.error('登录失败:', error)
      throw error
    }
  }
  
  // 登出方法
  const logout = async () => {
    try {
      // 调用API登出
      if (token.value) {
        await apiLogout()
      }
      
      // 清理状态和本地存储
      resetUserState()
      
      return true
    } catch (error) {
      console.error('登出失败:', error)
      throw error
    }
  }
  
  // 获取用户信息
  const fetchUserInfo = async () => {
    if (!token.value) {
      throw new Error('未授权，请先登录')
    }
    
    try {
      const response = await getUserInfo()
      userInfo.value = response.data
      return response
    } catch (error) {
      console.error('获取用户信息失败:', error)
      
      // 如果是401错误，清理用户状态
      if (error.response && error.response.status === 401) {
        resetUserState()
      }
      
      throw error
    }
  }
  
  // 重置用户状态
  const resetUserState = () => {
    token.value = ''
    userInfo.value = null
    localStorage.removeItem('token')
  }
  
  // 判断用户是否已登录
  const isLoggedIn = () => {
    return !!token.value
  }
  
  return {
    token,
    userInfo,
    login,
    logout,
    fetchUserInfo,
    isLoggedIn
  }
})
```

## API 客户端封装

```js
// src/api/auth.js
import request from '@/utils/request'

/**
 * 用户登录
 * @param {Object} data - 包含 username 和 password 的对象
 * @returns {Promise}
 */
export function login(data) {
  return request({
    url: '/auth/login',
    method: 'post',
    data
  })
}

/**
 * 用户登出
 * @returns {Promise}
 */
export function logout() {
  return request({
    url: '/auth/logout',
    method: 'post'
  })
}

/**
 * 获取当前用户信息
 * @returns {Promise}
 */
export function getUserInfo() {
  return request({
    url: '/auth/me',
    method: 'get'
  })
}
```

## HTTP 请求封装

```js
// src/utils/request.js
import axios from 'axios'
import { ElMessage } from 'element-plus'

// 创建 axios 实例
const service = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api/v1',
  timeout: 15000
})

// 请求拦截器
service.interceptors.request.use(
  (config) => {
    // 从本地存储获取令牌
    const token = localStorage.getItem('token')
    
    // 如果有令牌，添加到请求头
    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`
    }
    
    return config
  },
  (error) => {
    console.error('请求错误:', error)
    return Promise.reject(error)
  }
)

// 响应拦截器
service.interceptors.response.use(
  (response) => {
    const res = response.data
    
    // 检查响应码
    if (res.code !== 200) {
      // 显示错误消息
      ElMessage.error(res.message || '请求失败')
      
      // 处理特定错误码
      if (res.code === 401) {
        // 未授权，重定向到登录页
        localStorage.removeItem('token')
        location.href = '/login'
      }
      
      return Promise.reject(new Error(res.message || '请求失败'))
    }
    
    return res
  },
  (error) => {
    console.error('响应错误:', error)
    
    // 处理网络错误
    let message = '网络错误，请稍后重试'
    
    if (error.response) {
      // 有响应但状态码不是2xx
      const status = error.response.status
      
      switch (status) {
        case 400:
          message = '请求参数错误'
          break
        case 401:
          message = '未授权，请重新登录'
          // 未授权，重定向到登录页
          localStorage.removeItem('token')
          location.href = '/login'
          break
        case 403:
          message = '权限不足，拒绝访问'
          break
        case 404:
          message = '请求的资源不存在'
          break
        case 500:
          message = '服务器内部错误'
          break
        default:
          message = `请求失败(${status})`
      }
    } else if (error.request) {
      // 请求已发送但没有收到响应
      message = '服务器无响应，请检查网络'
    }
    
    ElMessage.error(message)
    return Promise.reject(error)
  }
)

export default service
``` 