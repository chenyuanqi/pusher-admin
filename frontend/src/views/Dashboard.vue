<template>
  <MainLayout>
    <div class="dashboard-container">
      <h2 class="page-title">首页 - 欢迎使用推客后台管理系统</h2>
      
      <el-card class="welcome-card">
        <template #header>
          <div class="card-header">
            <span>文案转换工具</span>
          </div>
        </template>
        <div class="card-content">
          <p>这是一个功能强大的文案转换工具，可以帮助您快速转换文案风格和内容。</p>
          <p>目前支持以下转换类型：</p>
          <ul>
            <li><strong>表情符号转换</strong>：将文本转换为包含表情符号的风格</li>
            <li><strong>正式文案转换</strong>：将普通文本转换为正式场合使用的文案</li>
            <li><strong>轻松文案转换</strong>：将文本转换为轻松活泼的风格</li>
            <li><strong>社交媒体优化</strong>：针对社交媒体平台优化文案</li>
          </ul>
          <div class="quick-actions">
            <el-button type="primary" @click="goToTransform">开始转换</el-button>
          </div>
        </div>
      </el-card>
    </div>
  </MainLayout>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'
import MainLayout from '../components/MainLayout.vue'

const router = useRouter()

// 跳转到文案转换页面
const goToTransform = () => {
  router.push('/transform')
}
</script>

<style scoped>
.dashboard-container {
  padding: 1.5rem;
}

.page-title {
  margin-bottom: 1.5rem;
  color: #303133;
}

.welcome-card {
  margin-bottom: 1.5rem;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 1.2rem;
  font-weight: bold;
}

.card-content {
  padding: 1rem 0;
}

.quick-actions {
  margin-top: 1.5rem;
  text-align: center;
}
</style>
