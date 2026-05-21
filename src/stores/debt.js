import { defineStore } from 'pinia'
import axios from '@/plugins/axios'

export const useDebtStore = defineStore('debt', {
  state: () => ({
    debts: [],
    loading: false,
    error: null,
  }),

  actions: {
    async fetchDebts(params = {}) {
      this.loading = true
      try {
        const response = await axios.get('/debts', { params })
        this.debts = response.data
      } catch (err) {
        this.error = 'Gagal mengambil data hutang'
        console.error(err)
      } finally {
        this.loading = false
      }
    },

    async payDebt(id, paymentData) {
      try {
        const response = await axios.post(`/debts/${id}/pay`, paymentData)
        // Refresh debts list
        await this.fetchDebts()
        return response.data
      } catch (err) {
        throw err.response?.data?.message || 'Gagal memproses pembayaran hutang'
      }
    }
  }
})
