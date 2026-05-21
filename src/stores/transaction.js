import { defineStore } from 'pinia'
import { TransactionService } from '@/services/TransactionService'
import { saveOfflineTransaction, getOfflineTransactions, clearOfflineTransaction } from '@/utils/idb'

export const useTransactionStore = defineStore('transaction', {
  state: () => ({
    transactions: [],
    loading: false,
    error: null,
    isOfflineMode: !navigator.onLine,
    offlineQueueCount: 0
  }),

  actions: {
    async fetchTransactions() {
      this.loading = true
      try {
        const response = await TransactionService.getAll()
        this.transactions = response.data
      } catch (err) {
        this.error = 'Gagal mengambil data transaksi'
        console.error(err)
      } finally {
        this.loading = false
      }
    },

    async submitTransaction(transactionData) {
      try {
        // Mock a success response for immediate UI update in offline cases, if desired
        // But let's just try to call the API first
        const response = await TransactionService.create(transactionData)
        this.transactions.unshift(response.data)
        this.isOfflineMode = false
        return response.data
      } catch (err) {
        // If it's a network error, save to IndexedDB
        if (err.code === 'ERR_NETWORK' || err.message === 'Network Error') {
          this.isOfflineMode = true
          await saveOfflineTransaction(transactionData)
          await this.checkOfflineQueue()
          // Return a fake successful response so Kasir can clear the cart
          return { offline: true, message: 'Disimpan offline' }
        }
        throw err.response?.data?.message || 'Gagal memproses transaksi'
      }
    },

    async checkOfflineQueue() {
      const queue = await getOfflineTransactions()
      this.offlineQueueCount = queue.length
    },

    async syncOfflineTransactions() {
      const queue = await getOfflineTransactions()
      if (queue.length === 0) return

      this.loading = true
      let successCount = 0
      try {
        for (const item of queue) {
          // Exclude indexedDB-specific fields if necessary, like id and createdAt
          const payload = { ...item }
          const localId = payload.id
          delete payload.id
          delete payload.createdAt

          await TransactionService.create(payload)
          await clearOfflineTransaction(localId)
          successCount++
        }
        this.isOfflineMode = false
        await this.checkOfflineQueue()
        // Optionally fetch updated transactions from server
        await this.fetchTransactions()
        return successCount
      } catch (err) {
        console.error('Sync failed:', err)
        throw new Error(`Berhasil sync ${successCount} transaksi, sisa gagal karena jaringan.`)
      } finally {
        this.loading = false
      }
    },

    async fetchProfitReport(payload) {
      try {
        const response = await TransactionService.getProfitReport(payload)
        return response.data
      } catch (err) {
        throw err.response?.data?.message || 'Gagal mengambil laporan profit'
      }
    }
  }
})
