import axios from '@/plugins/axios'

export const InventoryService = {
  getAll() {
    return axios.get('/products')
  },
  create(data) {
    return axios.post('/products', data)
  },
  update(id, data) {
    return axios.put(`/products/${id}`, data)
  },
  addStock(id, data) {
    return axios.post(`/products/${id}/stock`, data)
  }
}
