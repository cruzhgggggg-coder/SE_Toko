import axios from '@/plugins/axios'

export const NotificationService = {
  getAll() {
    return axios.get('/notifications')
  }
}
