<template>
  <MainLayout>
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
              :loading="loading"
              :disabled="!form.content || !form.type"
              @click="handleTransform"
            >
              转换
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
          <!-- 修改此处，移除 readonly 属性 -->
          <el-input
            type="textarea"
            v-model="transformedContent"
            :rows="10"
            placeholder="转换结果将显示在这里..."
            resize="none"
          />
          <div class="word-count">
            字数：{{ transformedContent ? transformedContent.length : 0 }}
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { CopyDocument, Delete } from '@element-plus/icons-vue'
import MainLayout from '../components/MainLayout.vue'

// 响应式状态
const form = reactive({
  content: '',
  type: 'link', // 默认选择链接转换
  options: {} as Record<string, any>
})

const transformedContent = ref('')
const loading = ref(false)

// 定义转换类型 - 静态数据，实际项目中应该从API获取
const transformTypes = [
  {
    id: 'link',
    name: '链接转换',
    description: '提取原始文案中的链接，转换为特定格式',
    options: []
  },
  {
    id: 'code',
    name: '口令转换',
    description: '提取或生成淘宝口令格式',
    options: [
      {
        name: 'mode',
        label: '模式',
        type: 'select',
        values: [
          { value: 'extract', label: '提取口令' },
          { value: 'generate', label: '生成口令' }
        ],
        default: 'extract'
      }
    ]
  }
]

// 计算当前转换类型的选项
const currentTypeOptions = computed(() => {
  if (!form.type) return []

  const currentType = transformTypes.find(type => type.id === form.type)
  if (!currentType) return []

  // 初始化选项默认值
  currentType.options.forEach(option => {
    if (option.default !== undefined && form.options[option.name] === undefined) {
      form.options[option.name] = option.default
    }
  })

  return currentType.options
})

// 清空原始内容
const clearOriginalContent = () => {
  form.content = ''
}

// 从文本中提取链接
const extractLinkFromText = (text: string): string => {
  // 匹配http/https链接
  const urlRegex = /(https?:\/\/[^\s]+)/g
  const matches = text.match(urlRegex)

  if (matches && matches.length > 0) {
    return matches[0]
  }
  return ''
}

// 提取淘宝口令
const extractTaobaoCode = (text: string): string => {
  // 匹配淘宝口令格式（数字+￥+字母数字+空格+字母数字+￥）
  const codeRegex = /(\d+￥\s*[A-Za-z0-9]+\s+[A-Za-z0-9]+\s*￥)/
  const matches = text.match(codeRegex)

  if (matches && matches.length > 0) {
    return matches[0].trim()
  }
  return ''
}

// 格式化淘宝口令
const formatTaobaoCode = (code: string): string => {
  if (!code) return ''
  return `如商品链接访问有问题，复制 ${code} 到TB搜索框打开`
}

// 生成随机口令
const generateCode = (): string => {
  const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'
  let result = ''
  for (let i = 0; i < 8; i++) {
    result += chars.charAt(Math.floor(Math.random() * chars.length))
  }
  return result
}

// 模拟API调用的转换函数
const transformContentApi = async (data: { content: string; type: string; options?: Record<string, any> }) => {
  return new Promise<{
    original_content: string;
    transformed_content: string;
    type: string;
    transform_id: string;
  }>((resolve, reject) => {
    setTimeout(() => {
      try {
        let transformedContent = data.content

        if (data.type === 'link') {
          // 链接转换
          const link = extractLinkFromText(data.content)
          if (link) {
            transformedContent = `戳👉>>>${ link }`
          } else {
            throw new Error('未在文本中找到链接')
          }
        } else if (data.type === 'code') {
          const mode = data.options?.mode || 'extract'

          if (mode === 'extract') {
            // 提取淘宝口令
            const code = extractTaobaoCode(data.content)
            if (code) {
              transformedContent = formatTaobaoCode(code)
            } else {
              throw new Error('未在文本中找到淘宝口令')
            }
          } else {
            // 生成随机口令
            const code = generateCode()
            transformedContent = `如商品链接访问有问题，复制 ${ code } 到TB搜索框打开`
          }
        }

        resolve({
          original_content: data.content,
          transformed_content: transformedContent,
          type: data.type,
          transform_id: Date.now().toString()
        })
      } catch (error) {
        reject(error)
      }
    }, 1000) // 模拟1秒延迟
  })
}

// 处理转换
const handleTransform = async () => {
  if (!form.content || !form.type) return

  loading.value = true

  try {
    const result = await transformContentApi({
      content: form.content,
      type: form.type,
      options: form.options
    })

    transformedContent.value = result.transformed_content
    ElMessage.success('转换成功')

    // 自动复制结果到剪贴板
    copyToClipboard()
  } catch (error: any) {
    ElMessage.error(error.message || '转换失败')
  } finally {
    loading.value = false
  }
}

// 复制到剪贴板
const copyToClipboard = () => {
  if (!transformedContent.value) return

  navigator.clipboard.writeText(transformedContent.value)
    .then(() => {
      ElMessage.success('已复制到剪贴板')
    })
    .catch(() => {
      ElMessage.error('复制失败，请手动复制')
    })
}

// 组件挂载时执行初始化
onMounted(() => {
  // 在实际项目中，这里会调用API获取转换类型
})
</script>

<style scoped>
.transform-container {
  padding: 1.5rem;
}

.page-title {
  margin-bottom: 1.5rem;
  color: #303133;
}

.transform-panel {
  display: flex;
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.panel-section {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.section-header h3 {
  margin: 0;
  font-size: 1.2rem;
  color: #303133;
}

.word-count {
  margin-top: 0.5rem;
  font-size: 0.85rem;
  color: #909399;
  text-align: right;
}

.panel-controls {
  width: 200px;
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  gap: 1rem;
}

.control-item {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.control-label {
  font-size: 0.9rem;
  color: #606266;
}

.option-description {
  font-size: 0.8rem;
  color: #909399;
  margin-top: 2px;
}

.control-buttons {
  margin-top: auto;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

/* 响应式设计 */
@media (max-width: 1024px) {
  .transform-panel {
    flex-direction: column;
  }

  .panel-controls {
    width: 100%;
    flex-direction: row;
    flex-wrap: wrap;
  }

  .control-item {
    width: 45%;
  }

  .control-buttons {
    width: 100%;
    flex-direction: row;
    justify-content: flex-end;
  }
}

@media (max-width: 768px) {
  .control-item {
    width: 100%;
  }
}
</style>
