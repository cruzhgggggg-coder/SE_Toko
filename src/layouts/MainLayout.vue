<template>
  <div class="app-wrapper">
    <!-- SIDEBAR -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <span class="sidebar-logo" v-html="formattedShopName"></span>
        <span class="sidebar-role">{{ currentPage }}</span>
      </div>

      <nav class="sidebar-nav">
        <RouterLink v-if="auth.allowedRoutes.includes('Kasir')" to="/kasir" class="nav-item" :class="{ active: route.name === 'Kasir' }">
          <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path d="M9 7H6a2 2 0 00-2 2v9a2 2 0 002 2h12a2 2 0 002-2V9a2 2 0 00-2-2h-3M9 7V5a2 2 0 014 0v2M9 7h6"/>
          </svg>
          Kasir
        </RouterLink>

        <RouterLink v-if="auth.allowedRoutes.includes('Inventory')" to="/inventory" class="nav-item" :class="{ active: route.name === 'Inventory' }">
          <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
          </svg>
          Stok Barang
        </RouterLink>

        <RouterLink v-if="auth.allowedRoutes.includes('Pelanggan')" to="/pelanggan" class="nav-item" :class="{ active: route.name === 'Pelanggan' }">
          <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zM23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
          </svg>
          Data Pelanggan
        </RouterLink>



        <RouterLink v-if="auth.allowedRoutes.includes('Dashboard')" to="/" class="nav-item" :class="{ active: route.name === 'Dashboard' }">
          <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
            <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
          </svg>
          Beranda
        </RouterLink>

        <RouterLink v-if="auth.allowedRoutes.includes('Laporan')" to="/laporan" class="nav-item" :class="{ active: route.name === 'Laporan' }">
          <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
          </svg>
          Laporan
        </RouterLink>

        <RouterLink v-if="auth.allowedRoutes.includes('Backup')" to="/backup" class="nav-item" :class="{ active: route.name === 'Backup' }">
          <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
          </svg>
          Simpan Data
        </RouterLink>
      </nav>

      <div class="sidebar-footer">
        <RouterLink v-if="auth.allowedRoutes.includes('Pengaturan')" to="/pengaturan" class="nav-item" :class="{ active: route.name === 'Pengaturan' }">
          <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="3"/>
            <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/>
          </svg>
          Pengaturan
        </RouterLink>

        <button class="nav-item nav-logout" @click="handleLogout">
          <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
          </svg>
          Keluar
        </button>
      </div>
    </aside>

    <!-- MAIN CONTENT AREA -->
    <div class="main-area">
      <!-- HEADER -->
      <header class="topbar">
        <div class="topbar-left">
          <div v-if="showSearch" class="topbar-search-container">
            <div class="topbar-search">
              <i class="fas fa-search search-icon"></i>
              <input type="text" v-model="ui.searchQuery" :placeholder="searchPlaceholder" />
              <button class="barcode-btn">
                <i class="fas fa-barcode"></i>
              </button>
            </div>
          </div>
        </div>

        <div class="topbar-right">
          <div v-if="route.name === 'Kasir'" class="offline-badge">
            <i class="fas fa-wifi-slash"></i>
            <span>Offline Mode Active</span>
          </div>

          <button class="icon-btn" @click="toggleNotification" title="Notifikasi" style="position:relative">
            <i class="far fa-bell"></i>
            <span v-if="notifCount > 0" class="notif-count-badge">{{ notifCount > 99 ? '99+' : notifCount }}</span>
            <span v-else class="notif-dot"></span>
          </button>

          <div class="user-chip" @click="toggleUserMenu">
            <div class="user-text">
              <span class="user-name">{{ auth.user?.name || 'Admin Utama' }}</span>
              <span class="user-role">{{ auth.roleDisplayName?.toUpperCase() || 'ADMIN UTAMA' }}</span>
            </div>
            <div class="user-avatar">
              <i class="fas fa-user"></i>
            </div>
          </div>

          <!-- User Dropdown -->
          <div v-if="showUserMenu" class="user-dropdown">
            <div class="user-dropdown-header">
              <span class="user-dropdown-label">Sesi Aktif</span>
              <p class="user-dropdown-name">{{ auth.user?.name || 'Admin Utama' }}</p>
            </div>
            <div class="dd-divider"></div>
            <button class="dd-item" @click="showGantiUser = true">
              <i class="fas fa-user-friends"></i> Ganti User
            </button>
            <button class="dd-item" @click="$router.push('/pengaturan')">
              <i class="fas fa-cog"></i> Pengaturan
            </button>
            <div class="dd-divider"></div>
            <button class="dd-item danger" @click="handleLogout">
              <i class="fas fa-sign-out-alt"></i> Keluar
            </button>
          </div>
        </div>
      </header>

      <!-- PAGE CONTENT -->
      <main class="page-content" :class="{ 'no-padding': route.name === 'Kasir' }">
        <slot />
      </main>
    </div>

    <!-- NOTIFICATION PANEL (Aside) -->
    <NotificationPanel v-if="showNotification" @close="showNotification = false" @update-count="fetchNotifCount" />

    <!-- GANTI USER MODAL -->
    <GantiUserModal 
      v-if="showGantiUser" 
      :currentUser="{ id: 1, name: 'Admin Utama' }"
      @close="showGantiUser = false" 
      @select="handleUserChange"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useUIStore } from '@/stores/ui'
import axios from '@/plugins/axios'
import NotificationPanel from '@/components/shared/NotificationPanel.vue'
import GantiUserModal from '@/components/modals/GantiUserModal.vue'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const ui = useUIStore()

const showNotification = ref(false)
const showUserMenu = ref(false)
const showGantiUser = ref(false)
const notifCount = ref(0)
let notifPoll = null

async function fetchNotifCount() {
  try {
    const res = await axios.get('/notifications')
    let dismissed = []
    const stored = localStorage.getItem('dismissed_notifications')
    if (stored) {
      try {
        dismissed = JSON.parse(stored)
      } catch (e) {}
    }
    const filtered = res.data.filter(n => {
      const key = `${n.type}:${n.title || ''}:${n.message || ''}`
      return !dismissed.includes(key)
    })
    notifCount.value = filtered.length
  } catch (e) {
    // silent fail
  }
}

const formattedShopName = computed(() => {
  const name = ui.shopName || 'TOKO SUMBER MAKMUR'
  return name.replace(/\s+/g, '<br />')
})

onMounted(async () => {
  fetchNotifCount()
  notifPoll = setInterval(fetchNotifCount, 90000) // refresh setiap 90 detik
  try {
    const res = await axios.get('/settings')
    if (res.data && res.data.shop_name) {
      ui.setShopName(res.data.shop_name)
    }
  } catch (e) {
    console.error('Gagal memuat nama toko:', e)
  }
})

onUnmounted(() => {
  if (notifPoll) clearInterval(notifPoll)
})

const handleLogout = () => {
  auth.logout()
  router.push('/login')
}

const currentPage = computed(() => {
  const map = {
    'Kasir': 'KASIR', 
    'Inventory': 'STOK BARANG',
    'Pelanggan': 'DATA PELANGGAN', 
    'Dashboard': 'BERANDA',
    'Laporan': 'LAPORAN', 
    'Pengaturan': 'PENGATURAN', 
    'Backup': 'SIMPAN DATA',

  }
  return map[route.name] || 'BERANDA'
})

const showSearch = computed(() => ['Kasir', 'Inventory'].includes(route.name))
const searchPlaceholder = computed(() =>
  route.name === 'Kasir'
    ? 'Cari sembako (cth: Beras Maknyus) atau Scan Barcode...'
    : 'Cari SKU atau Nama Barang...'
)

function toggleNotification() {
  showNotification.value = !showNotification.value
  showUserMenu.value = false
}

function toggleUserMenu() {
  showUserMenu.value = !showUserMenu.value
  showNotification.value = false
}

function handleUserChange(user) {
  // Clearing role to force a new role selection for the new user or just logout
  // Based on the requirement, "Ganti User" should take them to role selection if they stay in the same account
  // but if they want to change the actual account, we should logout.
  // The GantiUserModal seems to imply different people, so we'll logout.
  auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.app-wrapper {
  display: flex;
  height: 100vh;
  overflow: hidden;
  background: var(--color-bg-page);
}

/* ---- SIDEBAR ---- */
.sidebar {
  width: var(--sidebar-width);
  min-width: var(--sidebar-width);
  background: var(--color-sidebar-bg);
  border-right: 1px solid var(--color-border);
  display: flex;
  flex-direction: column;
  padding: var(--spacing-lg) 0;
  overflow-y: auto;
  z-index: 100;
}

.sidebar-header {
  padding: 0 var(--spacing-md) var(--spacing-xl);
  border-bottom: 1px solid var(--color-border);
  margin-bottom: var(--spacing-md);
}

.sidebar-logo {
  display: block;
  font-size: 15px;
  font-weight: 800;
  color: var(--color-primary);
  letter-spacing: 0.3px;
  line-height: 1.3;
  text-transform: uppercase;
}

.sidebar-role {
  display: block;
  font-size: var(--font-xs);
  font-weight: 600;
  color: var(--color-text-muted);
  margin-top: 4px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.sidebar-nav {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 2px;
  padding: 0 var(--spacing-sm);
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 11px var(--spacing-md);
  border-radius: var(--radius-md);
  font-size: var(--font-base);
  font-weight: 600;
  color: var(--color-sidebar-text);
  transition: all var(--transition-fast);
  cursor: pointer;
}

.nav-item:hover {
  background: var(--color-sidebar-hover);
  color: var(--color-sidebar-text-active);
}

.nav-item.active {
  background: var(--color-sidebar-active);
  color: var(--color-sidebar-text-active);
  border-left: 3px solid var(--color-primary);
  padding-left: calc(var(--spacing-md) - 3px);
}

.sidebar-footer {
  padding: var(--spacing-md) var(--spacing-sm) 0;
  border-top: 1px solid var(--color-border);
  margin-top: var(--spacing-md);
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.nav-logout { color: var(--color-danger); }
.nav-logout:hover { background: var(--color-danger-light); color: var(--color-danger); }

/* ---- MAIN AREA ---- */
.main-area {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* ---- TOPBAR ---- */
.topbar {
  height: var(--header-height);
  background: var(--color-header-bg);
  border-bottom: 1px solid var(--color-border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 var(--spacing-xl);
  gap: var(--spacing-md);
  flex-shrink: 0;
  box-shadow: var(--shadow-sm);
}

.topbar-left { flex: 1; }
.topbar-right { display: flex; align-items: center; gap: var(--spacing-md); }

.topbar-search-container {
  display: flex;
  align-items: center;
  gap: var(--spacing-md);
}

.topbar-search {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  background: #F8FAFC;
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius-lg);
  padding: 10px 20px;
  width: 100%;
  max-width: 600px;
  transition: border-color var(--transition-fast);
}
.topbar-search:focus-within { border-color: var(--color-primary); }

.topbar-search svg { color: var(--color-text-muted); flex-shrink: 0; }

.topbar-search input {
  flex: 1;
  background: none;
  border: none;
  color: var(--color-text-primary);
  font-size: var(--font-sm);
  min-width: 0;
}

.topbar-search input::placeholder { color: var(--color-text-muted); }

.barcode-btn {
  color: var(--color-text-muted);
  padding: 0;
  display: flex;
  align-items: center;
}
.barcode-btn:hover { color: var(--color-primary); }

.offline-badge {
  display: flex;
  align-items: center;
  gap: 8px;
  background: var(--color-warning-light);
  border: 1.5px solid var(--color-warning);
  border-radius: var(--radius-full);
  padding: 6px 14px;
  font-size: 11px;
  color: var(--color-warning);
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.icon-btn {
  position: relative;
  width: 38px;
  height: 38px;
  border-radius: var(--radius-full);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-text-secondary);
  border: 1.5px solid var(--color-border);
  background: #F8FAFC;
  transition: all var(--transition-fast);
}
.icon-btn:hover { background: var(--color-primary-light); color: var(--color-primary); border-color: var(--color-primary); }

.notif-dot {
  position: absolute;
  top: 5px; right: 5px;
  width: 8px; height: 8px;
  background: var(--color-danger);
  border-radius: 50%;
  border: 2px solid #FFFFFF;
}

.notif-count-badge {
  position: absolute;
  top: 2px; right: 2px;
  min-width: 18px; height: 18px;
  background: var(--color-danger);
  color: #fff;
  border-radius: 9px;
  font-size: 10px;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 4px;
  border: 2px solid #FFFFFF;
  line-height: 1;
}

/* USER CHIP & DROPDOWN */
.user-menu-wrapper { position: relative; }

.user-chip {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 5px 10px 5px 14px;
  background: #F8FAFC;
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius-full);
  cursor: pointer;
  transition: all var(--transition-fast);
}
.user-chip:hover { border-color: var(--color-primary); background: var(--color-primary-light); }

.user-text {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 2px;
}

.user-name {
  font-size: 13px;
  color: var(--color-text-primary);
  font-weight: 700;
}

.user-role {
  font-size: 10px;
  color: var(--color-text-muted);
  font-weight: 600;
  letter-spacing: 0.5px;
}

.user-avatar {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background: var(--color-primary-light);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-primary);
  font-size: 14px;
  border: 1.5px solid var(--color-primary);
}

.user-dropdown {
  position: absolute;
  top: calc(100% + 10px);
  right: 0;
  background: var(--color-card-bg);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-lg);
  border: 1px solid var(--color-border);
  min-width: 240px;
  z-index: 200;
  overflow: hidden;
  animation: slideUp 0.15s ease;
}

.user-dropdown-header {
  padding: 16px 20px;
  background: var(--color-primary-light);
  border-bottom: 1px solid var(--color-border);
}

.user-dropdown-label {
  display: block;
  font-size: 10px;
  color: var(--color-primary);
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 4px;
}

.user-dropdown-name {
  font-size: 14px;
  font-weight: 700;
  color: var(--color-text-primary);
}

.dd-item {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
  padding: 12px 20px;
  font-size: 14px;
  font-weight: 600;
  color: var(--color-text-secondary);
  transition: all var(--transition-fast);
  text-align: left;
}

.dd-item:hover {
  background: var(--color-primary-light);
  color: var(--color-primary);
  padding-left: 24px;
}

.dd-item i {
  font-size: 16px;
  width: 20px;
  text-align: center;
  opacity: 0.7;
}

.dd-item.danger {
  color: var(--color-danger);
}

.dd-item.danger:hover {
  background: #FEF2F2;
}

.dd-divider {
  height: 1px;
  background: var(--color-border);
}

/* ---- PAGE CONTENT ---- */
.page-content {
  flex: 1;
  overflow-y: auto;
  padding: var(--spacing-xl);
}

.page-content.no-padding {
  padding: 0;
}
</style>
