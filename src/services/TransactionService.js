import axios from '@/plugins/axios'

export const TransactionService = {
  getAll() {
    return axios.get('/transactions')
  },
  create(data) {
    return axios.post('/transactions', data)
  },
  getProfitReport(payload) {
    return axios.post('/reports/profit', payload)
  }
}
