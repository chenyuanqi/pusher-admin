<template>
  <div class="sidebar">
    <el-menu
      :default-active="activeMenu"
      class="sidebar-menu"
      :collapse="isCollapse"
      :router="true"
      background-color="#304156"
      text-color="#bfcbd9"
      active-text-color="#409EFF"
    >
      <el-menu-item index="/dashboard">
        <el-icon><Monitor /></el-icon>
        <template #title>首页</template>
      </el-menu-item>

      <el-menu-item index="/transform">
        <el-icon><Edit /></el-icon>
        <template #title>文案转换</template>
      </el-menu-item>

      <el-menu-item index="/settings">
        <el-icon><Setting /></el-icon>
        <template #title>系统设置</template>
      </el-menu-item>
    </el-menu>

    <div class="collapse-btn" @click="toggleCollapse">
      <el-icon v-if="isCollapse"><Expand /></el-icon>
      <el-icon v-else><Fold /></el-icon>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import { Monitor, Edit, Setting, Expand, Fold } from '@element-plus/icons-vue'

const route = useRoute()
const isCollapse = ref(false)

const activeMenu = computed(() => {
  return route.path
})

const toggleCollapse = () => {
  isCollapse.value = !isCollapse.value
}
</script>

<style scoped>
.sidebar {
  height: calc(100vh - 60px);
  background-color: #304156;
  position: relative;
  transition: width 0.3s;
}

.sidebar-menu {
  height: calc(100% - 50px);
  border-right: none;
}

.sidebar-menu:not(.el-menu--collapse) {
  width: 200px;
}

.collapse-btn {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 50px;
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  color: #bfcbd9;
  background-color: #263445;
  transition: background-color 0.3s;
}

.collapse-btn:hover {
  background-color: #1f2d3d;
  color: #fff;
}
</style>
