<template>
  <div class="customer-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Direktori Pelanggan</h1>
        <p class="page-subtitle">Kelola saldo piutang dan profil pelanggan.</p>
      </div>
      <button class="btn-add" @click="showAdd = true">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Tambah Pelanggan Baru
      </button>
    </div>

    <!-- Summary Cards -->
    <div class="cust-stats">
      <div class="cust-stat-card left-accent orange">
        <p class="cs-label">TOTAL PIUTANG (BON)</p>
        <p class="cs-val">Rp {{ formatNum(totalPiutang) }}</p>
        <p class="cs-trend up">↗ Update Otomatis</p>
      </div>
      <div class="cust-stat-card left-accent blue">
        <p class="cs-label">TOTAL PELANGGAN</p>
        <p class="cs-val">{{ formatNum(totalPelanggan) }}</p>
        <p class="cs-trend ok">✅ Data Tersinkron</p>
      </div>
      <div class="cust-stat-card left-accent yellow">
        <p class="cs-label">PEMBAYARAN TERTUNDA</p>
        <p class="cs-val">{{ formatNum(pembayaranTertunda) }}</p>
        <p class="cs-trend warn">⏰ Perlu ditagih</p>
      </div>
    </div>

    <!-- Search & Filter -->
    <div class="toolbar">
      <div class="search-box">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input v-model="search" type="text" placeholder="Cari pelanggan berdasarkan nama, telepon, atau status bon..." />
      </div>
      <select v-model="filterStatus" class="select-filter">
        <option value="">Semua Status</option>
        <option value="lunas">Lunas</option>
        <option value="tertunda">Tertunda</option>
        <option value="jatuh_tempo">Jatuh Tempo</option>
      </select>
    </div>

    <!-- Customer Table -->
    <div class="table-card">
      <div class="table-head-row">
        <span class="col-customer">NAMA PELANGGAN</span>
        <span>NOMOR TELEPON</span>
        <span>STATUS BON / STATUS HUTANG</span>
        <span>AKSI</span>
      </div>

      <div v-for="c in filteredCustomers" :key="c.id" class="table-data-row">
        <div class="col-customer customer-info">
          <div class="avatar" :style="{ background: c.avatarColor }">{{ c.initials }}</div>
          <div>
            <p class="cust-name">{{ c.name }}</p>
            <p class="cust-tier">{{ c.tier }}</p>
          </div>
        </div>
        <span class="phone-text">{{ c.phone }}</span>
        <div class="debt-status" :class="c.status">
          <span v-if="c.debt > 0">
            {{ c.status === 'tertunda' ? '⚠' : '❗' }}
            Rp {{ formatNum(c.debt) }}
            ({{ statusLabel(c.status) }})
          </span>
          <span v-else>● Rp 0.00 (Lunas)</span>
        </div>
        <div class="action-btns">
          <button class="act-btn view" @click="viewCustomer(c)" title="Detail / Edit">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
          <button class="act-btn pay" v-if="c.debt > 0" @click="payDebt(c)" title="Bayar Hutang">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
          </button>
          <button 
            class="act-btn delete" 
            :disabled="c.debt > 0"
            @click="handleDelete(c)" 
            :title="c.debt > 0 ? 'Tidak bisa dihapus karena masih ada hutang' : 'Hapus Pelanggan'"
          >
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <polyline points="3 6 5 6 21 6"></polyline>
              <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
              <line x1="10" y1="11" x2="10" y2="17"></line>
              <line x1="14" y1="11" x2="14" y2="17"></line>
            </svg>
          </button>
        </div>
      </div>

      <div class="table-footer">
        Menampilkan {{ filteredCustomers.length }} dari {{ customers.length }} pelanggan
      </div>
    </div>

    <!-- Add Customer Modal -->
    <div v-if="showAdd" class="modal-overlay" @click.self="showAdd = false">
      <div class="modal-box">
        <div class="modal-header">
          <h2 class="modal-title">Tambah Pelanggan Baru</h2>
          <button @click="showAdd = false" class="close-btn">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div class="add-form">
          <div class="form-group">
            <label>Nama Lengkap</label>
            <input v-model="newCust.name" class="form-input" type="text" placeholder="Masukkan nama pelanggan" />
          </div>
          <div class="form-group">
            <label>Nomor Telepon</label>
            <input v-model="newCust.phone" class="form-input" type="tel" placeholder="08xx-xxxx-xxxx" />
          </div>
          <div class="form-group">
            <label>Alamat (Opsional)</label>
            <textarea v-model="newCust.address" class="form-textarea" placeholder="Alamat lengkap..."></textarea>
          </div>
          <div class="form-group">
            <label>Tier Pelanggan</label>
            <select v-model="newCust.tier" class="form-input">
              <option>New Customer</option>
              <option>Regular Customer</option>
              <option>Premium Tier Customer</option>
              <option>Regular Wholesaler</option>
              <option>High Volume Buyer</option>
            </select>
          </div>
          <div class="form-group">
            <label>Batas Hutang (Limit Kredit)</label>
            <input v-model.number="newCust.debt_limit" class="form-input" type="number" placeholder="5000000" />
          </div>
          <div class="modal-actions">
            <button class="btn-cancel" @click="showAdd = false">Batal</button>
            <button class="btn-confirm" @click="saveCustomer">Simpan Pelanggan</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Customer Modal -->
    <div v-if="showEdit" class="modal-overlay" @click.self="showEdit = false">
      <div class="modal-box">
        <div class="modal-header">
          <h2 class="modal-title">Edit Profil Pelanggan</h2>
          <button @click="showEdit = false" class="close-btn">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div class="add-form">
          <div class="form-group">
            <label>Nama Lengkap</label>
            <input v-model="editCust.name" class="form-input" type="text" placeholder="Masukkan nama pelanggan" />
          </div>
          <div class="form-group">
            <label>Nomor Telepon</label>
            <input v-model="editCust.phone" class="form-input" type="tel" placeholder="08xx-xxxx-xxxx" />
          </div>
          <div class="form-group">
            <label>Alamat (Opsional)</label>
            <textarea v-model="editCust.address" class="form-textarea" placeholder="Alamat lengkap..."></textarea>
          </div>
          <div class="form-group">
            <label>Tier Pelanggan</label>
            <select v-model="editCust.tier" class="form-input">
              <option>New Customer</option>
              <option>Regular Customer</option>
              <option>Premium Tier Customer</option>
              <option>Regular Wholesaler</option>
              <option>High Volume Buyer</option>
            </select>
          </div>
          <div class="form-group">
            <label>Batas Hutang (Limit Kredit)</label>
            <input v-model.number="editCust.debt_limit" class="form-input" type="number" placeholder="5000000" />
          </div>
          <div class="modal-actions">
            <button class="btn-cancel" @click="showEdit = false">Batal</button>
            <button class="btn-confirm" @click="saveEditCustomer">Simpan Perubahan</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Status bar -->
    <div class="status-bar">
      <span class="status-dot"></span>
      SYSTEMS ONLINE • LOCAL SYNC COMPLETE
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useCustomerStore } from '@/stores/customer'

const customerStore = useCustomerStore()
const router = useRouter()

const search = ref('')
const filterStatus = ref('')
const showAdd = ref(false)
const showEdit = ref(false)
const editCust = ref({ id: null, name: '', phone: '', address: '', tier: 'New Customer', debt_limit: 5000000 })

onMounted(async () => {
  await customerStore.fetchCustomers()
})

const customers = computed(() => customerStore.customers.map(c => ({
  ...c,
  initials: c.name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase(),
  avatarColor: stringToColor(c.name),
  status: c.current_debt > 0 ? 'tertunda' : 'lunas', // Simple logic for now
  debt: c.current_debt,
  tier: c.tier || 'Regular Customer'
})))

const newCust = ref({ name: '', phone: '', address: '', tier: 'New Customer', debt_limit: 5000000 })

const totalPiutang = computed(() => customers.value.reduce((s, c) => s + (c.debt || 0), 0))
const totalPelanggan = computed(() => customers.value.length)
const pembayaranTertunda = computed(() => customers.value.filter(c => c.debt > 0).length)

const filteredCustomers = computed(() => {
  return customers.value.filter(c => {
    const matchSearch = !search.value || c.name.toLowerCase().includes(search.value.toLowerCase()) || (c.phone || '').includes(search.value)
    const matchStatus = !filterStatus.value || c.status === filterStatus.value
    return matchSearch && matchStatus
  })
})

function statusLabel(s) {
  return { lunas: 'Lunas', tertunda: 'Tertunda', jatuh_tempo: 'Jatuh Tempo' }[s] || s
}

function stringToColor(str) {
  let hash = 0
  for (let i = 0; i < str.length; i++) {
    hash = str.charCodeAt(i) + ((hash << 5) - hash)
  }
  const colors = ['#1E40AF', '#374151', '#059669', '#DC2626', '#7C3AED', '#B45309', '#6366F1']
  return colors[Math.abs(hash) % colors.length]
}

function viewCustomer(c) {
  editCust.value = { 
    id: c.id, 
    name: c.name, 
    phone: c.phone, 
    address: c.address, 
    tier: c.tier, 
    debt_limit: c.debt_limit 
  }
  showEdit.value = true
}

function payDebt(c) {
  router.push(`/pelanggan/${c.id}/bayar`)
}

async function saveEditCustomer() {
  try {
    await customerStore.updateCustomer(editCust.value.id, {
      name: editCust.value.name,
      phone: editCust.value.phone,
      address: editCust.value.address,
      tier: editCust.value.tier,
      debt_limit: editCust.value.debt_limit
    })
    showEdit.value = false
    alert('Profil pelanggan berhasil diperbarui!')
  } catch (err) {
    alert(err)
  }
}

async function handleDelete(c) {
  if (c.debt > 0) {
    alert('Pelanggan tidak bisa dihapus karena masih memiliki hutang.')
    return
  }
  if (confirm(`Apakah Anda yakin ingin menghapus pelanggan ${c.name}?`)) {
    try {
      await customerStore.deleteCustomer(c.id)
      alert('Pelanggan berhasil dihapus!')
    } catch (err) {
      alert(err)
    }
  }
}

async function saveCustomer() {
  try {
    await customerStore.addCustomer({
      name: newCust.value.name,
      phone: newCust.value.phone,
      address: newCust.value.address,
      tier: newCust.value.tier,
      debt_limit: newCust.value.debt_limit
    })
    showAdd.value = false
    newCust.value = { name: '', phone: '', address: '', tier: 'New Customer', debt_limit: 5000000 }
  } catch (err) {
    alert(err)
  }
}

function formatNum(n) { return (n || 0).toLocaleString('id-ID') }
</script>

<style scoped>
.customer-page { max-width: 1100px; }
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
.cust-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--spacing-md); margin-bottom: var(--spacing-xl); }
.cust-stat-card {
  background: var(--color-card-bg);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-lg);
  padding: var(--spacing-lg) var(--spacing-xl);
  border-left: 5px solid;
}
.cust-stat-card.left-accent.orange { border-left-color: var(--color-warning); }
.cust-stat-card.left-accent.blue   { border-left-color: var(--color-secondary); }
.cust-stat-card.left-accent.yellow { border-left-color: #F59E0B; }

.cs-label { font-size: var(--font-xs); font-weight: 700; color: var(--color-text-muted); letter-spacing: 0.5px; margin-bottom: 8px; }
.cs-val   { font-size: var(--font-3xl); font-weight: 800; margin-bottom: 6px; }
.cs-trend { font-size: var(--font-sm); font-weight: 600; }
.cs-trend.up   { color: var(--color-primary); }
.cs-trend.ok   { color: var(--color-primary); }
.cs-trend.warn { color: var(--color-warning); }

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

.customer-info { display: flex; align-items: center; gap: 12px; }
.avatar {
  width: 40px; height: 40px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-weight: 700; font-size: var(--font-base);
  flex-shrink: 0;
}
.cust-name { font-size: var(--font-base); font-weight: 700; }
.cust-tier { font-size: var(--font-xs); color: var(--color-text-muted); }
.phone-text { font-size: var(--font-sm); color: var(--color-text-secondary); }

.debt-status {
  display: inline-flex; align-items: center; gap: 4px;
  padding: 6px 12px; border-radius: var(--radius-full);
  font-size: var(--font-xs); font-weight: 700;
}
.debt-status.lunas     { background: #F1F5F9; color: var(--color-text-secondary); }
.debt-status.tertunda  { background: var(--color-warning-light); color: var(--color-warning); }
.debt-status.jatuh_tempo { background: var(--color-danger-light); color: var(--color-danger); }

.action-btns { display: flex; gap: 6px; }
.act-btn {
  width: 30px; height: 30px; border-radius: var(--radius-sm);
  display: flex; align-items: center; justify-content: center;
  transition: all var(--transition-fast);
}
.act-btn.view { border: 1px solid var(--color-border); color: var(--color-text-secondary); }
.act-btn.view:hover { border-color: var(--color-secondary); color: var(--color-secondary); }
.act-btn.pay { border: 1px solid var(--color-primary-light); color: var(--color-primary); background: var(--color-primary-light); }
.act-btn.pay:hover { background: var(--color-primary); color: #fff; }
.act-btn.delete { border: 1px solid var(--color-danger-light); color: var(--color-danger); background: var(--color-danger-light); }
.act-btn.delete:hover:not(:disabled) { background: var(--color-danger); color: #fff; }
.act-btn.delete:disabled { opacity: 0.4; cursor: not-allowed; border-color: var(--color-border); color: var(--color-text-muted); background: var(--color-bg-page); }

.table-footer {
  display: flex; align-items: center; justify-content: space-between;
  padding: 14px 20px; font-size: var(--font-sm); color: var(--color-text-muted);
  border-top: 1px solid var(--color-border); background: #F8FAFC;
}
.pagination { display: flex; gap: 6px; }
.page-btn {
  width: 32px; height: 32px; border: 1px solid var(--color-border);
  border-radius: var(--radius-sm); font-size: var(--font-sm); font-weight: 600;
  color: var(--color-text-secondary); display: flex; align-items: center; justify-content: center;
  transition: all var(--transition-fast);
}
.page-btn:hover { border-color: var(--color-primary); color: var(--color-primary); }
.page-btn.active { background: var(--color-sidebar-bg); color: #fff; border-color: var(--color-sidebar-bg); }

.status-bar {
  display: flex; align-items: center; gap: 8px;
  font-size: var(--font-xs); font-weight: 700; color: var(--color-primary);
  margin-top: var(--spacing-lg);
}
.status-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--color-primary); }

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
.close-btn { color: var(--color-text-muted); padding: 6px; border-radius: var(--radius-sm); }
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
.form-textarea {
  padding: 11px 14px; border: 1.5px solid var(--color-border);
  border-radius: var(--radius-md); font-size: var(--font-base);
  height: 80px; resize: none; font-family: inherit;
}
.form-textarea:focus { outline: none; border-color: var(--color-primary); }
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
