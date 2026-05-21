import axios from '@/plugins/axios'

export const SettingService = {
  getSettings() {
    return axios.get('/settings')
  },
  updateSettings(data) {
    return axios.post('/settings', data)
  }
}
