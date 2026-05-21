import { defineStore } from 'pinia'
import { AuthService } from '@/services/AuthService'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('userData')) || null,
    token: localStorage.getItem('authToken') || null,
    role: localStorage.getItem('userRole') || null, // 'owner', 'admin', 'kasir'
    isAuthenticated: !!localStorage.getItem('authToken')
  }),
  actions: {
    async login(credentials) {
      try {
        const response = await AuthService.login(credentials)
        const { access_token, user } = response.data
        
        this.token = access_token
        this.user = user
        this.role = user.role
        this.isAuthenticated = true

        localStorage.setItem('authToken', access_token)
        localStorage.setItem('userData', JSON.stringify(user))
        localStorage.setItem('userRole', user.role)

        return response.data
      } catch (error) {
        this.logout()
        throw error
      }
    },
    async logout() {
      try {
        if (this.isAuthenticated) {
          await AuthService.logout()
        }
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        this.clearAuth()
      }
    },
    setRole(role) {
      this.role = role
      localStorage.setItem('userRole', role)
    },
    clearAuth() {
      this.user = null
      this.token = null
      this.role = null
      this.isAuthenticated = false
      localStorage.removeItem('authToken')
      localStorage.removeItem('userData')
      localStorage.removeItem('userRole')
    }
  },
  getters: {
    isOwner: (state) => state.role === 'owner',
    isAdminStok: (state) => state.role === 'admin',
    isKasir: (state) => state.role === 'kasir',
    roleDisplayName: (state) => {
      const names = {
        'owner': 'Admin Utama',
        'admin': 'Admin Gudang',
        'kasir': 'Kasir'
      }
      return names[state.role] || state.role
    },
    allowedRoutes: (state) => {
      if (state.role === 'owner') {
        return ['Dashboard', 'Kasir', 'Inventory', 'Pelanggan', 'Laporan', 'Pengaturan', 'Backup']
      }
      if (state.role === 'admin') {
        return ['Inventory']
      }
      if (state.role === 'kasir') {
        return ['Kasir', 'Pelanggan']
      }
      return []
    }
  }
})
