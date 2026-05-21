import axios from '@/plugins/axios'

export const BackupService = {
  getBackup() {
    return axios.get('/backup', { responseType: 'blob' })
  },
  restoreBackup(formData) {
    return axios.post('/restore', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  }
}
