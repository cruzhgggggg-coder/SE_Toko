<template>
  <div class="pengaturan-page">
    <div class="page-title-row">
      <h1 class="page-title">Pengaturan Sistem</h1>
    </div>

    <!-- Tabs -->
    <div class="settings-tabs">
      <button
        v-for="tab in tabs"
        :key="tab.key"
        class="settings-tab"
        :class="{ active: activeTab === tab.key }"
        @click="activeTab = tab.key"
      >{{ tab.label }}</button>
    </div>

    <div class="settings-body">
      <!-- Left Panel -->
      <div class="settings-main">

        <!-- Profile Tab -->
        <div v-if="activeTab === 'profile'" class="settings-section">
          <div class="section-card">
            <div class="section-header">
              <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>
              <h3>Identitas Operasional</h3>
            </div>

            <div class="form-group">
              <label>NAMA TOKO</label>
              <input v-model="store.shop_name" class="form-input" type="text" />
            </div>
            <div class="form-group">
              <label>NO TELEPON</label>
              <input v-model="store.shop_phone" class="form-input" type="text" />
            </div>
            <div class="form-group">
              <label>ALAMAT LENGKAP</label>
              <textarea v-model="store.shop_address" class="form-textarea"></textarea>
            </div>
          </div>

          <div class="settings-row-2">
            <!-- Change Password -->
            <div class="section-card">
              <div class="section-header">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="16" r="1"/><path d="M12 8v4"/><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                <h3>Ubah Password Owner</h3>
              </div>
              <div class="form-group">
                <label>PASSWORD SAAT INI</label>
                <input v-model="passwords.current" class="form-input" type="password" placeholder="Password Saat Ini" />
              </div>
              <div class="form-group">
                <label>PASSWORD BARU</label>
                <input v-model="passwords.new" class="form-input" type="password" placeholder="Password Baru" />
              </div>
              <button class="btn-save-pwd" @click="savePassword">Simpan Sandi</button>
            </div>

            <!-- Backup -->
            <div class="section-card">
              <div class="section-header">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0018 9h-1.26A8 8 0 103 16.3"/></svg>
                <h3 class="green-text">Manajemen Backup Data</h3>
              </div>
              <p class="section-desc">Amankan data transaksi harian Anda ke server cloud Atlas Terminal.</p>
              <button class="btn-backup" @click="$router.push('/backup')">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0018 9h-1.26A8 8 0 103 16.3"/></svg>
                Backup ke Cloud Sekarang
              </button>
              <p class="last-backup">⏱ LAST SUCCESSFUL BACKUP: OCT 24, 2024</p>
            </div>
          </div>
        </div>

        <!-- Security Tab -->
        <div v-if="activeTab === 'security'" class="settings-section">
          <div class="section-card">
            <div class="section-header">
              <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
              <h3>Keamanan Akses</h3>
            </div>
            <div class="toggle-row">
              <div>
                <p class="toggle-label">Wajib PIN saat buka kasir</p>
                <p class="toggle-desc">Kasir harus memasukkan PIN 4 digit sebelum memulai sesi</p>
              </div>
              <div class="toggle-switch" :class="{ on: security.pinRequired }" @click="security.pinRequired = !security.pinRequired">
                <div class="toggle-knob"></div>
              </div>
            </div>
            <div class="toggle-row">
              <div>
                <p class="toggle-label">Auto-lock setelah 15 menit idle</p>
                <p class="toggle-desc">Layar akan terkunci otomatis jika tidak ada aktivitas</p>
              </div>
              <div class="toggle-switch" :class="{ on: security.autoLock }" @click="security.autoLock = !security.autoLock">
                <div class="toggle-knob"></div>
              </div>
            </div>
            <div class="toggle-row">
              <div>
                <p class="toggle-label">Log aktivitas pengguna</p>
                <p class="toggle-desc">Catat semua tindakan kasir untuk audit trail</p>
              </div>
              <div class="toggle-switch" :class="{ on: security.activityLog }" @click="security.activityLog = !security.activityLog">
                <div class="toggle-knob"></div>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- Right Panel -->
      <div class="settings-sidebar">
        <!-- Logo Upload -->
        <div class="section-card">
          <h3 class="sidebar-section-title">Logo Toko</h3>
          <div class="logo-upload-area" @click="triggerFileInput">
            <svg width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="#CBD5E1" stroke-width="1.5">
              <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
            </svg>
            <p class="upload-hint">Klik untuk Unggah</p>
            <p class="upload-sub">Format: PNG, JPG (Max 2MB)</p>
          </div>
          <input ref="fileInput" type="file" accept="image/*" style="display:none" @change="handleLogoUpload" />
          <div v-if="logoPreview" class="logo-preview" style="margin-top: 12px;">
            <img :src="logoPreview" alt="Logo Preview" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 2px solid var(--color-border);" />
          </div>
        </div>

        <!-- Receipt Preview -->
        <div class="section-card">
          <h3 class="sidebar-section-title">PRATINJAU STRUK</h3>
          <div class="receipt-preview">
            <div class="receipt-line long"></div>
            <div class="receipt-line short"></div>
            <div class="receipt-line medium"></div>
            <div class="receipt-line long"></div>
          </div>
        </div>


      </div>
    </div>

    <!-- Footer Actions -->
    <div class="settings-footer">
      <button class="btn-cancel-settings" @click="fetchSettings">Batalkan Perubahan</button>
      <button class="btn-save-all" @click="saveAll">Simpan Seluruh Pengaturan</button>
    </div>

    <!-- System Sync Toast -->
    <div class="sync-badge" v-if="showSyncBadge">
      <div class="sync-icon">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/></svg>
      </div>
      <div>
        <p class="sync-title">Sistem Sinkron</p>
        <p class="sync-desc">Seluruh data telah ter-backup otomatis.</p>
      </div>
    </div>

    <!-- Toast -->
    <ToastNotification v-if="toast.show" :message="toast.message" :type="toast.type" @hide="toast.show = false" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from '@/plugins/axios'
import { useAuthStore } from '../stores/auth'
import { useUIStore } from '@/stores/ui'
import ToastNotification from '@/components/shared/ToastNotification.vue'

const activeTab = ref('profile')
const showSyncBadge = ref(false)
const fileInput = ref(null)
const logoPreview = ref(null)

const authStore = useAuthStore()

const tabs = [
  { key: 'profile', label: 'Profil Toko' },
  { key: 'security', label: 'Keamanan & Backup' },
]

const store = ref({ shop_name: '', shop_address: '', shop_phone: '' })
const passwords = ref({ current: '', new: '' })
const security = ref({ pinRequired: true, autoLock: false, activityLog: true })

const toast = ref({ show: false, message: '', type: 'success' })

function triggerFileInput() { fileInput.value?.click() }

function handleLogoUpload(event) {
  const file = event.target.files[0]
  if (!file) return
  if (file.size > 2 * 1024 * 1024) {
    toast.value = { show: true, message: 'Ukuran file maksimal 2MB.', type: 'error' }
    return
  }
  const reader = new FileReader()
  reader.onload = (e) => {
    logoPreview.value = e.target.result
  }
  reader.readAsDataURL(file)
  toast.value = { show: true, message: 'Logo berhasil dipilih. Klik Simpan untuk menyimpan.', type: 'success' }
}

async function savePassword() {
  if (!passwords.value.current || !passwords.value.new) {
    toast.value = { show: true, message: 'Password saat ini dan password baru wajib diisi.', type: 'error' }
    return
  }
  if (passwords.value.new.length < 6) {
    toast.value = { show: true, message: 'Password baru minimal 6 karakter.', type: 'error' }
    return
  }
  try {
    await axios.post('/settings', {
      settings: {
        password_current: passwords.value.current,
        password_new: passwords.value.new
      }
    })
    toast.value = { show: true, message: 'Password berhasil diubah!', type: 'success' }
    passwords.value = { current: '', new: '' }
  } catch (error) {
    toast.value = { show: true, message: error.response?.data?.message || 'Gagal mengubah password.', type: 'error' }
  }
}

async function fetchSettings() {
  try {
    const response = await axios.get('/settings')
    const data = response.data
    store.value.shop_name = data.shop_name || ''
    store.value.shop_address = data.shop_address || ''
    store.value.shop_phone = data.shop_phone || ''
    
    security.value.pinRequired = data.security_pin_required === 'true' || data.security_pin_required === true
    security.value.autoLock = data.security_auto_lock === 'true' || data.security_auto_lock === true
    security.value.activityLog = data.security_activity_log === 'true' || data.security_activity_log === true
  } catch (error) {
    console.error('Failed to fetch settings:', error)
  }
}

async function saveAll() {
  try {
    const settingsPayload = {
      settings: {
        shop_name: store.value.shop_name,
        shop_address: store.value.shop_address,
        shop_phone: store.value.shop_phone,
        security_pin_required: security.value.pinRequired.toString(),
        security_auto_lock: security.value.autoLock.toString(),
        security_activity_log: security.value.activityLog.toString(),
      }
    }
    
    await axios.post('/settings', settingsPayload)
    
    const uiStore = useUIStore()
    uiStore.setShopName(store.value.shop_name)
    
    showSyncBadge.value = true
    setTimeout(() => { showSyncBadge.value = false }, 3000)
    
    toast.value = { show: true, message: 'Pengaturan Berhasil Disimpan', type: 'success' }
  } catch (error) {
    console.error('Failed to save settings:', error)
    toast.value = { show: true, message: 'Gagal Menyimpan Pengaturan', type: 'error' }
  }
}

onMounted(() => {
  fetchSettings()
})
</script>

<style scoped>
.pengaturan-page { max-width: 1100px; }
.page-title-row { margin-bottom: var(--spacing-md); }
.page-title { font-size: var(--font-3xl); font-weight: 700; }

.settings-tabs { display: flex; gap: 4px; margin-bottom: var(--spacing-xl); }
.settings-tab {
  padding: 10px 24px; border-radius: var(--radius-md);
  font-size: var(--font-base); font-weight: 600;
  color: var(--color-text-secondary); border: 1px solid var(--color-border);
  transition: all var(--transition-fast); background: #fff;
}
.settings-tab:hover { border-color: var(--color-primary); color: var(--color-primary); }
.settings-tab.active { background: var(--color-primary); color: #fff; border-color: var(--color-primary); }

.settings-body { display: grid; grid-template-columns: 1fr 300px; gap: var(--spacing-lg); }
.settings-main { display: flex; flex-direction: column; gap: var(--spacing-lg); }
.settings-section { display: flex; flex-direction: column; gap: var(--spacing-lg); }
.settings-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-lg); }

.section-card {
  background: var(--color-card-bg);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-lg);
  padding: var(--spacing-xl);
  display: flex; flex-direction: column; gap: var(--spacing-md);
}
.section-header {
  display: flex; align-items: center; gap: 10px;
  font-size: var(--font-lg); font-weight: 700; color: var(--color-text-primary);
}
.section-header svg { color: var(--color-text-secondary); }
.green-text { color: var(--color-primary); }
.section-desc { font-size: var(--font-sm); color: var(--color-text-muted); }

.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-group label { font-size: var(--font-xs); font-weight: 700; color: var(--color-text-muted); letter-spacing: 0.5px; }
.form-input {
  padding: 12px 14px; border: 1.5px solid var(--color-border);
  border-radius: var(--radius-md); font-size: var(--font-base);
  transition: border-color var(--transition-fast); font-family: inherit; background: #F8FAFC;
}
.form-input:focus { outline: none; border-color: var(--color-primary); background: #fff; }
.form-textarea {
  padding: 12px 14px; border: 1.5px solid var(--color-border);
  border-radius: var(--radius-md); font-size: var(--font-base);
  height: 80px; resize: none; font-family: inherit; background: #F8FAFC;
}
.form-textarea:focus { outline: none; border-color: var(--color-primary); background: #fff; }

.btn-save-pwd {
  width: 100%; padding: 12px;
  background: var(--color-primary); color: #fff;
  border-radius: var(--radius-md); font-weight: 700;
  transition: background var(--transition-fast); margin-top: var(--spacing-sm);
}
.btn-save-pwd:hover { background: var(--color-primary-hover); }

.btn-backup {
  display: flex; align-items: center; justify-content: center; gap: 8px;
  width: 100%; padding: 12px;
  background: var(--color-primary); color: #fff;
  border-radius: var(--radius-md); font-weight: 700;
  transition: background var(--transition-fast);
}
.btn-backup:hover { background: var(--color-primary-hover); }
.last-backup { font-size: var(--font-xs); color: var(--color-text-muted); text-align: center; }

/* Toggle */
.toggle-row {
  display: flex; align-items: center; justify-content: space-between;
  padding: var(--spacing-md) 0;
  border-bottom: 1px solid var(--color-border);
}
.toggle-row:last-child { border-bottom: none; }
.toggle-label { font-size: var(--font-base); font-weight: 600; margin-bottom: 2px; }
.toggle-desc  { font-size: var(--font-xs); color: var(--color-text-muted); }

.toggle-switch {
  width: 44px; height: 24px;
  background: #CBD5E1; border-radius: 12px;
  position: relative; cursor: pointer;
  transition: background var(--transition-fast);
  flex-shrink: 0;
}
.toggle-switch.on { background: var(--color-primary); }
.toggle-knob {
  position: absolute; top: 2px; left: 2px;
  width: 20px; height: 20px;
  background: #fff; border-radius: 50%;
  transition: transform var(--transition-fast);
  box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}
.toggle-switch.on .toggle-knob { transform: translateX(20px); }

/* Sidebar Cards */
.settings-sidebar { display: flex; flex-direction: column; gap: var(--spacing-md); }
.sidebar-section-title { font-size: var(--font-sm); font-weight: 700; color: var(--color-text-secondary); letter-spacing: 0.5px; margin-bottom: var(--spacing-sm); }

.logo-upload-area {
  border: 2px dashed var(--color-border);
  border-radius: var(--radius-md);
  padding: var(--spacing-xl);
  display: flex; flex-direction: column; align-items: center; gap: 8px;
  cursor: pointer; transition: border-color var(--transition-fast);
}
.logo-upload-area:hover { border-color: var(--color-primary); }
.upload-hint { font-size: var(--font-sm); font-weight: 600; color: var(--color-text-secondary); }
.upload-sub  { font-size: var(--font-xs); color: var(--color-text-muted); }

.receipt-preview { display: flex; flex-direction: column; gap: 8px; padding: var(--spacing-md); background: #F8FAFC; border-radius: var(--radius-sm); }
.receipt-line { height: 8px; background: #E2E8F0; border-radius: 4px; }
.receipt-line.long   { width: 100%; }
.receipt-line.medium { width: 70%; }
.receipt-line.short  { width: 45%; }



/* Footer */
.settings-footer {
  display: flex; justify-content: flex-end; gap: 12px;
  margin-top: var(--spacing-xl); padding-top: var(--spacing-lg);
  border-top: 1px solid var(--color-border);
}
.btn-cancel-settings {
  padding: 12px 24px; font-weight: 600;
  color: var(--color-text-secondary);
  transition: color var(--transition-fast);
}
.btn-cancel-settings:hover { color: var(--color-danger); }
.btn-save-all {
  padding: 12px 28px;
  background: var(--color-primary); color: #fff;
  border-radius: var(--radius-md); font-weight: 700;
  transition: background var(--transition-fast);
}
.btn-save-all:hover { background: var(--color-primary-hover); }

/* Sync Badge */
.sync-badge {
  position: fixed; bottom: 24px; right: 24px;
  display: flex; align-items: center; gap: 12px;
  background: #fff; border-radius: var(--radius-lg); padding: 14px 18px;
  box-shadow: 0 4px 20px rgba(15, 23, 42, 0.12);
  border: 1px solid var(--color-border);
}
.sync-icon {
  width: 40px; height: 40px;
  background: var(--color-primary-light); border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  color: var(--color-primary);
  animation: spin 2s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
.sync-title { font-size: var(--font-base); font-weight: 700; }
.sync-desc  { font-size: var(--font-xs); color: var(--color-text-muted); }
</style>
