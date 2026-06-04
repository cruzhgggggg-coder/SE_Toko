import axios from '@/plugins/axios'

export const CustomerService = {
  getAll() {
    return axios.get('/customers')
  },
  create(data) {
    return axios.post('/customers', data)
  },
  update(id, data) {
    return axios.put(`/customers/${id}`, data)
  },
  delete(id) {
    return axios.delete(`/customers/${id}`)
  }
}
