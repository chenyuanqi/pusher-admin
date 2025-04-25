# 前端文案转换页面实现示例

## 文案转换页面 Vue 组件 (TransformPanel.vue)

```vue
<template>
  <div class="transform-container">
    <h2 class="page-title">文案转换工具</h2>
    
    <!-- 操作区域 -->
    <div class="transform-panel">
      <!-- 左侧：原始文案输入 -->
      <div class="panel-section">
        <div class="section-header">
          <h3>原始文案</h3>
          <el-button 
            type="text" 
            size="small" 
            @click="clearOriginalContent"
            :disabled="!form.content"
          >
            <el-icon><Delete /></el-icon> 清空
          </el-button>
        </div>
        <el-input
          type="textarea"
          v-model="form.content"
          :rows="10"
          placeholder="请输入需要转换的文案内容..."
          resize="none"
        />
        <div class="word-count">
          字数：{{ form.content.length }}
        </div>
      </div>
      
      <!-- 中间：操作按钮区 -->
      <div class="panel-controls">
        <div class="control-item">
          <span class="control-label">转换类型：</span>
          <el-select 
            v-model="form.type" 
            placeholder="请选择"
            style="width: 100%;"
          >
            <el-option
              v-for="item in transformTypes"
              :key="item.id"
              :label="item.name"
              :value="item.id"
            >
              <div>
                <span>{{ item.name }}</span>
                <div class="option-description">{{ item.description }}</div>
              </div>
            </el-option>
          </el-select>
        </div>
        
        <!-- 根据当前类型显示附加选项 -->
        <div v-if="currentTypeOptions.length > 0" class="control-item">
          <template v-for="option in currentTypeOptions" :key="option.name">
            <span class="control-label">{{ option.label }}：</span>
            <el-select 
              v-if="option.type === 'select'"
              v-model="form.options[option.name]" 
              placeholder="请选择"
              style="width: 100%;"
            >
              <el-option
                v-for="value in option.values"
                :key="value.value"
                :label="value.label"
                :value="value.value"
              />
            </el-select>
          </template>
        </div>
        
        <div class="control-buttons">
          <el-button 
            type="primary" 
            :icon="Refresh"
            :loading="loading"
            :disabled="!form.content || !form.type"
            @click="handleTransform"
          >
            转换
          </el-button>
          
          <el-button 
            type="success" 
            :icon="CopyDocument"
            :disabled="!transformedContent"
            @click="copyToClipboard"
          >
            复制结果
          </el-button>
        </div>
      </div>
      
      <!-- 右侧：转换结果展示 -->
      <div class="panel-section">
        <div class="section-header">
          <h3>转换结果</h3>
          <el-button 
            type="text" 
            size="small" 
            @click="copyToClipboard"
            :disabled="!transformedContent"
          >
            <el-icon><CopyDocument /></el-icon> 复制
          </el-button>
        </div>
        <el-input
          type="textarea"
          v-model="transformedContent"
          :rows="10"
          readonly
          placeholder="转换结果将显示在这里..."
          resize="none"
        />
        <div class="word-count">
          字数：{{ transformedContent ? transformedContent.length : 0 }}
        </div>
      </div>
    </div>
    
    <!-- 历史记录区域 -->
    <div class="history-section">
      <div class="section-header">
        <h3>转换历史</h3>
        <div>
          <el-button 
            type="danger" 
            size="small" 
            :disabled="selectedHistory.length === 0"
            @click="handleBatchDelete"
          >
            批量删除
          </el-button>
          <el-button 
            type="primary" 
            size="small" 
            @click="fetchHistoryList"
          >
            <el-icon><Refresh /></el-icon> 刷新
          </el-button>
        </div>
      </div>
      
      <el-table
        v-loading="historyLoading"
        :data="historyList"
        style="width: 100%"
        @selection-change="handleSelectionChange"
      >
        <el-table-column type="selection" width="55" />
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="original_content" label="原始内容" show-overflow-tooltip>
          <template #default="scope">
            {{ truncateText(scope.row.original_content, 50) }}
          </template>
        </el-table-column>
        <el-table-column prop="type" label="转换类型" width="120">
          <template #default="scope">
            {{ getTypeNameById(scope.row.type) }}
          </template>
        </el-table-column>
        <el-table-column prop="created_at" label="转换时间" width="180" />
        <el-table-column label="操作" width="150">
          <template #default="scope">
            <el-button 
              type="text" 
              size="small" 
              @click="viewHistoryDetail(scope.row)"
            >
              查看
            </el-button>
            <el-button 
              type="text" 
              size="small" 
              @click="loadFromHistory(scope.row)"
            >
              使用
            </el-button>
            <el-button 
              type="text" 
              size="small" 
              @click="deleteHistory(scope.row.id)"
            >
              删除
            </el-button>
          </template>
        </el-table-column>
      </el-table>
      
      <!-- 分页控件 -->
      <el-pagination
        class="pagination"
        v-model:current-page="queryParams.page"
        v-model:page-size="queryParams.limit"
        :page-sizes="[10, 20, 50, 100]"
        layout="total, sizes, prev, pager, next, jumper"
        :total="total"
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange"
      />
    </div>
    
    <!-- 历史详情对话框 -->
    <el-dialog
      v-model="historyDetailVisible"
      title="转换历史详情"
      width="60%"
    >
      <div v-if="currentHistoryDetail" class="history-detail">
        <div class="detail-item">
          <span class="detail-label">ID:</span>
          <span>{{ currentHistoryDetail.id }}</span>
        </div>
        <div class="detail-item">
          <span class="detail-label">转换类型:</span>
          <span>{{ getTypeNameById(currentHistoryDetail.type) }}</span>
        </div>
        <div class="detail-item">
          <span class="detail-label">转换时间:</span>
          <span>{{ currentHistoryDetail.created_at }}</span>
        </div>
        
        <el-divider />
        
        <div class="detail-content">
          <h4>原始内容:</h4>
          <el-input
            type="textarea"
            v-model="currentHistoryDetail.original_content"
            :rows="5"
            readonly
          />
        </div>
        
        <div class="detail-content">
          <h4>转换结果:</h4>
          <el-input
            type="textarea"
            v-model="currentHistoryDetail.transformed_content"
            :rows="5"
            readonly
          />
        </div>
      </div>
      <template #footer>
        <el-button @click="historyDetailVisible = false">关闭</el-button>
        <el-button 
          type="primary" 
          @click="copyHistoryResult"
        >
          复制结果
        </el-button>
        <el-button 
          type="success" 
          @click="loadFromHistoryDetail"
        >
          使用此内容
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Delete, Refresh, CopyDocument } from '@element-plus/icons-vue'
import { 
  getTransformTypes,
  transformContent,
  getTransformHistory,
  getTransformHistoryDetail,
  deleteTransformHistory,
  batchDeleteTransformHistory
} from '@/api/transform'
import { debounce } from 'lodash-es'

// 响应式状态
const form = reactive({
  content: '',
  type: '',
  options: {}
})

const transformedContent = ref('')
const loading = ref(false)
const transformTypes = ref([])

// 历史记录相关
const historyList = ref([])
const historyLoading = ref(false)
const queryParams = reactive({
  page: 1,
  limit: 10,
  type: '',
  start_date: '',
  end_date: ''
})
const total = ref(0)
const selectedHistory = ref([])

// 历史详情相关
const historyDetailVisible = ref(false)
const currentHistoryDetail = ref(null)

// 计算属性：当前选择类型的附加选项
const currentTypeOptions = computed(() => {
  if (!form.type) return []
  
  const currentType = transformTypes.value.find(t => t.id === form.type)
  if (!currentType) return []
  
  return currentType.options || []
})

// 初始化默认选项值
const initDefaultOptions = (options) => {
  options.forEach(option => {
    if (option.default !== undefined) {
      form.options[option.name] = option.default
    }
  })
}

// 初始化函数
const init = async () => {
  await fetchTransformTypes()
  await fetchHistoryList()
}

// 获取转换类型列表
const fetchTransformTypes = async () => {
  try {
    const res = await getTransformTypes()
    transformTypes.value = res.data
    
    // 如果有类型数据，默认选择第一个
    if (transformTypes.value.length > 0) {
      form.type = transformTypes.value[0].id
      
      // 初始化默认选项
      if (transformTypes.value[0].options) {
        initDefaultOptions(transformTypes.value[0].options)
      }
    }
  } catch (error) {
    console.error('获取转换类型失败:', error)
    ElMessage.error('获取转换类型失败')
  }
}

// 转换处理
const handleTransform = debounce(async () => {
  if (!form.content || !form.type) {
    ElMessage.warning('请输入文案内容并选择转换类型')
    return
  }
  
  loading.value = true
  transformedContent.value = ''
  
  try {
    const res = await transformContent({
      content: form.content,
      type: form.type,
      options: form.options
    })
    
    transformedContent.value = res.data.transformed_content
    
    // 自动刷新历史记录
    await fetchHistoryList()
    
    ElMessage.success('转换成功')
  } catch (error) {
    console.error('转换失败:', error)
    ElMessage.error('转换失败: ' + (error.message || '未知错误'))
  } finally {
    loading.value = false
  }
}, 300)

// 清空原始内容
const clearOriginalContent = () => {
  form.content = ''
  transformedContent.value = ''
}

// 复制结果到剪贴板
const copyToClipboard = () => {
  if (!transformedContent.value) {
    ElMessage.warning('没有可复制的内容')
    return
  }
  
  navigator.clipboard.writeText(transformedContent.value)
    .then(() => {
      ElMessage.success('已复制到剪贴板')
    })
    .catch(() => {
      ElMessage.error('复制失败，请手动复制')
    })
}

// 复制历史详情结果
const copyHistoryResult = () => {
  if (!currentHistoryDetail.value?.transformed_content) {
    ElMessage.warning('没有可复制的内容')
    return
  }
  
  navigator.clipboard.writeText(currentHistoryDetail.value.transformed_content)
    .then(() => {
      ElMessage.success('已复制到剪贴板')
    })
    .catch(() => {
      ElMessage.error('复制失败，请手动复制')
    })
}

// 获取历史记录列表
const fetchHistoryList = async () => {
  historyLoading.value = true
  
  try {
    const res = await getTransformHistory(queryParams)
    historyList.value = res.data.items
    total.value = res.data.total
  } catch (error) {
    console.error('获取历史记录失败:', error)
    ElMessage.error('获取历史记录失败')
  } finally {
    historyLoading.value = false
  }
}

// 查看历史详情
const viewHistoryDetail = async (row) => {
  try {
    const res = await getTransformHistoryDetail(row.id)
    currentHistoryDetail.value = res.data
    historyDetailVisible.value = true
  } catch (error) {
    console.error('获取历史详情失败:', error)
    ElMessage.error('获取历史详情失败')
  }
}

// 从历史记录加载内容
const loadFromHistory = (row) => {
  form.content = row.original_content
  form.type = row.type
  
  // 刷新选项
  form.options = {}
  const currentType = transformTypes.value.find(t => t.id === row.type)
  if (currentType && currentType.options) {
    initDefaultOptions(currentType.options)
  }
  
  transformedContent.value = row.transformed_content
  
  ElMessage.success('已加载历史内容')
}

// 从历史详情加载内容
const loadFromHistoryDetail = () => {
  if (!currentHistoryDetail.value) return
  
  form.content = currentHistoryDetail.value.original_content
  form.type = currentHistoryDetail.value.type
  
  // 刷新选项
  form.options = { ...currentHistoryDetail.value.options }
  
  transformedContent.value = currentHistoryDetail.value.transformed_content
  
  historyDetailVisible.value = false
  ElMessage.success('已加载历史内容')
}

// 删除单条历史记录
const deleteHistory = async (id) => {
  try {
    await ElMessageBox.confirm(
      '确定要删除这条历史记录吗？删除后无法恢复。',
      '提示',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning',
      }
    )
    
    await deleteTransformHistory(id)
    ElMessage.success('删除成功')
    await fetchHistoryList()
  } catch (error) {
    if (error !== 'cancel') {
      console.error('删除失败:', error)
      ElMessage.error('删除失败')
    }
  }
}

// 批量删除历史记录
const handleBatchDelete = async () => {
  if (selectedHistory.value.length === 0) {
    ElMessage.warning('请先选择要删除的记录')
    return
  }
  
  try {
    await ElMessageBox.confirm(
      `确定要删除选中的 ${selectedHistory.value.length} 条记录吗？删除后无法恢复。`,
      '提示',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning',
      }
    )
    
    const ids = selectedHistory.value.map(item => item.id)
    await batchDeleteTransformHistory(ids)
    ElMessage.success('批量删除成功')
    await fetchHistoryList()
  } catch (error) {
    if (error !== 'cancel') {
      console.error('批量删除失败:', error)
      ElMessage.error('批量删除失败')
    }
  }
}

// 表格选择变化
const handleSelectionChange = (selection) => {
  selectedHistory.value = selection
}

// 分页大小变化
const handleSizeChange = (size) => {
  queryParams.limit = size
  fetchHistoryList()
}

// 页码变化
const handleCurrentChange = (page) => {
  queryParams.page = page
  fetchHistoryList()
}

// 根据类型ID获取类型名称
const getTypeNameById = (typeId) => {
  const type = transformTypes.value.find(t => t.id === typeId)
  return type ? type.name : typeId
}

// 文本截断函数
const truncateText = (text, length) => {
  if (!text) return ''
  if (text.length <= length) return text
  return text.substring(0, length) + '...'
}

// 组件挂载时初始化
onMounted(() => {
  init()
})
</script>

<style scoped>
.transform-container {
  padding: 20px;
}

.page-title {
  margin-bottom: 20px;
  font-weight: 600;
  color: #303133;
}

.transform-panel {
  display: flex;
  gap: 20px;
  margin-bottom: 30px;
}

.panel-section {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.panel-controls {
  width: 200px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 20px;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.section-header h3 {
  margin: 0;
  font-size: 16px;
  color: #606266;
}

.word-count {
  margin-top: 5px;
  text-align: right;
  font-size: 12px;
  color: #909399;
}

.control-item {
  margin-bottom: 15px;
}

.control-label {
  display: block;
  margin-bottom: 5px;
  color: #606266;
}

.control-buttons {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.option-description {
  font-size: 12px;
  color: #909399;
  margin-top: 4px;
}

.history-section {
  margin-top: 30px;
}

.pagination {
  margin-top: 20px;
  display: flex;
  justify-content: center;
}

.history-detail {
  padding: 0 20px;
}

.detail-item {
  margin-bottom: 10px;
  display: flex;
}

.detail-label {
  width: 100px;
  color: #606266;
  font-weight: bold;
}

.detail-content {
  margin-top: 20px;
}

.detail-content h4 {
  margin-bottom: 10px;
  color: #606266;
}

/* 响应式设计 */
@media (max-width: 1200px) {
  .transform-panel {
    flex-direction: column;
  }
  
  .panel-controls {
    width: 100%;
    flex-direction: row;
    align-items: center;
    gap: 15px;
  }
  
  .control-item {
    flex: 1;
  }
  
  .control-buttons {
    width: 200px;
    flex-direction: column;
  }
}

@media (max-width: 768px) {
  .panel-controls {
    flex-direction: column;
  }
  
  .control-buttons {
    width: 100%;
    flex-direction: row;
  }
}
</style>
```

## 文案转换 API 接口封装

```js
// src/api/transform.js
import request from '@/utils/request'

/**
 * 获取转换类型列表
 * @returns {Promise}
 */
export function getTransformTypes() {
  return request({
    url: '/transform/types',
    method: 'get'
  })
}

/**
 * 执行文案转换
 * @param {Object} data - 包含 content, type 和 options 的对象
 * @returns {Promise}
 */
export function transformContent(data) {
  return request({
    url: '/transform',
    method: 'post',
    data
  })
}

/**
 * 获取转换历史记录
 * @param {Object} params - 查询参数
 * @returns {Promise}
 */
export function getTransformHistory(params) {
  return request({
    url: '/transform/history',
    method: 'get',
    params
  })
}

/**
 * 获取转换历史详情
 * @param {Number} id - 历史记录ID
 * @returns {Promise}
 */
export function getTransformHistoryDetail(id) {
  return request({
    url: `/transform/history/${id}`,
    method: 'get'
  })
}

/**
 * 删除转换历史记录
 * @param {Number} id - 历史记录ID
 * @returns {Promise}
 */
export function deleteTransformHistory(id) {
  return request({
    url: `/transform/history/${id}`,
    method: 'delete'
  })
}

/**
 * 批量删除转换历史记录
 * @param {Array} ids - 历史记录ID数组
 * @returns {Promise}
 */
export function batchDeleteTransformHistory(ids) {
  return request({
    url: '/transform/history/batch',
    method: 'delete',
    data: { ids }
  })
}
```

## 路由配置

```js
// src/router/index.js 的相关配置
const routes = [
  {
    path: '/',
    component: Layout,
    redirect: '/dashboard',
    children: [
      {
        path: 'dashboard',
        name: 'Dashboard',
        component: () => import('@/views/dashboard/index.vue'),
        meta: { title: '首页', icon: 'el-icon-s-home' }
      }
    ]
  },
  {
    path: '/transform',
    component: Layout,
    children: [
      {
        path: '',
        name: 'Transform',
        component: () => import('@/views/transform/index.vue'),
        meta: { title: '文案转换', icon: 'el-icon-s-tools' }
      }
    ]
  },
  // 其他路由...
]
``` 