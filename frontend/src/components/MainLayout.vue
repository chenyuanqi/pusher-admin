<template>
  <div class="layout-container">
    <!-- 顶部导航栏 -->
    <header class="layout-header">
      <div class="logo-container">
        <img src="../assets/logo.svg" alt="推客后台" class="logo">
        <h1 class="site-title">推客后台管理系统</h1>
      </div>
      <div class="user-info">
        <el-dropdown @command="handleCommand">
          <span class="user-dropdown-link">
            <el-avatar :size="32" icon="UserFilled" />
            <span class="username">{{ userInfo.username || '管理员' }}</span>
            <el-icon><ArrowDown /></el-icon>
          </span>
          <template #dropdown>
            <el-dropdown-menu>
              <el-dropdown-item command="profile">个人信息</el-dropdown-item>
              <el-dropdown-item command="logout" divided>退出登录</el-dropdown-item>
            </el-dropdown-menu>
          </template>
        </el-dropdown>
      </div>
    </header>
    
    <div class="layout-main">
      <!-- 侧边栏菜单 -->
      <aside class="layout-sidebar">
        <el-menu
          :default-active="activeMenuPath"
          class="sidebar-menu"
          :collapse="isCollapse"
          :router="true"
          background-color="#304156"
          text-color="#bfcbd9"
          active-text-color="#409EFF"
        >
          <el-menu-item index="/dashboard">
            <el-icon><HomeFilled /></el-icon>
            <template #title>首页</template>
          </el-menu-item>
          
          <el-sub-menu index="tools">
            <template #title>
              <el-icon><Tools /></el-icon>
              <span>工具</span>
            </template>
            <el-menu-item index="/transform">
              <el-icon><Edit /></el-icon>
              <span>文案转换</span>
            </el-menu-item>
          </el-sub-menu>
          
          <el-menu-item index="/settings">
            <el-icon><Setting /></el-icon>
            <template #title>系统设置</template>
          </el-menu-item>
        </el-menu>
        
        <div class="sidebar-collapse-btn" @click="toggleCollapse">
          <el-icon v-if="isCollapse"><Expand /></el-icon>
          <el-icon v-else><Fold /></el-icon>
        </div>
      </aside>
      
      <!-- 主要内容区 -->
      <main class="layout-content">
        <slot></slot>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { 
  HomeFilled, 
  Tools, 
  Edit, 
  Setting, 
  Expand, 
  Fold, 
  ArrowDown 
} from '@element-plus/icons-vue'
import { ElMessageBox } from 'element-plus'

const router = useRouter()
const route = useRoute()

// 侧边栏折叠状态
const isCollapse = ref(false)

// 用户信息
const userInfo = ref({
  username: '',
  role: ''
})

// 计算当前激活的菜单路径
const activeMenuPath = computed(() => {
  return route.path
})

// 切换侧边栏折叠状态
const toggleCollapse = () => {
  isCollapse.value = !isCollapse.value
}

// 处理用户下拉菜单命令
const handleCommand = (command: string) => {
  if (command === 'logout') {
    ElMessageBox.confirm('确定要退出登录吗？', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    }).then(() => {
      // 清除本地存储的token和用户信息
      localStorage.removeItem('token')
      localStorage.removeItem('userInfo')
      
      // 跳转到登录页
      router.push('/login')
    }).catch(() => {})
  } else if (command === 'profile') {
    // 跳转到个人信息页面
    // router.push('/profile')
  }
}

// 组件挂载时获取用户信息
onMounted(() => {
  const storedUserInfo = localStorage.getItem('userInfo')
  if (storedUserInfo) {
    try {
      userInfo.value = JSON.parse(storedUserInfo)
    } catch (e) {
      console.error('Failed to parse user info:', e)
    }
  }
})
</script>

<style scoped>
.layout-container {
  height: 100vh;
  display: flex;
  flex-direction: column;
}

.layout-header {
  height: 60px;
  background-color: #fff;
  border-bottom: 1px solid #e6e6e6;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 20px;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
}

.logo-container {
  display: flex;
  align-items: center;
}

.logo {
  width: 36px;
  height: 36px;
  margin-right: 10px;
}

.site-title {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
  color: #1e80ff;
}

.user-info {
  display: flex;
  align-items: center;
}

.user-dropdown-link {
  display: flex;
  align-items: center;
  cursor: pointer;
}

.username {
  margin: 0 8px;
  color: #606266;
}

.layout-main {
  flex: 1;
  display: flex;
  overflow: hidden;
}

.layout-sidebar {
  position: relative;
  background-color: #304156;
  height: 100%;
  transition: width 0.3s;
}

.sidebar-menu:not(.el-menu--collapse) {
  width: 200px;
}

.sidebar-menu {
  height: calc(100% - 40px);
  border-right: none;
}

.sidebar-collapse-btn {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 40px;
  display: flex;
  justify-content: center;
  align-items: center;
  background-color: #263445;
  color: #bfcbd9;
  cursor: pointer;
  transition: background-color 0.3s;
}

.sidebar-collapse-btn:hover {
  background-color: #1f2d3d;
  color: #fff;
}

.layout-content {
  flex: 1;
  overflow-y: auto;
  background-color: #f0f2f5;
}
</style> 