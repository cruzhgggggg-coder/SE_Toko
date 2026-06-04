<template>
  <div class="notif-overlay" @click.self="$emit('close')">
    <div class="notif-panel">
      <!-- Header -->
      <div class="notif-header">
        <div class="notif-title">
          <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0"/>
          </svg>
          NOTIFICATION CENTER
        </div>
        <button class="close-btn" @click="$emit('close')">
          <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </button>
      </div>

      <!-- Content -->
      <div class="notif-scroll">
        <!-- Stok Menipis -->
        <div class="notif-group-label" v-if="lowStockAlerts.length > 0">
          <span class="dot warning"></span>
          PEMBERITAHUAN STOK MENIPIS
        </div>

        <div class="notif-item" v-for="(item, index) in lowStockAlerts" :key="'ls'+index">
          <div class="notif-icon warning">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </div>
          <div class="notif-body">
            <div class="notif-meta">
              <span class="notif-name">{{ item.title }}</span>
              <button class="dismiss-btn" @click="dismissNotification(item)" title="Hapus Notifikasi">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
              </button>
            </div>
            <p class="notif-desc">{{ item.message }}</p>
            <button class="notif-action-btn" @click="$router.push('/inventory'); $emit('close')">RESTOCK SEKARANG →</button>
          </div>
        </div>

        <!-- Kedaluwarsa -->
        <div class="notif-group-label" style="margin-top:16px" v-if="expiryAlerts.length > 0">
          <span class="dot danger"></span>
          PERINGATAN BARANG KEDALUWARSA
        </div>

        <div class="notif-item danger-item" v-for="(item, index) in expiryAlerts" :key="'exp'+index">
          <div class="notif-icon danger">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
          </div>
          <div class="notif-body">
            <div class="notif-meta">
              <span class="notif-name">{{ item.title }}</span>
              <div style="display: flex; align-items: center; gap: 8px;">
                <span class="badge badge-danger" style="font-size:10px">KADALUARSA</span>
                <button class="dismiss-btn" @click="dismissNotification(item)" title="Hapus Notifikasi">
                  <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                  </svg>
                </button>
              </div>
            </div>
            <p class="notif-desc">{{ item.message }}</p>
            <div class="notif-actions-row">
              <button class="notif-danger-btn" @click="dismissNotification(item)">BUANG</button>
            </div>
          </div>
        </div>

        <!-- Hutang Overdue -->
        <div class="notif-group-label" style="margin-top:16px" v-if="overdueDebts.length > 0">
          <span class="dot danger"></span>
          HUTANG JATUH TEMPO
        </div>

        <div class="notif-item" v-for="(item, index) in overdueDebts" :key="'ov'+index">
          <div class="notif-icon danger">
             <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
          </div>
          <div class="notif-body">
            <div class="notif-meta">
              <span class="notif-name">{{ item.title }}</span>
              <button class="dismiss-btn" @click="dismissNotification(item)" title="Hapus Notifikasi">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
              </button>
            </div>
            <p class="notif-desc">{{ item.message }}</p>
          </div>
        </div>
      </div>

      <!-- Footer Summary -->
      <div class="notif-footer">
        <div class="notif-summary-header">
          <span>PENGHITUNGAN CEPAT</span>
          <button class="hapus-semua" @click="dismissAll">HAPUS SEMUA</button>
        </div>
        <div class="notif-summary-grid">
          <div class="summary-card">
            <span class="summary-label">STOCK / DEBT</span>
            <span class="summary-num">{{ lowStockAlerts.length + overdueDebts.length }}</span>
          </div>
          <div class="summary-card danger">
            <span class="summary-label">EXPIRED</span>
            <span class="summary-num">{{ expiryAlerts.length }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import axios from '@/plugins/axios'
import { useAuthStore } from '../../stores/auth'

const emit = defineEmits(['close', 'update-count'])

const authStore = useAuthStore()

const notifications = ref([])
const dismissedKeys = ref([])
let pollInterval = null

async function fetchNotifications() {
  try {
    const response = await axios.get('/notifications')
    notifications.value = response.data
  } catch (error) {
    console.error('Failed to fetch notifications:', error)
  }
}

onMounted(() => {
  fetchNotifications()
  pollInterval = setInterval(fetchNotifications, 60000) // Poll every 60 seconds
  const stored = localStorage.getItem('dismissed_notifications')
  if (stored) {
    try {
      dismissedKeys.value = JSON.parse(stored)
    } catch (e) {
      dismissedKeys.value = []
    }
  }
})

onUnmounted(() => {
  if (pollInterval) clearInterval(pollInterval)
})

const filteredNotifications = computed(() => {
  return notifications.value.filter(n => {
    const key = `${n.type}:${n.title || ''}:${n.message || ''}`
    return !dismissedKeys.value.includes(key)
  })
})

const lowStockAlerts = computed(() => {
  return filteredNotifications.value.filter(n => n.type === 'LOW_STOCK')
})

const expiryAlerts = computed(() => {
  return filteredNotifications.value.filter(n => n.type === 'EXPIRY')
})

const overdueDebts = computed(() => {
  return filteredNotifications.value.filter(n => n.type === 'OVERDUE_DEBT')
})

const dismissNotification = (item) => {
  const key = `${item.type}:${item.title || ''}:${item.message || ''}`
  if (!dismissedKeys.value.includes(key)) {
    dismissedKeys.value.push(key)
    localStorage.setItem('dismissed_notifications', JSON.stringify(dismissedKeys.value))
    emit('update-count')
  }
}

const dismissAll = () => {
  notifications.value.forEach(item => {
    const key = `${item.type}:${item.title || ''}:${item.message || ''}`
    if (!dismissedKeys.value.includes(key)) {
      dismissedKeys.value.push(key)
    }
  })
  localStorage.setItem('dismissed_notifications', JSON.stringify(dismissedKeys.value))
  emit('update-count')
}
</script>

<style scoped>
.notif-overlay {
  position: fixed;
  inset: 0;
  z-index: 300;
  background: rgba(0,0,0,0.3);
}

.notif-panel {
  position: fixed;
  top: 0; right: 0;
  width: 360px;
  height: 100vh;
  background: var(--color-card-bg);
  display: flex;
  flex-direction: column;
  box-shadow: var(--shadow-lg);
  animation: slideInRight 0.25s ease;
}

@keyframes slideInRight {
  from { transform: translateX(100%); opacity: 0; }
  to   { transform: translateX(0);    opacity: 1; }
}

.notif-header {
  background: var(--color-primary);
  padding: 20px var(--spacing-lg);
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-shrink: 0;
}

.notif-title {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #fff;
  font-weight: 700;
  font-size: var(--font-sm);
  letter-spacing: 0.8px;
}

.close-btn {
  color: rgba(255,255,255,0.6);
  padding: 4px;
  border-radius: 6px;
  display: flex;
}
.close-btn:hover { color: #fff; background: rgba(255,255,255,0.1); }

.notif-scroll {
  flex: 1;
  overflow-y: auto;
  padding: var(--spacing-lg);
}

.notif-group-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: var(--font-xs);
  font-weight: 700;
  color: var(--color-text-secondary);
  letter-spacing: 0.6px;
  text-transform: uppercase;
  margin-bottom: 10px;
}

.dot {
  width: 8px; height: 8px;
  border-radius: 50%;
  flex-shrink: 0;
}
.dot.warning { background: var(--color-warning); }
.dot.danger  { background: var(--color-danger); }

.notif-item {
  display: flex;
  gap: 12px;
  background: #F8FAFC;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  padding: 12px;
  margin-bottom: 8px;
}

.danger-item { background: #FFF5F5; border-color: #FECACA; }

.notif-icon {
  width: 36px; height: 36px;
  border-radius: var(--radius-sm);
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.notif-icon.warning { background: var(--color-warning-light); color: var(--color-warning); }
.notif-icon.danger  { background: var(--color-danger-light); color: var(--color-danger); }
.notif-icon.muted   { background: #E2E8F0; color: var(--color-text-muted); }

.notif-body { flex: 1; min-width: 0; }

.notif-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 4px;
}

.notif-name { font-size: var(--font-sm); font-weight: 600; color: var(--color-text-primary); }
.notif-time { font-size: var(--font-xs); color: var(--color-text-muted); }
.notif-desc { font-size: var(--font-sm); color: var(--color-text-secondary); margin-bottom: 8px; }

.notif-action-btn {
  width: 100%;
  background: var(--color-primary);
  color: #fff;
  padding: 6px;
  border-radius: var(--radius-sm);
  font-size: var(--font-xs);
  font-weight: 700;
  letter-spacing: 0.5px;
  cursor: pointer;
}

.notif-actions-row { display: flex; gap: 8px; align-items: center; }

.notif-danger-btn {
  flex: 1;
  background: var(--color-danger);
  color: #fff;
  padding: 6px;
  border-radius: var(--radius-sm);
  font-size: var(--font-xs);
  font-weight: 700;
  letter-spacing: 0.5px;
}

.icon-btn-sm {
  width: 30px; height: 30px;
  border-radius: var(--radius-sm);
  border: 1px solid var(--color-border);
  display: flex; align-items: center; justify-content: center;
  color: var(--color-text-secondary);
}

/* Footer */
.notif-footer {
  border-top: 1px solid var(--color-border);
  padding: var(--spacing-md) var(--spacing-lg);
  background: #F8FAFC;
  flex-shrink: 0;
}

.notif-summary-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: var(--font-xs);
  font-weight: 700;
  color: var(--color-text-secondary);
  letter-spacing: 0.5px;
  margin-bottom: 10px;
}

.hapus-semua {
  font-size: var(--font-xs);
  font-weight: 700;
  color: var(--color-text-secondary);
  letter-spacing: 0.5px;
}
.hapus-semua:hover { color: var(--color-danger); }

.notif-summary-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
}

.summary-card {
  background: #fff;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  padding: 10px 12px;
  text-align: center;
}

.summary-label {
  display: block;
  font-size: 10px;
  font-weight: 600;
  color: var(--color-text-muted);
  letter-spacing: 0.5px;
  margin-bottom: 4px;
}

.summary-num {
  display: block;
  font-size: var(--font-2xl);
  font-weight: 800;
  color: var(--color-text-primary);
}

.summary-card.danger .summary-num { color: var(--color-danger); }

.dismiss-btn {
  background: transparent;
  border: none;
  color: var(--color-text-muted);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2px;
  border-radius: 4px;
  transition: all var(--transition-fast);
}

.dismiss-btn:hover {
  color: var(--color-danger);
  background: var(--color-danger-light);
}
</style>
