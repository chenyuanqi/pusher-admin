<template>
  <MainLayout>
    <div class="settings-container">
      <h2 class="page-title">系统设置</h2>
      
      <el-card class="settings-card">
        <template #header>
          <div class="card-header">
            <span>基本设置</span>
          </div>
        </template>
        
        <el-form label-width="120px" class="settings-form">
          <el-form-item label="系统名称">
            <el-input v-model="settings.systemName" placeholder="输入系统名称"></el-input>
          </el-form-item>
          
          <el-form-item label="系统Logo">
            <el-upload
              class="avatar-uploader"
              action="#"
              :auto-upload="false"
              :show-file-list="false"
            >
              <img v-if="settings.logoUrl" :src="settings.logoUrl" class="avatar" />
              <el-icon v-else class="avatar-uploader-icon"><Plus /></el-icon>
            </el-upload>
          </el-form-item>
          
          <el-form-item label="主题色">
            <el-color-picker v-model="settings.themeColor" />
          </el-form-item>
          
          <el-form-item>
            <el-button type="primary" @click="saveSettings">保存设置</el-button>
            <el-button @click="resetSettings">重置</el-button>
          </el-form-item>
        </el-form>
      </el-card>
    </div>
  </MainLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { ElMessage } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'
import MainLayout from '../components/MainLayout.vue'

// 系统设置
const settings = reactive({
  systemName: '推客后台管理系统',
  logoUrl: '../assets/logo.svg',
  themeColor: '#1e80ff'
})

// 保存设置
const saveSettings = () => {
  ElMessage.success('设置保存成功')
}

// 重置设置
const resetSettings = () => {
  settings.systemName = '推客后台管理系统'
  settings.logoUrl = '../assets/logo.svg'
  settings.themeColor = '#1e80ff'
  ElMessage.info('设置已重置')
}
</script>

<style scoped>
.settings-container {
  padding: 1.5rem;
}

.page-title {
  margin-bottom: 1.5rem;
  color: #303133;
}

.settings-card {
  margin-bottom: 1.5rem;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 1.2rem;
  font-weight: bold;
}

.settings-form {
  max-width: 600px;
  margin: 0 auto;
}

.avatar-uploader {
  border: 1px dashed #d9d9d9;
  border-radius: 6px;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  width: 120px;
  height: 120px;
  display: flex;
  justify-content: center;
  align-items: center;
}

.avatar-uploader:hover {
  border-color: #409EFF;
}

.avatar-uploader-icon {
  font-size: 28px;
  color: #8c939d;
  width: 120px;
  height: 120px;
  display: flex;
  justify-content: center;
  align-items: center;
}

.avatar {
  width: 120px;
  height: 120px;
  display: block;
}
</style> 