<template>
  <header class="app-header">
    <div class="header-logo">
      <img src="@/assets/logo.png" alt="推客后台" class="logo">
      <h1 class="app-title">推客后台管理系统</h1>
    </div>

    <div class="user-area">
      <el-dropdown @command="handleCommand">
        <span class="user-info">
          <el-avatar :size="36" icon="el-icon-user" />
          <span class="username">{{ userStore.username }}</span>
          <el-icon><ArrowDown /></el-icon>
        </span>
        <template #dropdown>
          <el-dropdown-menu>
            <el-dropdown-item command="userInfo">个人信息</el-dropdown-item>
            <el-dropdown-item command="logout" divided>退出登录</el-dropdown-item>
          </el-dropdown-menu>
        </template>
      </el-dropdown>
    </div>
  </header>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useUserStore } from '@/stores/user'
import { ElMessageBox } from 'element-plus'
import { ArrowDown } from '@element-plus/icons-vue'

const router = useRouter()
const userStore = useUserStore()

const handleCommand = (command: string) => {
  if (command === 'logout') {
    ElMessageBox.confirm('确定要退出登录吗？', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    }).then(async () => {
      await userStore.logout()
      router.push('/login')
    }).catch(() => {})
  } else if (command === 'userInfo') {
    // 这里可以添加显示用户信息的逻辑
  }
}
</script>

<style scoped>
.app-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 1.5rem;
  height: 60px;
  background-color: #fff;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
}

.header-logo {
  display: flex;
  align-items: center;
}

.logo {
  height: 36px;
  margin-right: 1rem;
}

.app-title {
  font-size: 1.2rem;
  font-weight: 600;
  color: #1e80ff;
  margin: 0;
}

.user-area {
  display: flex;
  align-items: center;
}

.user-info {
  display: flex;
  align-items: center;
  cursor: pointer;
}

.username {
  margin: 0 0.5rem;
  font-size: 0.9rem;
  color: #606266;
}
</style>
