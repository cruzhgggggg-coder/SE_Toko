import { defineStore } from 'pinia'
import { InventoryService } from '@/services/InventoryService'

export const useInventoryStore = defineStore('inventory', {
  state: () => ({
    products: [],
    loading: false,
    error: null,
  }),

  actions: {
    async fetchProducts() {
      this.loading = true
      this.error = null
      try {
        const response = await InventoryService.getAll()
        this.products = response.data.map(p => ({
          ...p,
          price: p.batches?.[0]?.sell_price || p.base_sell_price || 0,
          stock: p.total_stock || 0
        }))
      } catch (err) {
        this.error = 'Gagal mengambil data produk'
        console.error(err)
      } finally {
        this.loading = false
      }
    },

    async addProduct(productData) {
      try {
        const response = await InventoryService.create(productData)
        this.products.unshift(response.data)
        return response.data
      } catch (err) {
        throw err.response?.data?.message || 'Gagal menambah produk'
      }
    },

    async editProduct(productId, productData) {
      try {
        const response = await InventoryService.update(productId, productData)
        await this.fetchProducts()
        return response.data
      } catch (err) {
        throw err.response?.data?.message || 'Gagal mengubah produk'
      }
    },

    async addStock(productId, stockData) {
      try {
        const response = await InventoryService.addStock(productId, stockData)
        // Refresh product data to update total stock
        await this.fetchProducts()
        return response.data
      } catch (err) {
        throw err.response?.data?.message || 'Gagal menambah stok'
      }
    },

    async deleteProduct(productId) {
      try {
        const response = await InventoryService.delete(productId)
        this.products = this.products.filter(p => p.id !== productId)
        return response.data
      } catch (err) {
        throw err.response?.data?.message || 'Gagal menghapus produk'
      }
    },
  },

  getters: {
    stats: (state) => {
      const totalSKU = state.products.length
      const lowStock = state.products.filter(p => p.total_stock > 0 && p.total_stock <= p.min_stock).length
      const emptyStock = state.products.filter(p => p.total_stock === 0).length
      const totalValue = state.products.reduce((acc, p) => {
        const batchTotal = (p.batches || []).reduce((bAcc, b) => bAcc + ((b.current_qty || 0) * (b.buy_price || 0)), 0)
        return acc + batchTotal
      }, 0)
      
      return { totalSKU, lowStock, emptyStock, totalValue }
    }
  }
})
