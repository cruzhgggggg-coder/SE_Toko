import { defineStore } from 'pinia'
import { CustomerService } from '@/services/CustomerService'

export const useCustomerStore = defineStore('customer', {
  state: () => ({
    customers: [],
    loading: false,
    error: null,
  }),

  actions: {
    async fetchCustomers() {
      this.loading = true
      try {
        const response = await CustomerService.getAll()
        this.customers = response.data
      } catch (err) {
        this.error = 'Gagal mengambil data pelanggan'
        console.error(err)
      } finally {
        this.loading = false
      }
    },

    async addCustomer(customerData) {
      try {
        const response = await CustomerService.create(customerData)
        this.customers.unshift(response.data)
        return response.data
      } catch (err) {
        throw err.response?.data?.message || 'Gagal menambah pelanggan'
      }
    },

    async updateCustomer(id, customerData) {
      try {
        const response = await CustomerService.update(id, customerData)
        const index = this.customers.findIndex(c => c.id === id)
        if (index !== -1) {
          this.customers[index] = response.data
        }
        return response.data
      } catch (err) {
        throw err.response?.data?.message || 'Gagal mengubah data pelanggan'
      }
    }
  }
})
