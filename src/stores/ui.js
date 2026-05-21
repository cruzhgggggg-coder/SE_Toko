import { defineStore } from 'pinia'

export const useUIStore = defineStore('ui', {
  state: () => ({
    searchQuery: '',
    isSidebarCollapsed: false,
    showNotification: false,
    shopName: 'TOKO SUMBER MAKMUR'
  }),
  actions: {
    setSearchQuery(query) {
      this.searchQuery = query
    },
    setShopName(name) {
      this.shopName = name
    }
  }
})
