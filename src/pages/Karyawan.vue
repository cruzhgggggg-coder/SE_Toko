<template>
  <div class="employee-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Manajemen Karyawan</h1>
        <p class="page-subtitle">Kelola akun, hak akses (role), dan kredensial staf toko.</p>
      </div>
      <button class="btn-add" @click="openAddModal">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Tambah Karyawan Baru
      </button>
    </div>

    <!-- Summary Cards -->
    <div class="emp-stats">
      <div class="emp-stat-card left-accent blue">
        <p class="cs-label">TOTAL KARYAWAN</p>
        <p class="cs-val">{{ totalEmployees }}</p>
        <p class="cs-trend ok">👤 Aktif Bekerja</p>
      </div>
      <div class="emp-stat-card left-accent orange">
        <p class="cs-label">ADMIN UTAMA (OWNER)</p>
        <p class="cs-val">{{ totalOwners }}</p>
        <p class="cs-trend up">👑 Akses Penuh</p>
      </div>
      <div class="emp-stat-card left-accent green">
        <p class="cs-label">KASIR & ADMIN GUDANG</p>
        <p class="cs-val">{{ totalStaff }}</p>
        <p class="cs-trend ok">💼 Operasional Toko</p>
      </div>
    </div>

    <!-- Search & Filter -->
    <div class="toolbar">
      <div class="search-box">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input v-model="search" type="text" placeholder="Cari karyawan berdasarkan nama atau username..." />
      </div>
      <select v-model="filterRole" class="select-filter">
        <option value="">Semua Hak Akses</option>
        <option value="owner">Admin Utama (Owner)</option>
        <option value="admin">Admin Gudang</option>
        <option value="kasir">Kasir</option>
      </select>
    </div>

    <!-- Employee Table -->
    <div class="table-card">
      <div class="table-head-row">
        <span class="col-employee">NAMA KARYAWAN</span>
        <span>USERNAME</span>
        <span>HAK AKSES / ROLE</span>
        <span>AKSI</span>
      </div>

      <div v-if="loading" class="table-loading">
        Sedang memuat data...
      </div>
      <div v-else-if="filteredEmployees.length === 0" class="table-empty">
        Tidak ada data karyawan ditemukan.
      </div>
      <div v-else v-for="emp in filteredEmployees" :key="emp.id" class="table-data-row">
        <div class="col-employee employee-info">
          <div class="avatar" :style="{ background: emp.avatarColor }">{{ emp.initials }}</div>
          <div>
            <p class="emp-name">{{ emp.name }}</p>
            <p class="emp-id-tag">ID: #00{{ emp.id }}</p>
          </div>
        </div>
        <span class="username-text">@{{ emp.username }}</span>
        <div>
          <span class="role-pill" :class="emp.role">
            {{ roleLabel(emp.role) }}
          </span>
        </div>
        <div class="action-btns">
          <button class="act-btn edit" @click="openEditModal(emp)" title="Edit">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          </button>
          <button class="act-btn delete" @click="handleDelete(emp)" title="Hapus">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
          </button>
        </div>
      </div>

      <div class="table-footer">
        Menampilkan {{ filteredEmployees.length }} dari {{ employeesList.length }} karyawan
      </div>
    </div>

    <!-- Modal Form (Add/Edit) -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
      <div class="modal-box">
        <div class="modal-header">
          <h2 class="modal-title">{{ isEdit ? 'Edit Akun Karyawan' : 'Tambah Karyawan Baru' }}</h2>
          <button @click="showModal = false" class="close-btn">✕</button>
        </div>
        <div class="add-form">
          <div class="form-group">
            <label>Nama Lengkap</label>
            <input v-model="formData.name" class="form-input" type="text" placeholder="Nama lengkap karyawan" required />
          </div>
          <div class="form-group">
            <label>Username</label>
            <input v-model="formData.username" class="form-input" type="text" placeholder="username_staf" required />
          </div>
          <div class="form-group">
            <label>Password {{ isEdit ? '(Kosongkan jika tidak ingin diubah)' : '' }}</label>
            <input v-model="formData.password" class="form-input" type="password" placeholder="Minimal 6 karakter" />
          </div>
          <div class="form-group">
            <label>Hak Akses / Jabatan</label>
            <select v-model="formData.role" class="form-input">
              <option value="owner">Admin Utama (Owner)</option>
              <option value="admin">Admin Gudang</option>
              <option value="kasir">Kasir</option>
            </select>
          </div>
          <div class="modal-actions">
            <button class="btn-cancel" @click="showModal = false">Batal</button>
            <button class="btn-confirm" @click="submitForm">{{ isEdit ? 'Simpan Perubahan' : 'Tambah Staf' }}</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast Notification -->
    <ToastNotification
      v-if="toast.show"
      :message="toast.message"
      :type="toast.type"
      @hide="toast.show = false"
    />

    <!-- Status bar -->
    <div class="status-bar">
      <span class="status-dot"></span>
      SINKRONISASI PENGGUNA AKTIF • DATABASE REST API SECURE
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useEmployeeStore } from '@/stores/employee'
import ToastNotification from '@/components/shared/ToastNotification.vue'

const employeeStore = useEmployeeStore()

const search = ref('')
const filterRole = ref('')
const showModal = ref(false)
const isEdit = ref(false)
const selectedEmpId = ref(null)

const formData = ref({
  name: '',
  username: '',
  password: '',
  role: 'kasir'
})

const toast = ref({
  show: false,
  message: '',
  type: 'success'
})

const loading = computed(() => employeeStore.loading)

onMounted(async () => {
  await employeeStore.fetchEmployees()
})

const employeesList = computed(() => {
  return employeeStore.employees.map(emp => ({
    ...emp,
    initials: emp.name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase(),
    avatarColor: stringToColor(emp.name)
  }))
})

const totalEmployees = computed(() => employeesList.value.length)
const totalOwners = computed(() => employeesList.value.filter(e => e.role === 'owner').length)
const totalStaff = computed(() => employeesList.value.filter(e => e.role === 'kasir' || e.role === 'admin').length)

const filteredEmployees = computed(() => {
  return employeesList.value.filter(e => {
    const matchSearch = !search.value || 
      e.name.toLowerCase().includes(search.value.toLowerCase()) || 
      e.username.toLowerCase().includes(search.value.toLowerCase())
    const matchRole = !filterRole.value || e.role === filterRole.value
    return matchSearch && matchRole
  })
})

function roleLabel(role) {
  return {
    owner: 'Admin Utama (Owner)',
    admin: 'Admin Gudang',
    kasir: 'Kasir'
  }[role] || role
}

function stringToColor(str) {
  let hash = 0
  for (let i = 0; i < str.length; i++) {
    hash = str.charCodeAt(i) + ((hash << 5) - hash)
  }
  const colors = ['#1E40AF', '#374151', '#059669', '#DC2626', '#7C3AED', '#B45309', '#6366F1']
  return colors[Math.abs(hash) % colors.length]
}

function openAddModal() {
  isEdit.value = false
  selectedEmpId.value = null
  formData.value = {
    name: '',
    username: '',
    password: '',
    role: 'kasir'
  }
  showModal.value = true
}

function openEditModal(emp) {
  isEdit.value = true
  selectedEmpId.value = emp.id
  formData.value = {
    name: emp.name,
    username: emp.username,
    password: '',
    role: emp.role
  }
  showModal.value = true
}

async function submitForm() {
  if (!formData.value.name || !formData.value.username) {
    showToast('Nama dan Username wajib diisi!', 'danger')
    return
  }

  try {
    if (isEdit.value) {
      const payload = {
        name: formData.value.name,
        username: formData.value.username,
        role: formData.value.role
      }
      if (formData.value.password) {
        payload.password = formData.value.password
      }
      await employeeStore.updateEmployee(selectedEmpId.value, payload)
      showToast('Data karyawan berhasil diubah', 'success')
    } else {
      if (!formData.value.password) {
        showToast('Password wajib diisi untuk karyawan baru!', 'danger')
        return
      }
      await employeeStore.addEmployee(formData.value)
      showToast('Karyawan baru berhasil ditambahkan', 'success')
    }
    showModal.value = false
  } catch (err) {
    showToast(err, 'danger')
  }
}

async function handleDelete(emp) {
  if (confirm(`Apakah Anda yakin ingin menghapus akun @${emp.username}?`)) {
    try {
      await employeeStore.deleteEmployee(emp.id)
      showToast('Karyawan berhasil dihapus', 'success')
    } catch (err) {
      showToast(err, 'danger')
    }
  }
}

function showToast(message, type = 'success') {
  toast.value.message = message
  toast.value.type = type
  toast.value.show = true
}
</script>

<style scoped>
.employee-page { max-width: 1100px; }
.page-header {
  display: flex; align-items: flex-start; justify-content: space-between;
  margin-bottom: var(--spacing-xl);
}
.page-title    { font-size: var(--font-3xl); font-weight: 700; }
.page-subtitle { font-size: var(--font-sm); color: var(--color-text-muted); margin-top: 4px; }
.btn-add {
  display: flex; align-items: center; gap: 8px;
  background: var(--color-primary); color: #fff;
  padding: 12px 20px; border-radius: var(--radius-md);
  font-weight: 700; transition: background var(--transition-fast);
}
.btn-add:hover { background: var(--color-primary-hover); }

/* Stats */
.emp-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--spacing-md); margin-bottom: var(--spacing-xl); }
.emp-stat-card {
  background: var(--color-card-bg);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-lg);
  padding: var(--spacing-lg) var(--spacing-xl);
  border-left: 5px solid;
}
.emp-stat-card.left-accent.orange { border-left-color: var(--color-warning); }
.emp-stat-card.left-accent.blue   { border-left-color: var(--color-secondary); }
.emp-stat-card.left-accent.green  { border-left-color: var(--color-primary); }

.cs-label { font-size: var(--font-xs); font-weight: 700; color: var(--color-text-muted); letter-spacing: 0.5px; margin-bottom: 8px; }
.cs-val   { font-size: var(--font-3xl); font-weight: 800; margin-bottom: 6px; }
.cs-trend { font-size: var(--font-sm); font-weight: 600; }
.cs-trend.up   { color: var(--color-warning); }
.cs-trend.ok   { color: var(--color-primary); }

/* Toolbar */
.toolbar { display: flex; gap: var(--spacing-md); margin-bottom: var(--spacing-lg); }
.search-box {
  flex: 1; display: flex; align-items: center; gap: 10px;
  background: #fff; border: 1.5px solid var(--color-border);
  border-radius: var(--radius-md); padding: 0 14px; color: var(--color-text-muted);
}
.search-box:focus-within { border-color: var(--color-primary); }
.search-box input { flex: 1; border: none; padding: 12px 0; font-size: var(--font-base); outline: none; }
.select-filter {
  padding: 12px 14px; border: 1.5px solid var(--color-border);
  border-radius: var(--radius-md); font-size: var(--font-sm); font-weight: 600;
  color: var(--color-text-secondary); background: #fff;
}
.select-filter:focus { outline: none; border-color: var(--color-primary); }

/* Table */
.table-card { background: var(--color-card-bg); border: 1px solid var(--color-border); border-radius: var(--radius-lg); overflow: hidden; }
.table-head-row {
  display: grid; grid-template-columns: 2.5fr 1.5fr 2fr 120px;
  padding: 12px 20px; background: #F8FAFC;
  border-bottom: 1px solid var(--color-border);
  font-size: var(--font-xs); font-weight: 700; color: var(--color-text-muted); letter-spacing: 0.5px;
}
.table-data-row {
  display: grid; grid-template-columns: 2.5fr 1.5fr 2fr 120px;
  align-items: center; padding: 16px 20px;
  border-bottom: 1px solid var(--color-border);
  transition: background var(--transition-fast);
}
.table-data-row:hover { background: #F8FAFC; }

.col-employee { display: flex; align-items: center; gap: 12px; }
.avatar {
  width: 40px; height: 40px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-weight: 700; font-size: var(--font-base);
  flex-shrink: 0;
}
.emp-name { font-size: var(--font-base); font-weight: 700; }
.emp-id-tag { font-size: var(--font-xs); color: var(--color-text-muted); }
.username-text { font-size: var(--font-sm); color: var(--color-text-secondary); font-weight: 600; }

.role-pill {
  display: inline-flex; align-items: center;
  padding: 6px 12px; border-radius: var(--radius-full);
  font-size: var(--font-xs); font-weight: 700;
}
.role-pill.owner { background: var(--color-warning-light); color: var(--color-warning); }
.role-pill.admin { background: var(--color-primary-light); color: var(--color-primary); }
.role-pill.kasir { background: #EFF6FF; color: var(--color-secondary); }

.action-btns { display: flex; gap: 6px; }
.act-btn {
  width: 30px; height: 30px; border-radius: var(--radius-sm);
  display: flex; align-items: center; justify-content: center;
  transition: all var(--transition-fast);
}
.act-btn.edit { border: 1px solid var(--color-border); color: var(--color-text-secondary); }
.act-btn.edit:hover { border-color: var(--color-secondary); color: var(--color-secondary); }
.act-btn.delete { border: 1px solid var(--color-danger-light); color: var(--color-danger); background: var(--color-danger-light); }
.act-btn.delete:hover { background: var(--color-danger); color: #fff; }

.table-footer {
  padding: 14px 20px; font-size: var(--font-sm); color: var(--color-text-muted);
  border-top: 1px solid var(--color-border); background: #F8FAFC;
}

.status-bar {
  display: flex; align-items: center; gap: 8px;
  font-size: var(--font-xs); font-weight: 700; color: var(--color-primary);
  margin-top: var(--spacing-lg);
}
.status-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--color-primary); }

.table-loading, .table-empty {
  padding: 40px; text-align: center; font-size: var(--font-base); color: var(--color-text-muted);
}

/* Modal */
.modal-overlay {
  position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7);
  display: flex; align-items: center; justify-content: center; z-index: 1000;
}
.modal-box {
  background: #fff; border-radius: var(--radius-xl);
  width: 480px; max-width: 95vw; padding: var(--spacing-xl);
  box-shadow: 0 25px 50px rgba(15, 23, 42, 0.3);
  animation: slideUp 0.2s ease;
}
@keyframes slideUp { from { transform: translateY(24px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.modal-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--spacing-lg); }
.modal-title  { font-size: var(--font-xl); font-weight: 700; }
.close-btn { color: var(--color-text-muted); padding: 6px; border-radius: var(--radius-sm); font-size: 18px; }
.close-btn:hover { background: #F1F5F9; }
.add-form { display: flex; flex-direction: column; gap: var(--spacing-md); }
.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-group label { font-size: var(--font-xs); font-weight: 700; color: var(--color-text-muted); }
.form-input {
  padding: 11px 14px; border: 1.5px solid var(--color-border);
  border-radius: var(--radius-md); font-size: var(--font-base);
  transition: border-color var(--transition-fast); font-family: inherit;
}
.form-input:focus { outline: none; border-color: var(--color-primary); }
.modal-actions { display: flex; gap: 12px; margin-top: var(--spacing-sm); }
.btn-cancel {
  flex: 1; padding: 13px; border: 1.5px solid var(--color-border);
  border-radius: var(--radius-md); font-weight: 600; color: var(--color-text-secondary);
}
.btn-cancel:hover { border-color: var(--color-danger); color: var(--color-danger); }
.btn-confirm {
  flex: 2; padding: 13px; background: var(--color-primary); color: #fff;
  border-radius: var(--radius-md); font-weight: 700;
  transition: background var(--transition-fast);
}
.btn-confirm:hover { background: var(--color-primary-hover); }
</style>
