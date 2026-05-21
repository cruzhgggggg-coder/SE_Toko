import axios from '@/plugins/axios'

export const ReportService = {
  getDailyReports() {
    return axios.get('/financial-reports')
  },
  generatePreview() {
    return axios.get('/financial-reports/generate')
  },
  saveDailyReport(data) {
    return axios.post('/financial-reports', data)
  },
  getDetailed(payload) {
    return axios.post('/reports/detailed', payload)
  }
}
