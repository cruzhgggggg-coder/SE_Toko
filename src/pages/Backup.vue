<template>
  <div class="backup-page">
    <div class="backup-header">
      <div class="backup-header-info">
        <div class="cloud-logo">
          <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0018 9h-1.26A8 8 0 103 16.3"/></svg>
        </div>
        <div>
          <h1 class="backup-title">TOKO SUMBER MAKMUR Terminal</h1>
          <div class="sync-pill" :class="{ active: isSyncing }">
            <span class="sync-dot"></span>
            {{ isSyncing ? 'MENYINKRONKAN...' : 'SINKRONISASI AKTIF' }}
          </div>
        </div>
      </div>
    </div>

    <div class="backup-content">
      <!-- Main Panel -->
      <div class="backup-main">
        <div class="section-card">
          <div class="system-status-badge">
            <span class="status-dot"></span>
            SYSTEM STATUS: OPTIMAL
          </div>
          <div class="backup-hero">
            <div>
              <h2 class="hero-title">Integritas Data Cloud</h2>
              <p class="hero-desc">Sinkronkan seluruh inventaris, riwayat transaksi, dan data pelanggan ke Atlas Cloud secara aman.</p>
              <div class="backup-action">
                <button class="btn-backup-now" @click="startBackup" :disabled="isSyncing">
                  <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0018 9h-1.26A8 8 0 103 16.3"/></svg>
                  {{ isSyncing ? 'Menyinkronkan...' : 'Simpan Data' }}
                </button>
                <div class="progress-col">
                  <span class="progress-label">PROSES</span>
                  <div class="progress-bar-wrap">
                    <div class="progress-bar" :style="{ width: progress + '%' }"></div>
                  </div>
                  <span class="progress-pct">{{ progress }}%</span>
                </div>
              </div>
            </div>
            <div class="cloud-illustration">
              <svg width="100" height="80" fill="none" viewBox="0 0 24 24" stroke="#CBD5E1" stroke-width="1">
                <polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/>
                <path d="M20.39 18.39A5 5 0 0018 9h-1.26A8 8 0 103 16.3"/>
              </svg>
            </div>
          </div>
        </div>

        <!-- Log Files -->
        <div class="section-card">
          <div class="log-header">
            <h3 class="log-title">LOG & RIWAYAT TRANSAKSI</h3>
            <button class="btn-view-log">⏱ Lihat Log Detail</button>
          </div>

          <div v-for="file in backupFiles" :key="file.name" class="backup-file-row">
            <div class="file-icon">
              <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/>
              </svg>
            </div>
            <div class="file-info">
              <p class="file-name">{{ file.name }}</p>
              <p class="file-meta">Size: {{ file.size }} • {{ file.records.toLocaleString() }} Records</p>
            </div>
            <div class="file-date">
              <p class="file-date-val">{{ file.date }}</p>
              <span class="verified-badge">VERIFIED INTEGRITY</span>
            </div>
            <button class="download-btn">
              <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Side Panel -->
      <div class="backup-sidebar">
        <!-- Last Backup -->
        <div class="sidebar-card success-card">
          <div class="success-icon">
            <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          </div>
          <p class="success-label">CADANGAN TERAKHIR BERHASIL</p>
          <p class="success-date">Oct 24, 2024</p>
          <p class="success-time">14:22:05 PM (UTC +7)</p>
        </div>

        <!-- Offline Queue -->
        <div class="sidebar-card warn-card">
          <div class="warn-icon">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          </div>
          <div>
            <p class="warn-title">Antrean Offline</p>
            <p class="warn-desc">14 perubahan lokal belum sinkron</p>
          </div>
        </div>

        <!-- Export Excel -->
        <div class="sidebar-card">
          <h3 class="sidebar-section-title">Ekspor Tabel Data</h3>
          <p class="sidebar-desc">Buat laporan .xlsx universal untuk audit dan pelaporan offline.</p>
          <button class="btn-export">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            Ekspor ke Excel
          </button>
        </div>

        <!-- Quick Recovery -->
        <div class="sidebar-card">
          <h3 class="sidebar-section-title">Pemulihan Cepat</h3>
          <button class="btn-recovery" v-for="rec in recoveryOptions" :key="rec.label" @click="handleRecovery(rec)">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/></svg>
            {{ rec.label }}
          </button>
          <input type="file" ref="fileInput" @change="handleFileUpload" style="display: none" accept=".sqlite" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from '@/plugins/axios'
import { useAuthStore } from '../stores/auth'

const isSyncing = ref(false)
const progress = ref(0)
const fileInput = ref(null)

const authStore = useAuthStore()

const backupFiles = [
  { name: 'full_system_backup_last.sqlite', size: 'Unknown', records: 'N/A', date: new Date().toLocaleDateString() },
]

const recoveryOptions = [
  { label: 'Impor Database Cloud (.sqlite)',  key: 'import' },
]

async function startBackup() {
  isSyncing.value = true
  progress.value = 10
  
  try {
    const response = await axios.get('/backup', {
      responseType: 'blob'
    })
    
    progress.value = 80
    
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    
    const contentDisposition = response.headers['content-disposition']
    let fileName = 'backup.sqlite'
    if (contentDisposition) {
      const fileNameMatch = contentDisposition.match(/filename="(.+)"/)
      if (fileNameMatch && fileNameMatch.length === 2)
        fileName = fileNameMatch[1]
      else if (contentDisposition.includes('filename=')) {
        fileName = contentDisposition.split('filename=')[1].replace(/["']/g, '')
      }
    }
    
    link.setAttribute('download', fileName)
    document.body.appendChild(link)
    link.click()
    
    window.URL.revokeObjectURL(url)
    document.body.removeChild(link)
    
    progress.value = 100
  } catch (error) {
    console.error('Backup failed:', error)
    alert('Failed to download backup.')
  } finally {
    setTimeout(() => {
      isSyncing.value = false
      progress.value = 0
    }, 1000)
  }
}

function handleRecovery(rec) {
  if (rec.key === 'import') {
    fileInput.value.click()
  }
}

async function handleFileUpload(event) {
  const file = event.target.files[0]
  if (!file) return
  
  if (!file.name.endsWith('.sqlite')) {
    alert('Pastikan file berekstensi .sqlite')
    event.target.value = ''
    return
  }

  if (!confirm('Apakah Anda yakin ingin memulihkan database ini? Data saat ini akan diganti sepenuhnya.')) {
    event.target.value = ''
    return
  }

  isSyncing.value = true
  progress.value = 10
  
  const formData = new FormData()
  formData.append('backup_file', file)

  try {
    await axios.post('/restore', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      },
      onUploadProgress: (progressEvent) => {
        if (progressEvent.total) {
            const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total)
            progress.value = percentCompleted > 90 ? 90 : percentCompleted
        }
      }
    })
    
    progress.value = 100
    alert('Database berhasil dipulihkan. Halaman akan dimuat ulang.')
    window.location.reload()
  } catch (error) {
    console.error('Restore failed:', error)
    alert(error.response?.data?.message || 'Gagal memulihkan database.')
  } finally {
    isSyncing.value = false
    progress.value = 0
    event.target.value = ''
  }
}
</script>

<style scoped>
.backup-page { max-width: 1100px; }

.backup-header {
  background: var(--color-sidebar-bg); color: #fff;
  border-radius: var(--radius-lg); padding: 16px 24px;
  margin-bottom: var(--spacing-xl);
  display: flex; align-items: center; justify-content: space-between;
}
.backup-header-info { display: flex; align-items: center; gap: 16px; }
.cloud-logo {
  width: 44px; height: 44px;
  background: rgba(255,255,255,0.1);
  border-radius: var(--radius-md);
  display: flex; align-items: center; justify-content: center;
}
.backup-title { font-size: var(--font-lg); font-weight: 700; margin-bottom: 4px; }
.sync-pill {
  display: inline-flex; align-items: center; gap: 6px;
  background: rgba(255,255,255,0.1);
  padding: 4px 12px; border-radius: var(--radius-full);
  font-size: var(--font-xs); font-weight: 700;
}
.sync-dot {
  width: 8px; height: 8px; border-radius: 50%;
  background: #94A3B8;
}
.sync-pill.active .sync-dot { background: var(--color-primary); animation: pulse 1.5s infinite; }
@keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.3; } }

.backup-content { display: grid; grid-template-columns: 1fr 300px; gap: var(--spacing-lg); }
.backup-main { display: flex; flex-direction: column; gap: var(--spacing-lg); }

.section-card {
  background: var(--color-card-bg); border: 1px solid var(--color-border);
  border-radius: var(--radius-lg); padding: var(--spacing-xl);
}

.system-status-badge {
  display: inline-flex; align-items: center; gap: 6px;
  background: var(--color-primary-light); color: var(--color-primary);
  padding: 6px 12px; border-radius: var(--radius-full);
  font-size: var(--font-xs); font-weight: 700; letter-spacing: 0.5px;
  margin-bottom: var(--spacing-lg);
}
.system-status-badge::before { content: '●'; font-size: 10px; }

.backup-hero {
  display: flex; align-items: center; justify-content: space-between;
}
.hero-title { font-size: var(--font-2xl); font-weight: 800; margin-bottom: 8px; }
.hero-desc  { font-size: var(--font-sm); color: var(--color-text-muted); max-width: 400px; margin-bottom: var(--spacing-lg); }

.backup-action { display: flex; align-items: center; gap: var(--spacing-lg); }
.btn-backup-now {
  display: flex; align-items: center; gap: 10px;
  padding: 14px 24px; background: var(--color-sidebar-bg); color: #fff;
  border-radius: var(--radius-md); font-weight: 700; font-size: var(--font-base);
  transition: background var(--transition-fast); white-space: nowrap;
}
.btn-backup-now:hover:not(:disabled) { background: #1E293B; }
.btn-backup-now:disabled { opacity: 0.7; cursor: not-allowed; }

.progress-col { display: flex; align-items: center; gap: 10px; }
.progress-label { font-size: var(--font-xs); font-weight: 700; color: var(--color-text-muted); white-space: nowrap; }
.progress-bar-wrap { width: 140px; height: 6px; background: #F1F5F9; border-radius: 3px; overflow: hidden; }
.progress-bar { height: 100%; background: var(--color-primary); border-radius: 3px; transition: width 0.4s ease; }
.progress-pct { font-size: var(--font-xs); font-weight: 700; color: var(--color-text-muted); }

.cloud-illustration { opacity: 0.4; }

/* Log */
.log-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--spacing-lg); }
.log-title { font-size: var(--font-xs); font-weight: 700; letter-spacing: 0.8px; color: var(--color-text-muted); }
.btn-view-log { font-size: var(--font-xs); font-weight: 600; color: var(--color-primary); }

.backup-file-row {
  display: flex; align-items: center; gap: var(--spacing-md);
  padding: var(--spacing-md) 0; border-bottom: 1px solid var(--color-border);
}
.backup-file-row:last-child { border-bottom: none; }
.file-icon {
  width: 40px; height: 40px;
  background: #F1F5F9; border-radius: var(--radius-md);
  display: flex; align-items: center; justify-content: center;
  color: var(--color-text-secondary); flex-shrink: 0;
}
.file-info { flex: 1; }
.file-name { font-size: var(--font-sm); font-weight: 700; font-family: monospace; }
.file-meta { font-size: var(--font-xs); color: var(--color-text-muted); margin-top: 2px; }
.file-date { text-align: right; }
.file-date-val  { font-size: var(--font-sm); font-weight: 700; }
.verified-badge {
  display: inline-block;
  font-size: 10px; font-weight: 700;
  color: var(--color-primary); letter-spacing: 0.3px;
}
.download-btn {
  width: 32px; height: 32px;
  border: 1px solid var(--color-border); border-radius: var(--radius-sm);
  display: flex; align-items: center; justify-content: center;
  color: var(--color-text-muted); transition: all var(--transition-fast);
}
.download-btn:hover { border-color: var(--color-primary); color: var(--color-primary); }

/* Sidebar */
.backup-sidebar { display: flex; flex-direction: column; gap: var(--spacing-md); }
.sidebar-card {
  background: var(--color-card-bg); border: 1px solid var(--color-border);
  border-radius: var(--radius-lg); padding: var(--spacing-lg);
}
.success-card { background: #EFF6FF; border-color: #BFDBFE; text-align: center; }
.success-icon {
  width: 50px; height: 50px; background: var(--color-primary);
  border-radius: 50%; display: flex; align-items: center; justify-content: center;
  color: #fff; margin: 0 auto var(--spacing-sm);
}
.success-label { font-size: var(--font-xs); font-weight: 700; color: var(--color-text-muted); letter-spacing: 0.5px; margin-bottom: 8px; }
.success-date  { font-size: var(--font-2xl); font-weight: 800; }
.success-time  { font-size: var(--font-xs); color: var(--color-text-muted); }

.warn-card { display: flex; align-items: center; gap: 12px; background: var(--color-warning-light); border-color: var(--color-warning); }
.warn-icon { color: var(--color-warning); flex-shrink: 0; }
.warn-title { font-size: var(--font-base); font-weight: 700; }
.warn-desc  { font-size: var(--font-xs); color: var(--color-text-muted); }

.sidebar-section-title { font-size: var(--font-sm); font-weight: 700; margin-bottom: 8px; }
.sidebar-desc { font-size: var(--font-sm); color: var(--color-text-muted); margin-bottom: var(--spacing-md); }

.btn-export {
  display: flex; align-items: center; justify-content: center; gap: 8px;
  width: 100%; padding: 12px;
  background: var(--color-primary); color: #fff;
  border-radius: var(--radius-md); font-weight: 700;
  transition: background var(--transition-fast);
}
.btn-export:hover { background: var(--color-primary-hover); }

.btn-recovery {
  display: flex; align-items: center; gap: 10px;
  width: 100%; padding: 12px;
  border: 1.5px solid var(--color-border); border-radius: var(--radius-md);
  font-size: var(--font-sm); font-weight: 600; color: var(--color-text-secondary);
  margin-bottom: 8px; transition: all var(--transition-fast);
}
.btn-recovery:last-child { margin-bottom: 0; }
.btn-recovery:hover { border-color: var(--color-secondary); color: var(--color-secondary); }
</style>
