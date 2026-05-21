import { defineStore } from 'pinia'

export const useUIStore = defineStore('ui', {
  state: () => ({
    searchQuery: '',
    isSidebarCollapsed: false,
    showNotification: false
  }),
  actions: {
    setSearchQuery(query) {
      this.searchQuery = query
    }
  }
})
