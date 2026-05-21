<template>
  <div class="role-selection-container">
    <div class="role-selection-card">
      <div class="logo-section">
        <div class="logo-circle">
          <i class="fas fa-store"></i>
        </div>
        <h2>TOKO SUMBER MAKMUR</h2>
        <p>Pilih role untuk melanjutkan ke sistem</p>
      </div>

      <div class="roles-grid">
        <div 
          v-for="role in roles" 
          :key="role.id"
          class="role-card"
          :class="{ active: selectedRole === role.id }"
          @click="selectRole(role.id)"
        >
          <div class="role-icon" :style="{ backgroundColor: role.color + '20', color: role.color }">
            <i :class="role.icon"></i>
          </div>
          <h3>{{ role.name }}</h3>
          <p>{{ role.desc }}</p>
          <div class="selection-indicator">
            <i class="fas fa-check-circle"></i>
          </div>
        </div>
      </div>

      <div class="action-section">
        <button 
          @click="confirmRole" 
          class="btn-confirm" 
          :disabled="!selectedRole"
        >
          Masuk sebagai {{ currentRoleName }}
          <i class="fas fa-arrow-right"></i>
        </button>
        <button @click="backToLogin" class="btn-back">
          <i class="fas fa-sign-out-alt"></i>
          Ganti Akun
        </button>
      </div>

      <div class="footer">
        <p>Versi 2.0.4-stable &copy; 2026 Toko Sumber Makmur</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth = useAuthStore()

const selectedRole = ref(auth.role || null)

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

const currentRoleName = computed(() => {
  const role = roles.find(r => r.id === selectedRole.value)
  return role ? role.name : '...'
})

const selectRole = (roleId) => {
  selectedRole.value = roleId
}

const confirmRole = () => {
  if (!selectedRole.value) return
  
  if (!auth.isAuthenticated) {
    router.push({ name: 'Login', query: { role: selectedRole.value } })
  } else {
    auth.setRole(selectedRole.value)
    if (selectedRole.value === 'kasir') {
      router.push('/kasir')
    } else if (selectedRole.value === 'admin') {
      router.push('/inventory')
    } else {
      router.push('/')
    }
  }
}

const backToLogin = () => {
  auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.role-selection-container {
  min-height: 100vh;
  width: 100vw;
  background: linear-gradient(135deg, #F1F5F9 0%, #E2E8F0 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  font-family: 'Inter', sans-serif;
}

.role-selection-card {
  width: 100%;
  max-width: 800px;
  background: white;
  border-radius: 32px;
  box-shadow: 0 20px 50px rgba(15, 23, 42, 0.1);
  padding: 48px;
  animation: slideUp 0.6s ease-out;
}

@keyframes slideUp {
  from { opacity: 0; transform: translateY(30px); }
  to { opacity: 1; transform: translateY(0); }
}

.logo-section {
  text-align: center;
  margin-bottom: 40px;
}

.logo-circle {
  width: 64px;
  height: 64px;
  background: #0F172A;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
  border-radius: 16px;
  margin: 0 auto 16px;
}

.logo-section h2 {
  font-size: 24px;
  font-weight: 800;
  color: #0F172A;
  margin-bottom: 8px;
  letter-spacing: -0.5px;
}

.logo-section p {
  color: #64748B;
  font-size: 16px;
}

.roles-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
  margin-bottom: 40px;
}

@media (max-width: 768px) {
  .roles-grid {
    grid-template-columns: 1fr;
  }
}

.role-card {
  background: #F8FAFC;
  border: 2px solid #F1F5F9;
  border-radius: 24px;
  padding: 24px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.role-card:hover {
  transform: translateY(-5px);
  border-color: #CBD5E1;
  background: white;
  box-shadow: 0 10px 20px rgba(0,0,0,0.05);
}

.role-card.active {
  background: white;
  border-color: #0F172A;
  box-shadow: 0 15px 30px rgba(15, 23, 42, 0.1);
}

.role-icon {
  width: 56px;
  height: 56px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  margin: 0 auto 16px;
  transition: all 0.3s;
}

.role-card.active .role-icon {
  transform: scale(1.1);
}

.role-card h3 {
  font-size: 18px;
  font-weight: 700;
  color: #1E293B;
  margin-bottom: 8px;
}

.role-card p {
  font-size: 13px;
  color: #64748B;
  line-height: 1.5;
}

.selection-indicator {
  position: absolute;
  top: 16px;
  right: 16px;
  font-size: 20px;
  color: #0F172A;
  opacity: 0;
  transform: scale(0.5);
  transition: all 0.3s;
}

.role-card.active .selection-indicator {
  opacity: 1;
  transform: scale(1);
}

.action-section {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.btn-confirm {
  width: 100%;
  padding: 16px;
  background: #0F172A;
  color: white;
  border: none;
  border-radius: 16px;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  transition: all 0.2s;
}

.btn-confirm:hover:not(:disabled) {
  background: #1E293B;
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(15, 23, 42, 0.2);
}

.btn-confirm:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-back {
  background: transparent;
  border: none;
  color: #64748B;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: color 0.2s;
}

.btn-back:hover {
  color: #1E293B;
}

.footer {
  margin-top: 40px;
  text-align: center;
  font-size: 12px;
  color: #94A3B8;
}
</style>
