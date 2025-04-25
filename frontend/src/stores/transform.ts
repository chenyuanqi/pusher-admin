import { defineStore } from 'pinia'
import axios from 'axios'

export interface TransformType {
  id: string
  name: string
  description: string
  options: TransformOption[]
}

export interface TransformOption {
  name: string
  label: string
  type: string
  values?: { value: any; label: string }[]
  default?: any
}

export interface TransformResult {
  original_content: string
  transformed_content: string
  type: string
  transform_id: string
}

export interface TransformHistoryItem {
  id: number
  original_content: string
  transformed_content: string
  type: string
  options?: Record<string, any>
  created_at: string
  user?: { id: number; username: string }
}

export interface QueryParams {
  page: number
  limit: number
  type?: string
  start_date?: string
  end_date?: string
}

export const useTransformStore = defineStore('transform', {
  state: () => ({
    transformTypes: [] as TransformType[],
    lastResult: null as TransformResult | null,
    historyList: [] as TransformHistoryItem[],
    loading: false,
    historyLoading: false,
    total: 0,
    queryParams: {
      page: 1,
      limit: 10
    } as QueryParams
  }),

  getters: {
    getTypeById: (state) => (id: string) => {
      return state.transformTypes.find(type => type.id === id) || null
    },

    getTypeNameById: (state) => (id: string) => {
      const type = state.transformTypes.find(type => type.id === id)
      return type ? type.name : id
    }
  },

  actions: {
    async fetchTransformTypes() {
      try {
        const response = await axios.get('http://localhost:8000/api/v1/transform/types', {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        })

        if (response.data.code === 200) {
          this.transformTypes = response.data.data
        }
      } catch (error) {
        console.error('获取转换类型错误:', error)
      }
    },

    async transformContent(data: { content: string; type: string; options?: Record<string, any> }) {
      this.loading = true

      try {
        const response = await axios.post('http://localhost:8000/api/v1/transform', data, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        })

        if (response.data.code === 200) {
          this.lastResult = response.data.data
          return response.data.data
        } else {
          throw new Error(response.data.message || '转换失败')
        }
      } catch (error: any) {
        console.error('转换内容错误:', error)
        throw new Error(error.response?.data?.message || '转换请求失败')
      } finally {
        this.loading = false
      }
    },

    async fetchHistoryList(params?: Partial<QueryParams>) {
      this.historyLoading = true

      // 合并查询参数
      if (params) {
        this.queryParams = { ...this.queryParams, ...params }
      }

      try {
        const response = await axios.get('http://localhost:8000/api/v1/transform/history', {
          params: this.queryParams,
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        })

        if (response.data.code === 200) {
          this.historyList = response.data.data.items
          this.total = response.data.data.total
        }
      } catch (error) {
        console.error('获取历史记录错误:', error)
      } finally {
        this.historyLoading = false
      }
    },

    async getHistoryDetail(id: number) {
      try {
        const response = await axios.get(`http://localhost:8000/api/v1/transform/history/${id}`, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        })

        if (response.data.code === 200) {
          return response.data.data
        }
        return null
      } catch (error) {
        console.error('获取历史详情错误:', error)
        return null
      }
    },

    async deleteHistory(id: number) {
      try {
        const response = await axios.delete(`http://localhost:8000/api/v1/transform/history/${id}`, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        })

        if (response.data.code === 200) {
          // 删除成功后刷新列表
          await this.fetchHistoryList()
          return true
        }
        return false
      } catch (error) {
        console.error('删除历史记录错误:', error)
        return false
      }
    },

    async batchDeleteHistory(ids: number[]) {
      try {
        const response = await axios.delete('http://localhost:8000/api/v1/transform/history/batch', {
          data: { ids },
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        })

        if (response.data.code === 200) {
          // 删除成功后刷新列表
          await this.fetchHistoryList()
          return true
        }
        return false
      } catch (error) {
        console.error('批量删除历史记录错误:', error)
        return false
      }
    }
  }
})
