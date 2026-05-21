import { defineStore } from 'pinia'
import { ReturnService } from '@/services/ReturnService'

export const useReturnStore = defineStore('return', {
  state: () => ({
    returns: [],
    loading: false,
    error: null,
  }),

  actions: {
    async fetchReturns() {
      this.loading = true
      try {
        const response = await ReturnService.getAll()
        this.returns = response.data
      } catch (err) {
        this.error = 'Gagal mengambil data retur'
        console.error(err)
      } finally {
        this.loading = false
      }
    },

    async addReturn(returnData) {
      try {
        const response = await ReturnService.create(returnData)
        this.returns.push(response.data)
        return response.data
      } catch (err) {
        throw err.response?.data?.message || 'Gagal melakukan retur barang'
      }
    }
  }
})
