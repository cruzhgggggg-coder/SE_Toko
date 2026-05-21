import axios from '@/plugins/axios'

export const AuthService = {
  login(credentials) {
    return axios.post('/login', credentials)
  },
  logout() {
    return axios.post('/logout')
  }
}
