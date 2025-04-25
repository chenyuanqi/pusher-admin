import { defineStore } from 'pinia'
import axios from 'axios'

export const useUserStore = defineStore('user', {
  state: () => ({
    token: localStorage.getItem('token') || '',
    userInfo: JSON.parse(localStorage.getItem('userInfo') || '{}'),
    loading: false
  }),

  getters: {
    isLoggedIn: (state) => !!state.token,
    username: (state) => state.userInfo?.username || '',
    role: (state) => state.userInfo?.role || ''
  },

  actions: {
    async login(credentials: { username: string; password: string }) {
      this.loading = true
      try {
        const response = await axios.post('http://localhost:8000/api/v1/auth/login', credentials)

        if (response.data.code === 200) {
          const { token, user } = response.data.data

          this.token = token
          this.userInfo = user

          // 保存到本地存储
          localStorage.setItem('token', token)
          localStorage.setItem('userInfo', JSON.stringify(user))

          // 设置全局请求头
          axios.defaults.headers.common['Authorization'] = `Bearer ${token}`

          return user
        } else {
          throw new Error(response.data.message || '登录失败')
        }
      } catch (error: any) {
        console.error('登录错误:', error)
        throw new Error(error.response?.data?.message || '用户名或密码错误')
      } finally {
        this.loading = false
      }
    },

    async logout() {
      try {
        if (this.token) {
          await axios.post('http://localhost:8000/api/v1/auth/logout', {}, {
            headers: {
              Authorization: `Bearer ${this.token}`
            }
          })
        }
      } catch (error) {
        console.error('退出登录错误:', error)
      } finally {
        // 无论API请求成功与否，都清除本地状态
        this.clearUserState()
      }
    },

    async getUserInfo() {
      if (!this.token) return null

      try {
        const response = await axios.get('http://localhost:8000/api/v1/auth/me', {
          headers: {
            Authorization: `Bearer ${this.token}`
          }
        })

        if (response.data.code === 200) {
          this.userInfo = response.data.data
          localStorage.setItem('userInfo', JSON.stringify(response.data.data))
          return response.data.data
        }
        return null
      } catch (error) {
        console.error('获取用户信息错误:', error)
        // 如果是401错误，清除用户状态
        if (axios.isAxiosError(error) && error.response?.status === 401) {
          this.clearUserState()
        }
        return null
      }
    },

    clearUserState() {
      this.token = ''
      this.userInfo = {}

      // 清除本地存储
      localStorage.removeItem('token')
      localStorage.removeItem('userInfo')

      // 清除请求头
      delete axios.defaults.headers.common['Authorization']
    }
  }
})
