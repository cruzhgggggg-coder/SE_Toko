import axios from '@/plugins/axios'

export const ReturnService = {
  getAll() {
    return axios.get('/returns')
  },
  create(data) {
    return axios.post('/returns', data)
  }
}
