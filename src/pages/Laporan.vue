<template>
  <div class="laporan-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Laporan Keuangan</h1>
        <p class="page-subtitle">Audit performa detail untuk operasional Terminal Toko Sembako</p>
      </div>
      <div class="header-actions">
        <button class="btn-secondary">
          <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
          Kembali
        </button>
        <button class="btn-secondary" @click="showDetailedReportModal = true">
          <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 20v-6M6 20V10M18 20V4"/></svg>
          Detail Penjualan
        </button>
        <button class="btn-secondary" @click="saveDailyReport">
          <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          Simpan Laporan Harian
        </button>
        <button class="btn-export">
          <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          Export Excel
        </button>
      </div>
    </div>

    <!-- Financial Summary Cards -->
    <div class="fin-stats">
      <div class="fin-card income">
        <div class="fin-card-top">
          <p class="fin-label">Total Pemasukan</p>
          <span class="fin-icon">↗</span>
        </div>
        <p class="fin-val">Rp {{ formatNum(stats.total_revenue) }}</p>
        <p class="fin-trend up">Periode Terpilih</p>
      </div>
      <div class="fin-card expense">
        <div class="fin-card-top">
          <p class="fin-label">Total Pengeluaran (HPP)</p>
          <span class="fin-icon red">↘</span>
        </div>
        <p class="fin-val" v-if="isProfitUnlocked">Rp {{ formatNum(stats.total_cost) }}</p>
        <p class="fin-val locked" v-else>🔒 Terkunci</p>
        <p class="fin-trend down">Periode Terpilih</p>
      </div>
      <div class="fin-card profit">
        <div class="fin-card-top">
          <p class="fin-label">Laba Bersih</p>
          <span class="fin-icon green">💰</span>
        </div>
        <p class="fin-val" v-if="isProfitUnlocked">Rp {{ formatNum(stats.total_profit) }}</p>
        <div v-else class="locked-profit">
          <p class="fin-val locked">🔒 Terkunci</p>
          <button class="btn-unlock" @click="openPasswordModal">Buka Akses</button>
        </div>
        <p class="fin-trend safe" v-if="isProfitUnlocked">✅ Keuntungan Aman</p>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
      <div class="filter-item">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        <input type="date" v-model="startDate" class="filter-select" @change="fetchData" />
        <span>-</span>
        <input type="date" v-model="endDate" class="filter-select" @change="fetchData" />
      </div>
      <div class="filter-item">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
        <select v-model="methodFilter" class="filter-select">
          <option value="">Metode Pembayaran</option>
          <option value="qris">QRIS</option>
          <option value="tunai">Tunai</option>
          <option value="kasbon">Kasbon</option>
        </select>
      </div>
      <div class="search-inline">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input v-model="txSearch" type="text" placeholder="Cari ID Transaksi..." />
      </div>
    </div>

    <!-- Transaction Table -->
    <div class="table-card">
      <div class="table-head-row">
        <span>TANGGAL & WAKTU</span>
        <span>ID TRANSAKSI</span>
        <span>KETERANGAN</span>
        <span>METODE</span>
        <span class="text-right">NOMINAL</span>
        <span class="text-center">AKSI</span>
      </div>

      <div v-for="tx in filteredTransactions" :key="tx.id" class="table-data-row">
        <div class="date-col">
          <p class="tx-date">{{ formatDate(tx.transaction_date) }}</p>
          <p class="tx-time">{{ formatTime(tx.transaction_date) }} WIB</p>
        </div>
        <span class="tx-id">#TXN-{{ tx.id }}</span>
        <div class="tx-desc">
          <p class="tx-desc-main">{{ tx.payment_method === 'debt' ? 'Kasbon' : 'Penjualan' }}</p>
          <p class="tx-desc-sub">{{ tx.customer?.name || 'Customer Umum' }}</p>
        </div>
        <span class="method-badge" :class="tx.payment_method">{{ tx.payment_method.toUpperCase() }}</span>
        <span class="nominal" :class="tx.total_amount > 0 ? 'income' : 'expense'">
          + Rp {{ formatNum(Math.abs(tx.total_amount)) }}
        </span>
        <div class="action-btns text-center">
          <button class="act-btn detail" @click="showDetail(tx)" title="Detail & Retur">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
          </button>
        </div>
      </div>

      <div class="table-footer">
        <span>Menampilkan {{ filteredTransactions.length }} dari {{ transactions.length }} transaksi.</span>
        <div class="pagination">
          <button class="page-btn">‹</button>
          <button class="page-btn active">1</button>
          <button class="page-btn">›</button>
        </div>
      </div>
    </div>

    <!-- Password Modal -->
    <div v-if="showPasswordModal" class="modal-overlay" @click.self="showPasswordModal = false">
      <div class="modal-card">
        <div class="modal-header">
          <h3>Otorisasi Laporan Profit</h3>
          <button class="close-btn" @click="showPasswordModal = false">×</button>
        </div>
        <div class="modal-body">
          <p class="modal-desc">Masukkan password Anda untuk melihat Laba Bersih & HPP.</p>
          <input type="password" v-model="adminPassword" placeholder="Password..." class="form-input" @keyup.enter="submitPassword" />
          <p v-if="passwordError" class="error-msg">{{ passwordError }}</p>
        </div>
        <div class="modal-footer">
          <button class="btn-secondary" @click="showPasswordModal = false">Batal</button>
          <button class="btn-primary" @click="submitPassword">Buka Akses</button>
        </div>
      </div>
    </div>
    <!-- Detail / Retur Modal -->
    <div v-if="showDetailModal" class="modal-overlay" @click.self="showDetailModal = false">
      <div class="modal-card detail-modal" style="width: 550px; max-width: 95vw;">
        <div class="modal-header">
          <h3>Detail Transaksi #TXN-{{ selectedTx?.id }}</h3>
          <button class="close-btn" @click="showDetailModal = false">×</button>
        </div>
        <div class="modal-body">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <p class="modal-desc" style="margin: 0;">Pelanggan: {{ selectedTx?.customer?.name || 'Umum' }} | Waktu: {{ formatDate(selectedTx?.transaction_date) }} {{ formatTime(selectedTx?.transaction_date) }}</p>
            <button class="btn-primary" @click="reprintReceipt(selectedTx?.id)" style="padding: 6px 14px; font-size: 13px; display: flex; align-items: center; gap: 6px; background: #059669; border: none; color: #fff; border-radius: 8px; font-weight: 700; cursor: pointer;">
              <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
              Cetak Struk
            </button>
          </div>
          <div class="table-card">
            <div class="table-head-row item-row">
              <span>PRODUK</span>
              <span>QTY</span>
              <span>SUBTOTAL</span>
              <span class="text-center">AKSI</span>
            </div>
            <div v-for="item in selectedTx?.items" :key="item.id" class="table-data-row item-row">
              <span class="prod-name">{{ item.product?.name }}</span>
              <span>{{ item.qty }}</span>
              <span>Rp {{ formatNum(item.subtotal) }}</span>
              <div class="text-center">
                <button class="btn-secondary retur-btn" @click="openReturModal(item)" title="Retur Barang ini">Retur</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Retur Modal -->
    <div v-if="showReturModal" class="modal-overlay" @click.self="showReturModal = false">
      <div class="modal-card">
        <div class="modal-header">
          <h3>Retur Barang: {{ selectedItem?.product?.name }}</h3>
          <button class="close-btn" @click="showReturModal = false">×</button>
        </div>
        <div class="modal-body">
          <p class="modal-desc">Maksimal retur: {{ selectedItem?.qty }}</p>
          <div class="form-group mb-3">
            <label>Kuantitas Retur</label>
            <input type="number" v-model.number="returForm.qty" :max="selectedItem?.qty" min="1" class="form-input" />
          </div>
          <div class="form-group mb-3">
            <label>Alasan Retur</label>
            <textarea v-model="returForm.reason" class="form-input" rows="3" placeholder="Barang rusak, dsb"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-secondary" @click="showReturModal = false">Batal</button>
          <button class="btn-primary" @click="submitRetur">Proses Retur</button>
        </div>
      </div>
    </div>

    <!-- Detailed Report Modal -->
    <div v-if="showDetailedReportModal" class="modal-overlay" @click.self="showDetailedReportModal = false">
      <div class="modal-card" style="width: 600px;">
        <div class="modal-header">
          <h3>Detail Penjualan ({{ startDate || 'Awal' }} - {{ endDate || 'Akhir' }})</h3>
          <button class="close-btn" @click="showDetailedReportModal = false">×</button>
        </div>
        <div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
          <div v-if="detailedReport">
            <h4 style="margin-bottom: 10px;">Penjualan per Produk</h4>
            <div class="table-card mb-3">
              <div class="table-head-row" style="grid-template-columns: 2fr 1fr 2fr;">
                <span>PRODUK</span>
                <span>QTY TERJUAL</span>
                <span>TOTAL OMSET</span>
              </div>
              <div v-for="(item, idx) in detailedReport.sales_by_product" :key="idx" class="table-data-row" style="grid-template-columns: 2fr 1fr 2fr; padding: 10px 20px;">
                <span class="prod-name">{{ item.name }}</span>
                <span>{{ item.total_qty }}</span>
                <span>Rp {{ formatNum(item.total_revenue) }}</span>
              </div>
            </div>

            <h4 style="margin-bottom: 10px; margin-top: 20px;">Penjualan per Kategori</h4>
            <div class="table-card mb-3">
              <div class="table-head-row" style="grid-template-columns: 2fr 2fr;">
                <span>KATEGORI</span>
                <span>TOTAL OMSET</span>
              </div>
              <div v-for="(item, idx) in detailedReport.sales_by_category" :key="'cat'+idx" class="table-data-row" style="grid-template-columns: 2fr 2fr; padding: 10px 20px;">
                <span class="prod-name">{{ item.category_name }}</span>
                <span>Rp {{ formatNum(item.total_revenue) }}</span>
              </div>
            </div>
            
            <h4 style="margin-bottom: 10px; margin-top: 20px;">Berdasarkan Metode Pembayaran</h4>
            <div class="table-card">
              <div class="table-head-row" style="grid-template-columns: 1fr 1fr 2fr;">
                <span>METODE</span>
                <span>JML TRX</span>
                <span>TOTAL OMSET</span>
              </div>
              <div v-for="(item, idx) in detailedReport.sales_by_payment" :key="'pay'+idx" class="table-data-row" style="grid-template-columns: 1fr 1fr 2fr; padding: 10px 20px;">
                <span class="method-badge" :class="item.payment_method">{{ item.payment_method.toUpperCase() }}</span>
                <span>{{ item.transaction_count }}</span>
                <span>Rp {{ formatNum(item.total_revenue) }}</span>
              </div>
            </div>
          </div>
          <div v-else>
            <p>Memuat data...</p>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-secondary" @click="showDetailedReportModal = false">Tutup</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import axios from '@/plugins/axios'

const startDate = ref('')
const endDate = ref('')
const methodFilter = ref('')
const txSearch = ref('')

const transactions = ref([])
const stats = ref({
  total_revenue: 0,
  total_cost: 0,
  total_profit: 0
})

const isProfitUnlocked = ref(false)
const showPasswordModal = ref(false)
const adminPassword = ref('')
const savedPassword = ref('')
const passwordError = ref('')

const showDetailModal = ref(false)
const selectedTx = ref(null)

const showReturModal = ref(false)
const selectedItem = ref(null)
const returForm = ref({ qty: 1, reason: '' })

const showDetailedReportModal = ref(false)
const detailedReport = ref(null)

const isLoading = ref(true)

const fetchData = async () => {
  await fetchTransactions()
  await fetchProfitReport()
  if (isProfitUnlocked.value && savedPassword.value) {
    try {
      const params = { password: savedPassword.value }
      if (startDate.value) params.start_date = startDate.value
      if (endDate.value) params.end_date = endDate.value
      const res = await axios.post('/reports/profit', params)
      stats.value.total_revenue = res.data.total_revenue
      stats.value.total_cost = res.data.total_cost
      stats.value.total_profit = res.data.total_profit
    } catch (e) {
      console.error(e)
    }
  }
}

const fetchTransactions = async () => {
  try {
    isLoading.value = true
    const params = {}
    if (startDate.value) params.start_date = startDate.value
    if (endDate.value) params.end_date = endDate.value

    const response = await axios.get('/transactions', { params })
    transactions.value = response.data
  } catch (error) {
    console.error('Error fetching transactions:', error)
  } finally {
    isLoading.value = false
  }
}

const showDetail = (tx) => {
  selectedTx.value = tx
  showDetailModal.value = true
}

const reprintReceipt = (id) => {
  if (!id) return
  window.open(`/print-receipt/${id}`, '_blank')
}

const openReturModal = (item) => {
  selectedItem.value = item
  returForm.value = { qty: 1, reason: '' }
  showReturModal.value = true
}

const submitRetur = async () => {
  try {
    if (!returForm.value.reason) {
      alert("Alasan retur harus diisi")
      return
    }
    await axios.post('/returns', {
      transaction_item_id: selectedItem.value.id,
      qty: returForm.value.qty,
      reason: returForm.value.reason
    })
    
    alert('Retur berhasil diproses.')
    showReturModal.value = false
    showDetailModal.value = false
    await fetchTransactions() // Refresh data
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal memproses retur')
  }
}

const fetchProfitReport = async () => {
  try {
    const params = {}
    if (startDate.value) params.start_date = startDate.value
    if (endDate.value) params.end_date = endDate.value
    const res = await axios.post('/reports/detailed', params)
    // Populate total revenue from the detailed report (safe read — non-profit data visible to all)
    if (res.data) {
      stats.value.total_revenue = res.data.total_revenue || 0
      // cost & profit remain locked until password unlocked
    }
  } catch (error) {
    console.error('Error fetching stats:', error)
  }
}

const fetchDetailedReport = async () => {
  try {
    const params = {}
    if (startDate.value) params.start_date = startDate.value
    if (endDate.value) params.end_date = endDate.value
    const res = await axios.post('/reports/detailed', params)
    detailedReport.value = res.data
  } catch (error) {
    console.error('Error fetching detailed report', error)
  }
}

watch(showDetailedReportModal, (newVal) => {
  if (newVal) {
    fetchDetailedReport()
  }
})

const saveDailyReport = async () => {
  try {
    const dateToSave = startDate.value || new Date().toISOString().split('T')[0]
    const genRes = await axios.get('/financial-reports/generate', { params: { date: dateToSave } })
    
    await axios.post('/financial-reports', genRes.data)
    alert(`Laporan harian tanggal ${dateToSave} berhasil disimpan secara permanen.`)
  } catch (error) {
    alert(error.response?.data?.message || 'Gagal menyimpan laporan harian. Mungkin sudah disimpan untuk tanggal ini.')
  }
}

const openPasswordModal = () => {
  showPasswordModal.value = true
  passwordError.value = ''
  adminPassword.value = ''
}

const submitPassword = async () => {
  try {
    passwordError.value = ''
    const params = { password: adminPassword.value }
    if (startDate.value) params.start_date = startDate.value
    if (endDate.value) params.end_date = endDate.value
    
    const res = await axios.post('/reports/profit', params)
    
    stats.value.total_revenue = res.data.total_revenue
    stats.value.total_cost = res.data.total_cost
    stats.value.total_profit = res.data.total_profit
    
    savedPassword.value = adminPassword.value
    isProfitUnlocked.value = true
    showPasswordModal.value = false
  } catch (error) {
    passwordError.value = error.response?.data?.message || 'Password salah atau terjadi kesalahan.'
    isProfitUnlocked.value = false
    savedPassword.value = ''
  }
}

const filteredTransactions = computed(() => {
  return transactions.value.filter(tx => {
    const matchMethod = !methodFilter.value || tx.payment_method === methodFilter.value
    const matchSearch = !txSearch.value || tx.id.toString().includes(txSearch.value)
    return matchMethod && matchSearch
  })
})

function formatNum(n) { return (n || 0).toLocaleString('id-ID') }

function formatDate(dateString) {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}

function formatTime(dateString) {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
}

onMounted(() => {
  fetchTransactions()
  fetchProfitReport()
})
</script>

<style scoped>
.laporan-page { max-width: 1100px; }
.page-header {
  display: flex; align-items: flex-start; justify-content: space-between;
  margin-bottom: var(--spacing-xl);
}
.page-title    { font-size: var(--font-3xl); font-weight: 700; }
.page-subtitle { font-size: var(--font-sm); color: var(--color-text-muted); margin-top: 4px; }
.header-actions { display: flex; gap: 10px; align-items: center; }
.btn-secondary {
  display: flex; align-items: center; gap: 6px;
  padding: 10px 16px; border: 1.5px solid var(--color-border);
  border-radius: var(--radius-md); font-size: var(--font-sm); font-weight: 600;
  color: var(--color-text-secondary); transition: all var(--transition-fast);
}
.btn-secondary:hover { border-color: var(--color-secondary); color: var(--color-secondary); }
.btn-export {
  display: flex; align-items: center; gap: 6px;
  padding: 10px 18px; background: var(--color-sidebar-bg); color: #fff;
  border-radius: var(--radius-md); font-size: var(--font-sm); font-weight: 700;
  transition: background var(--transition-fast);
}
.btn-export:hover { background: #1E293B; }

/* Financial Cards */
.fin-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--spacing-md); margin-bottom: var(--spacing-xl); }
.fin-card {
  background: var(--color-card-bg);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-lg);
  padding: var(--spacing-xl);
  border-top: 4px solid;
}
.fin-card.income { border-top-color: var(--color-primary); }
.fin-card.expense { border-top-color: var(--color-danger); }
.fin-card.profit  { border-top-color: var(--color-secondary); }

.fin-card-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--spacing-md); }
.fin-label { font-size: var(--font-sm); font-weight: 700; color: var(--color-text-secondary); }
.fin-icon { font-size: var(--font-xl); }
.fin-icon.red   { color: var(--color-danger); }
.fin-icon.green { color: var(--color-primary); }
.fin-val { font-size: var(--font-2xl); font-weight: 800; color: var(--color-text-primary); margin-bottom: 8px; }
.fin-trend { font-size: var(--font-sm); font-weight: 600; }
.fin-trend.up   { color: var(--color-primary); }
.fin-trend.down { color: var(--color-danger); }
.fin-trend.safe { color: var(--color-primary); }

/* Filter Bar */
.filter-bar {
  display: flex; gap: var(--spacing-md); align-items: center;
  background: var(--color-card-bg); border: 1px solid var(--color-border);
  border-radius: var(--radius-md); padding: 14px 20px;
  margin-bottom: var(--spacing-lg);
}
.filter-item {
  display: flex; align-items: center; gap: 8px;
  color: var(--color-text-muted); border-right: 1px solid var(--color-border);
  padding-right: var(--spacing-md);
}
.filter-select {
  border: none; font-size: var(--font-sm); font-weight: 600;
  color: var(--color-text-primary); background: transparent; cursor: pointer;
}
.filter-select:focus { outline: none; }
.search-inline {
  flex: 1; display: flex; align-items: center; gap: 8px;
  color: var(--color-text-muted); margin-left: var(--spacing-sm);
}
.search-inline input { flex: 1; border: none; font-size: var(--font-base); color: var(--color-text-primary); outline: none; background: transparent; }

/* Table */
.table-card { background: var(--color-card-bg); border: 1px solid var(--color-border); border-radius: var(--radius-lg); overflow: hidden; }
.table-head-row {
  display: grid; grid-template-columns: 1.5fr 1.2fr 2fr 1fr 1.2fr 1fr;
  padding: 12px 20px; background: #F8FAFC;
  border-bottom: 1px solid var(--color-border);
  font-size: var(--font-xs); font-weight: 700; color: var(--color-text-muted); letter-spacing: 0.5px;
}
.table-data-row {
  display: grid; grid-template-columns: 1.5fr 1.2fr 2fr 1fr 1.2fr 1fr;
  align-items: center; padding: 16px 20px;
  border-bottom: 1px solid var(--color-border);
  transition: background var(--transition-fast);
}
.table-data-row:hover { background: #F8FAFC; }

.date-col {}
.tx-date { font-size: var(--font-base); font-weight: 700; }
.tx-time { font-size: var(--font-xs); color: var(--color-text-muted); }
.tx-id   { font-size: var(--font-sm); font-family: monospace; color: var(--color-text-secondary); }
.tx-desc-main { font-size: var(--font-base); font-weight: 600; }
.tx-desc-sub  { font-size: var(--font-xs); color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.3px; }

.method-badge {
  display: inline-block; padding: 4px 12px;
  border-radius: var(--radius-full);
  font-size: var(--font-xs); font-weight: 700; letter-spacing: 0.5px;
  text-align: center;
}
.method-badge.qris   { background: var(--color-primary-light); color: var(--color-primary); }
.method-badge.tunai  { background: #F1F5F9; color: var(--color-text-secondary); }
.method-badge.kasbon { background: var(--color-warning-light); color: var(--color-warning); }

.nominal { font-size: var(--font-base); font-weight: 700; text-align: right; }
.nominal.income  { color: var(--color-primary); }
.nominal.expense { color: var(--color-danger); }
.text-right { text-align: right; }

.table-footer {
  display: flex; align-items: center; justify-content: space-between;
  padding: 14px 20px; font-size: var(--font-sm); color: var(--color-text-muted);
  border-top: 1px solid var(--color-border); background: #F8FAFC;
  font-style: italic;
}
.pagination { display: flex; gap: 6px; font-style: normal; }
.page-btn {
  width: 32px; height: 32px; border: 1px solid var(--color-border);
  border-radius: var(--radius-sm); font-size: var(--font-sm); font-weight: 600;
  color: var(--color-text-secondary); display: flex; align-items: center; justify-content: center;
  transition: all var(--transition-fast);
}
.page-btn:hover { border-color: var(--color-primary); color: var(--color-primary); }
.page-btn.active { background: var(--color-sidebar-bg); color: #fff; border-color: var(--color-sidebar-bg); }

/* Locked States */
.locked { color: var(--color-text-muted) !important; font-size: var(--font-lg) !important; margin-bottom: 8px; }
.locked-profit { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 8px; }
.btn-unlock { background: var(--color-sidebar-bg); color: #fff; border: none; border-radius: var(--radius-md); padding: 6px 12px; font-size: var(--font-sm); font-weight: 600; cursor: pointer; transition: background var(--transition-fast); }
.btn-unlock:hover { background: #1E293B; }

/* Modal Styles */
.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal-card { background: var(--color-card-bg); border-radius: var(--radius-lg); width: 400px; max-width: 90vw; padding: var(--spacing-xl); box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
.modal-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--spacing-lg); }
.modal-header h3 { font-size: var(--font-xl); font-weight: 700; color: var(--color-text-primary); }
.close-btn { background: transparent; border: none; font-size: 24px; color: var(--color-text-muted); cursor: pointer; }
.modal-body { margin-bottom: var(--spacing-xl); }
.modal-desc { font-size: var(--font-sm); color: var(--color-text-secondary); margin-bottom: var(--spacing-md); }
.form-input { width: 100%; padding: 10px 14px; border: 1.5px solid var(--color-border); border-radius: var(--radius-md); font-size: var(--font-base); outline: none; transition: border-color var(--transition-fast); box-sizing: border-box;}
.form-input:focus { border-color: var(--color-primary); }
.error-msg { color: var(--color-danger); font-size: var(--font-sm); margin-top: 8px; font-weight: 500; }
.modal-footer { display: flex; justify-content: flex-end; gap: 10px; }
.btn-primary { background: var(--color-primary); color: #fff; border: none; padding: 10px 20px; border-radius: var(--radius-md); font-weight: 600; cursor: pointer; transition: background var(--transition-fast); }
.btn-primary:hover { background: #047857; }
</style>
