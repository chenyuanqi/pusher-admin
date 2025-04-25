<template>
  <div class="not-found">
    <div class="content">
      <h1 class="error-code">404</h1>
      <h2 class="error-message">页面不存在</h2>
      <p class="error-desc">抱歉，您访问的页面不存在或已被移除</p>
      <el-button type="primary" @click="goHome">返回首页</el-button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'

const router = useRouter()

const goHome = () => {
  router.push('/')
}
</script>

<style scoped>
.not-found {
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background-color: #f5f7fa;
}

.content {
  text-align: center;
  padding: 2rem;
}

.error-code {
  font-size: 8rem;
  font-weight: bold;
  color: #409EFF;
  margin: 0;
  line-height: 1;
}

.error-message {
  font-size: 2rem;
  color: #303133;
  margin: 1rem 0;
}

.error-desc {
  font-size: 1.2rem;
  color: #909399;
  margin-bottom: 2rem;
}
</style>
