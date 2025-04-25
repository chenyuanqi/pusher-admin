<template>
  <div class="login-container">
    <!-- 左侧品牌区域 -->
    <div class="brand-area">
      <div class="logo-container">
        <img src="../assets/logo.svg" alt="推客后台" class="logo">
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
          ref="loginFormRef"
          :model="loginForm"
          :rules="loginRules"
          @submit.prevent="handleSubmit"
        >
          <!-- 用户名输入框 -->
          <el-form-item prop="username">
            <el-input
              v-model="loginForm.username"
              placeholder="请输入管理员用户名"
              :prefix-icon="User"
            />
          </el-form-item>

          <!-- 密码输入框 -->
          <el-form-item prop="password">
            <el-input
              v-model="loginForm.password"
              type="password"
              placeholder="请输入密码"
              :prefix-icon="Lock"
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

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { User, Lock } from '@element-plus/icons-vue'
import { ElMessage } from 'element-plus'
import type { FormInstance } from 'element-plus'

// 路由实例
const router = useRouter()

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
const loginFormRef = ref<FormInstance>()

// 表单提交处理
const handleSubmit = async () => {
  // 重置错误消息
  errorMessage.value = ''

  if (!loginFormRef.value) return

  // 表单验证
  await loginFormRef.value.validate(async (valid) => {
    if (!valid) {
      return false
    }

    // 设置加载状态
    loading.value = true

    try {
      // 模拟登录API
      if (loginForm.username === 'admin' && loginForm.password === 'admin123') {
        localStorage.setItem('token', 'demo-token')
        localStorage.setItem('userInfo', JSON.stringify({
          username: loginForm.username,
          role: 'administrator'
        }))
        
        // 处理记住我选项
        if (loginForm.remember) {
          localStorage.setItem('remember_username', loginForm.username)
        } else {
          localStorage.removeItem('remember_username')
        }
        
        // 登录成功消息
        ElMessage.success('登录成功')
        
        // 路由跳转
        router.push({ name: 'Transform' })
      } else {
        throw new Error('用户名或密码错误')
      }
    } catch (error: any) {
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
onMounted(() => {
  initRememberedUser()
})
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
  margin-top: 1rem;
  text-align: center;
}

.copyright {
  margin-top: 2rem;
  color: #909399;
  font-size: 14px;
}

/* 响应式设计 */
@media (max-width: 768px) {
  .login-container {
    flex-direction: column;
  }

  .brand-area {
    padding: 1.5rem;
  }

  .form-area {
    padding: 1rem;
  }
}
</style>
