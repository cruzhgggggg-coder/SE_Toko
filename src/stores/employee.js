import { defineStore } from 'pinia'
import { EmployeeService } from '@/services/EmployeeService'

export const useEmployeeStore = defineStore('employee', {
  state: () => ({
    employees: [],
    loading: false,
    error: null,
  }),

  actions: {
    async fetchEmployees() {
      this.loading = true
      try {
        const response = await EmployeeService.getAll()
        this.employees = response.data
      } catch (err) {
        this.error = 'Gagal mengambil data karyawan'
        console.error(err)
      } finally {
        this.loading = false
      }
    },

    async addEmployee(employeeData) {
      try {
        const response = await EmployeeService.create(employeeData)
        this.employees.push(response.data)
        return response.data
      } catch (err) {
        throw err.response?.data?.message || 'Gagal menambah karyawan'
      }
    },

    async updateEmployee(id, employeeData) {
      try {
        const response = await EmployeeService.update(id, employeeData)
        const index = this.employees.findIndex(e => e.id === id)
        if (index !== -1) {
          this.employees[index] = response.data
        }
        return response.data
      } catch (err) {
        throw err.response?.data?.message || 'Gagal mengubah data karyawan'
      }
    },

    async deleteEmployee(id) {
      try {
        await EmployeeService.delete(id)
        this.employees = this.employees.filter(e => e.id !== id)
      } catch (err) {
        throw err.response?.data?.message || 'Gagal menghapus karyawan'
      }
    }
  }
})
