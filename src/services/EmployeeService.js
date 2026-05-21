import axios from '@/plugins/axios'

export const EmployeeService = {
  getAll() {
    return axios.get('/users')
  },
  create(data) {
    return axios.post('/users', data)
  },
  update(id, data) {
    return axios.put(`/users/${id}`, data)
  },
  delete(id) {
    return axios.delete(`/users/${id}`)
  }
}
