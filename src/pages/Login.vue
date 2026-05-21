<template>
  <div class="login-container">
    <!-- Left Side: Image/Branding -->
    <div class="login-visual">
      <div class="visual-overlay"></div>
      <div class="visual-content">
        <h1>Sistem POS Modern untuk Toko Anda</h1>
        <p>Kelola stok, transaksi, dan pelanggan dalam satu platform yang terintegrasi.</p>
      </div>
    </div>

    <!-- Right Side: Login Form -->
    <div class="login-form-container">
      <div class="login-form-box">
        <div class="system-status">
          <span class="status-dot"></span>
          Sistem Aktif
        </div>

        <div class="logo-section">
          <div class="logo-circle">
            <i class="fas fa-store"></i>
          </div>
          <h2>TOKO SUMBER MAKMUR</h2>
          <p>Sistem Manajemen Toko & POS</p>
        </div>

        <!-- Embedded Role Selection if missing -->
        <div v-if="!form.role" class="embedded-role-selection">
          <h3>Pilih Role Anda</h3>
          <div class="role-options-grid">
            <div v-for="role in roles" :key="role.id" class="role-option" @click="form.role = role.id">
              <div class="role-icon-box" :style="{ backgroundColor: role.color + '15', color: role.color }">
                <i :class="role.icon"></i>
              </div>
              <span>{{ role.name }}</span>
            </div>
          </div>
        </div>

        <form v-else @submit.prevent="handleLogin" class="login-form">
          <div class="selected-role-header">
            <div class="role-info">
              <div class="mini-icon" :style="{ backgroundColor: (selectedRoleInfo?.color || '#000') + '15', color: selectedRoleInfo?.color || '#000' }">
                <i :class="selectedRoleInfo?.icon || 'fas fa-user'"></i>
              </div>
              <span>Masuk sebagai <strong>{{ selectedRoleInfo?.name || 'Unknown' }}</strong></span>
            </div>
            <button type="button" @click="form.role = ''" class="btn-change">Ubah</button>
          </div>

          <div v-if="errorMsg" class="error-banner">
            <i class="fas fa-exclamation-circle"></i>
            {{ errorMsg }}
          </div>

          <div class="form-group">
            <label>Username</label>
            <div class="input-wrapper">
              <i class="fas fa-user input-icon"></i>
              <input v-model="form.username" type="text" placeholder="Masukkan username" class="form-input" required>
            </div>
          </div>

          <div class="form-group">
            <label>Password</label>
            <div class="input-wrapper">
              <i class="fas fa-lock input-icon"></i>
              <input v-model="form.password" type="password" placeholder="Masukkan password" class="form-input" required>
            </div>
          </div>

          <button type="submit" class="btn-login" :disabled="loading">
            <span v-if="loading" class="spinner"></span>
            <span v-else>Masuk ke Sistem</span>
            <i v-if="!loading" class="fas fa-sign-in-alt"></i>
          </button>

          <div class="demo-credentials">
            <p>Demo Akun:</p>
            <code>{{ getDemoAccount() }}</code>
          </div>
        </form>

        <div class="login-footer">
          <p>Versi 2.0.4-stable &copy; 2026 Toko Sumber Makmur</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()

const roles = [
  { 
    id: 'owner', 
    name: 'Admin Utama', 
    icon: 'fas fa-user-shield', 
    color: '#0F172A',
    desc: 'Pemilik dengan akses seluruh fitur sistem.'
  },
  { 
    id: 'admin', 
    name: 'Admin Gudang', 
    icon: 'fas fa-boxes', 
    color: '#2563EB',
    desc: 'Kelola stok, inventaris, dan data barang.'
  },
  { 
    id: 'kasir', 
    name: 'Kasir', 
    icon: 'fas fa-cash-register', 
    color: '#059669',
    desc: 'Fokus pada transaksi dan pelayanan pelanggan.'
  }
]

const form = reactive({
  username: '',
  password: '',
  role: route.query.role || '',
  remember: false
})

const selectedRoleInfo = computed(() => {
  return roles.find(r => r.id === form.role)
})

const getDemoAccount = () => {
  const mapping = {
    'owner': 'owner / password123',
    'admin': 'admin / password123',
    'kasir': 'kasir1 / password123'
  }
  return mapping[form.role] || 'Silakan pilih role dahulu'
}

onMounted(() => {
  // If role is in query, use it. Otherwise stay here and show embedded selection.
  if (route.query.role) {
    form.role = route.query.role
  }
})

const loading = ref(false)
const errorMsg = ref('')

const handleLogin = async () => {
  if (!form.username || !form.password) {
    errorMsg.value = 'Username dan Password wajib diisi.'
    return
  }
  
  if (!form.role || !['owner', 'admin', 'kasir'].includes(form.role)) {
    errorMsg.value = 'Silakan pilih Role terlebih dahulu.'
    form.role = ''
    return
  }
  
  loading.value = true
  errorMsg.value = ''
  
  try {
    await auth.login({
      username: form.username,
      password: form.password,
      role: form.role
    })
    
    // Redirect based on role
    if (form.role === 'kasir') {
      router.push('/kasir')
    } else if (form.role === 'admin') {
      router.push('/inventory')
    } else {
      router.push('/')
    }
  } catch (error) {
    console.error('Login error:', error)
    if (error.response && error.response.data && error.response.data.message) {
      errorMsg.value = error.response.data.message
    } else {
      errorMsg.value = 'Gagal terhubung ke server. Pastikan username dan password benar.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-container {
  display: flex;
  height: 100vh;
  width: 100vw;
  overflow: hidden;
  font-family: 'Inter', sans-serif;
}

.error-banner {
  background-color: #FEF2F2;
  border: 1px solid #FEE2E2;
  color: #DC2626;
  padding: 12px;
  border-radius: 12px;
  margin-bottom: 20px;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 8px;
}

/* Left Side */
.login-visual {
  flex: 1;
  background: linear-gradient(rgba(15, 23, 42, 0.7), rgba(15, 23, 42, 0.7)), 
              url('https://images.unsplash.com/photo-1534723452862-4c874018d66d?q=80&w=2070&auto=format&fit=crop');
  background-size: cover;
  background-position: center;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  padding: 80px;
  position: relative;
}

@media (max-width: 1024px) {
  .login-visual {
    display: none;
  }
}

.visual-content {
  z-index: 2;
  max-width: 500px;
}

.visual-content h1 {
  font-size: 3rem;
  font-weight: 800;
  margin-bottom: 24px;
  line-height: 1.2;
}

.visual-content p {
  font-size: 1.25rem;
  opacity: 0.9;
  line-height: 1.6;
}

/* Right Side */
.login-form-container {
  width: 550px;
  background-color: var(--color-bg-page, #F1F5F9);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px;
}

@media (max-width: 1024px) {
  .login-form-container {
    width: 100%;
  }
}

.login-form-box {
  width: 100%;
  max-width: 400px;
  background: white;
  padding: 40px;
  border-radius: 24px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
  position: relative;
}

.system-status {
  position: absolute;
  top: 24px;
  right: 24px;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
  font-weight: 600;
  color: var(--color-primary, #059669);
  background: #ECFDF5;
  padding: 4px 12px;
  border-radius: 100px;
}

.status-dot {
  width: 8px;
  height: 8px;
  background-color: currentColor;
  border-radius: 50%;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.5; transform: scale(1.2); }
  100% { opacity: 1; transform: scale(1); }
}

.logo-section {
  text-align: center;
  margin-bottom: 32px;
}

.logo-circle {
  width: 72px;
  height: 72px;
  background: var(--color-primary);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 30px;
  border-radius: 20px;
  margin: 0 auto 16px;
  box-shadow: 0 8px 24px rgba(5, 150, 105, 0.3);
}

.logo-section h2 {
  font-size: 21px;
  font-weight: 800;
  color: var(--color-text-primary);
  margin-bottom: 4px;
  letter-spacing: 0.5px;
}

.logo-section p {
  color: #64748B;
  font-size: 14px;
}

/* Embedded Role Selection */
.embedded-role-selection h3 {
  font-size: 16px;
  font-weight: 700;
  color: #1E293B;
  margin-bottom: 20px;
  text-align: center;
}

.role-options-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 12px;
}

.role-option {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px;
  background: #F8FAFC;
  border: 1.5px solid #E2E8F0;
  border-radius: 16px;
  cursor: pointer;
  transition: all 0.2s;
}

.role-option:hover {
  background: var(--color-primary-light);
  border-color: var(--color-primary);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(5, 150, 105, 0.12);
}

.role-icon-box {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
}

.role-option span {
  font-weight: 700;
  color: #334155;
}

/* Selected Role Header */
.selected-role-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #F8FAFC;
  padding: 12px 16px;
  border-radius: 16px;
  border: 1px solid #E2E8F0;
  margin-bottom: 24px;
}

.role-info {
  display: flex;
  align-items: center;
  gap: 10px;
}

.mini-icon {
  width: 28px;
  height: 28px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
}

.role-info span {
  font-size: 13px;
  color: #64748B;
}

.btn-change {
  background: transparent;
  border: none;
  color: #2563EB;
  font-weight: 700;
  font-size: 12px;
  cursor: pointer;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  font-size: 14px;
  font-weight: 600;
  color: #475569;
  margin-bottom: 8px;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 16px;
  color: #94A3B8;
  font-size: 16px;
}

.form-input {
  width: 100%;
  padding: 14px 16px 14px 48px;
  border: 1.5px solid #E2E8F0;
  border-radius: 14px;
  font-size: 15px;
  transition: all 0.2s;
  background-color: #F8FAFC;
}

.form-input:focus {
  outline: none;
  border-color: var(--color-primary);
  background-color: white;
  box-shadow: 0 0 0 4px rgba(5, 150, 105, 0.08);
}

.btn-login {
  width: 100%;
  padding: 16px;
  background-color: var(--color-primary);
  color: white;
  border: none;
  border-radius: 14px;
  font-size: 17px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  box-shadow: 0 4px 16px rgba(5, 150, 105, 0.25);
}

.btn-login:hover {
  background-color: var(--color-primary-hover);
  transform: translateY(-1px);
  box-shadow: 0 10px 24px rgba(5, 150, 105, 0.3);
}

.demo-credentials {
  margin-top: 24px;
  padding: 12px;
  background: #F1F5F9;
  border-radius: 12px;
  text-align: center;
}

.demo-credentials p {
  font-size: 11px;
  color: #64748B;
  margin-bottom: 4px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 700;
}

.demo-credentials code {
  font-family: 'JetBrains Mono', monospace;
  font-size: 13px;
  color: #0F172A;
  font-weight: 600;
}

.spinner {
  width: 20px;
  height: 20px;
  border: 3px solid rgba(255,255,255,0.3);
  border-radius: 50%;
  border-top-color: white;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.login-footer {
  margin-top: 40px;
  text-align: center;
  font-size: 12px;
  color: #94A3B8;
}
</style>
