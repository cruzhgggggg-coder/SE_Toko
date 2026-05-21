<template>
  <div class="retur-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Retur Barang</h1>
        <p class="page-subtitle">Pencatatan dan pengelolaan pengembalian sembako rusak atau salah kirim.</p>
      </div>
      <button class="btn-add" @click="openAddModal">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Catat Retur Baru
      </button>
    </div>

    <!-- Stats Summary Cards -->
    <div class="retur-stats">
      <div class="retur-stat-card left-accent emerald">
        <p class="cs-label">TOTAL BARANG DIRETUR</p>
        <p class="cs-val">{{ totalReturnedQty }} Pcs</p>
        <p class="cs-trend ok">🔄 Stok Dikembalikan</p>
      </div>
      <div class="retur-stat-card left-accent blue">
        <p class="cs-label">KASUS RETUR SELESAI</p>
        <p class="cs-val">{{ returnsList.length }} Transaksi</p>
        <p class="cs-trend up">✅ Auto-Approved</p>
      </div>
      <div class="retur-stat-card left-accent orange">
        <p class="cs-label">ALASAN TERBANYAK</p>
        <p class="cs-val text-truncate">{{ topReason }}</p>
        <p class="cs-trend warning">⚠️ Masalah Kualitas</p>
      </div>
    </div>

    <!-- Toolbar Filters -->
    <div class="toolbar">
      <div class="search-box">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input v-model="searchQuery" type="text" placeholder="Cari retur berdasarkan SKU, nama produk, atau ID transaksi..." />
      </div>
    </div>

    <!-- Returns History Table -->
    <div class="table-card">
      <div class="table-head-row">
        <span>TANGGAL RETUR</span>
        <span>ID RETUR & TXN</span>
        <span class="col-product">PRODUK & DETAIL</span>
        <span>QTY RETUR</span>
        <span>ALASAN</span>
        <span>OLEH STAF</span>
      </div>

      <div v-if="loading" class="table-loading">
        Sedang memuat data retur...
      </div>
      <div v-else-if="filteredReturns.length === 0" class="table-empty">
        Tidak ada catatan retur barang ditemukan.
      </div>
      <div v-else v-for="ret in filteredReturns" :key="ret.id" class="table-data-row">
        <div class="date-col">
          <p class="ret-date">{{ formatDate(ret.created_at) }}</p>
          <p class="ret-time">{{ formatTime(ret.created_at) }} WIB</p>
        </div>
        <div>
          <span class="badge-retur">#RET-{{ ret.id }}</span>
          <p class="txn-ref">TXN #{{ ret.transaction_id }}</p>
        </div>
        <div class="col-product product-info">
          <p class="prod-name">{{ ret.transaction_item?.product?.name || 'Produk Sembako' }}</p>
          <p class="prod-sku">SKU: {{ ret.transaction_item?.product?.sku || '-' }}</p>
        </div>
        <span class="qty-text">{{ ret.qty }} Pcs</span>
        <span class="reason-text" :title="ret.reason">{{ ret.reason }}</span>
        <span class="staff-text">👤 {{ ret.user?.name || 'Administrator' }}</span>
      </div>

      <div class="table-footer">
        Menampilkan {{ filteredReturns.length }} dari {{ returnsList.length }} catatan retur
      </div>
    </div>

    <!-- Multi-Step Return Creator Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-box" :class="{ 'wide-modal': step === 2 }">
        <div class="modal-header">
          <h2 class="modal-title">Pencatatan Retur Barang (Langkah {{ step }} dari 3)</h2>
          <button @click="closeModal" class="close-btn">✕</button>
        </div>

        <!-- STEP 1: Search & Select Transaction -->
        <div v-if="step === 1" class="step-container">
          <p class="modal-desc">Cari transaksi penjualan asal dari barang sembako yang ingin diretur.</p>
          <div class="form-group mb-4">
            <label>ID Transaksi / ID Penjualan</label>
            <div class="search-input-wrapper">
              <input v-model="searchTxId" type="text" class="form-input" placeholder="Masukkan ID transaksi (Contoh: 1, 2, ...)" @keyup.enter="searchTransaction" />
              <button class="btn-search-txn" @click="searchTransaction" :disabled="searchingTx">
                {{ searchingTx ? 'Mencari...' : 'Cari' }}
              </button>
            </div>
            <p v-if="searchError" class="error-msg mt-2">{{ searchError }}</p>
          </div>

          <div v-if="txResults.length > 0" class="tx-results-list">
            <p class="section-title">Hasil Pencarian Transaksi:</p>
            <div v-for="tx in txResults" :key="tx.id" class="tx-result-card" @click="selectTransaction(tx)">
              <div class="tx-result-header">
                <span class="tx-id">#TXN-{{ tx.id }}</span>
                <span class="tx-date">{{ formatDate(tx.transaction_date) }}</span>
              </div>
              <div class="tx-result-body">
                <p>Pelanggan: <strong>{{ tx.customer?.name || 'Customer Umum' }}</strong></p>
                <p>Total Penjualan: <strong>Rp {{ formatNum(tx.total_amount) }}</strong></p>
                <p>Metode Pembayaran: <span class="badge-method">{{ tx.payment_method?.toUpperCase() }}</span></p>
              </div>
              <div class="tx-result-footer">
                <span>Klik untuk memilih transaksi ini &rarr;</span>
              </div>
            </div>
          </div>
          <div v-else-if="searched && txResults.length === 0" class="tx-no-results">
            Transaksi tidak ditemukan. Harap periksa kembali ID Transaksi.
          </div>
        </div>

        <!-- STEP 2: Select Item to Return -->
        <div v-if="step === 2" class="step-container">
          <p class="modal-desc">Pilih produk dari Transaksi #TXN-{{ selectedTx?.id }} yang ingin dikembalikan.</p>
          
          <div class="tx-detail-info mb-4">
            <p><strong>Tanggal:</strong> {{ formatDate(selectedTx?.transaction_date) }} | <strong>Pelanggan:</strong> {{ selectedTx?.customer?.name || 'Umum' }}</p>
          </div>

          <div class="table-card max-h-60 overflow-y">
            <div class="table-head-row item-row">
              <span>NAMA PRODUK</span>
              <span>QTY BELI</span>
              <span>SUBTOTAL</span>
              <span class="text-center">AKSI</span>
            </div>
            <div v-for="item in selectedTx?.items" :key="item.id" class="table-data-row item-row">
              <div class="product-info">
                <p class="prod-name">{{ item.product?.name }}</p>
                <p class="prod-sku">SKU: {{ item.product?.sku }}</p>
              </div>
              <span>{{ item.qty }} Pcs</span>
              <span>Rp {{ formatNum(item.subtotal) }}</span>
              <div class="text-center">
                <button class="btn-select-item" @click="selectItemForReturn(item)">Pilih</button>
              </div>
            </div>
          </div>

          <div class="modal-actions mt-4">
            <button class="btn-cancel" @click="step = 1">Kembali</button>
          </div>
        </div>

        <!-- STEP 3: Enter Return Quantity & Reason -->
        <div v-if="step === 3" class="step-container">
          <p class="modal-desc">Masukkan kuantitas retur dan alasan pengembalian barang sembako ini.</p>
          
          <div class="selected-product-banner mb-4">
            <div class="prod-detail">
              <span class="label">Produk Terpilih:</span>
              <span class="val">{{ selectedItem?.product?.name }}</span>
            </div>
            <div class="prod-detail mt-2">
              <span class="label">Maksimal Kuantitas Retur:</span>
              <span class="val bold">{{ maxQtyToReturn }} Pcs</span>
            </div>
          </div>

          <div class="form-group mb-3">
            <label>Kuantitas yang Diretur (Pcs)</label>
            <input type="number" v-model.number="formData.qty" min="1" :max="maxQtyToReturn" class="form-input" required />
            <p class="hint">Harus berkisar antara 1 s/d {{ maxQtyToReturn }} Pcs</p>
          </div>

          <div class="form-group mb-4">
            <label>Alasan Retur / Masalah Barang</label>
            <textarea v-model="formData.reason" class="form-input" rows="3" placeholder="Sebutkan alasan (contoh: Mie Instan hancur, Beras basah, dsb)" required></textarea>
          </div>

          <div class="modal-actions">
            <button class="btn-cancel" @click="step = 2">Kembali</button>
            <button class="btn-confirm" @click="submitReturn" :disabled="submitting">
              {{ submitting ? 'Memproses...' : 'Proses Retur Barang' }}
            </button>
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

    <!-- Status Bar -->
    <div class="status-bar">
      <span class="status-dot"></span>
      SINKRONISASI STOK BARANG OTOMATIS (FIFO RESTOCKED) • DB TRANSACTION SECURE
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useReturnStore } from '@/stores/return'
import ToastNotification from '@/components/shared/ToastNotification.vue'
import axios from '@/plugins/axios'

const returnStore = useReturnStore()

// Search & UI control states
const searchQuery = ref('')
const showModal = ref(false)
const step = ref(1)

// Multi-step form values
const searchTxId = ref('')
const searchingTx = ref(false)
const searched = ref(false)
const searchError = ref('')
const txResults = ref([])

const selectedTx = ref(null)
const selectedItem = ref(null)
const maxQtyToReturn = ref(0)
const submitting = ref(false)

const formData = ref({
  qty: 1,
  reason: ''
})

const toast = ref({
  show: false,
  message: '',
  type: 'success'
})

const loading = computed(() => returnStore.loading)
const returnsList = computed(() => returnStore.returns)

onMounted(async () => {
  await returnStore.fetchReturns()
})

// Statistics computed values
const totalReturnedQty = computed(() => {
  return returnsList.value.reduce((acc, curr) => acc + (curr.qty || 0), 0)
})

const topReason = computed(() => {
  if (returnsList.value.length === 0) return 'Belum Ada Retur'
  const counts = {}
  returnsList.value.forEach(ret => {
    const reason = ret.reason ? ret.reason.trim() : 'Lain-lain'
    counts[reason] = (counts[reason] || 0) + 1
  })
  let maxCount = 0
  let mainReason = 'Rusak / Kedaluwarsa'
  for (const reason in counts) {
    if (counts[reason] > maxCount) {
      maxCount = counts[reason]
      mainReason = reason
    }
  }
  return mainReason
})

// Filtered Returns List
const filteredReturns = computed(() => {
  return returnsList.value.filter(ret => {
    const query = searchQuery.value.toLowerCase()
    if (!query) return true
    
    const matchesSku = ret.transaction_item?.product?.sku?.toLowerCase().includes(query)
    const matchesName = ret.transaction_item?.product?.name?.toLowerCase().includes(query)
    const matchesTxId = ret.transaction_id?.toString().includes(query)
    const matchesRetId = ret.id?.toString().includes(query)
    const matchesReason = ret.reason?.toLowerCase().includes(query)

    return matchesSku || matchesName || matchesTxId || matchesRetId || matchesReason
  })
})

// Functions
function openAddModal() {
  step.value = 1
  searchTxId.value = ''
  txResults.value = []
  searched.value = false
  searchError.value = ''
  selectedTx.value = null
  selectedItem.value = null
  formData.value = { qty: 1, reason: '' }
  showModal.value = true
}

function closeModal() {
  showModal.value = false
}

async function searchTransaction() {
  if (!searchTxId.value.trim()) {
    searchError.value = 'Harap masukkan ID Transaksi terlebih dahulu.'
    return
  }
  searchError.value = ''
  searchingTx.value = true
  searched.value = true
  txResults.value = []

  try {
    const response = await axios.get('/transactions', {
      params: { search: searchTxId.value.trim() }
    })
    
    // Exact match filter because search returns LIKE
    const exactMatches = response.data.filter(tx => tx.id.toString() === searchTxId.value.trim())
    
    if (exactMatches.length > 0) {
      txResults.value = exactMatches
    } else {
      // Fallback to like search if exact ID not match directly but listed
      txResults.value = response.data
    }
  } catch (error) {
    console.error('Error searching transaction:', error)
    searchError.value = 'Gagal mencari transaksi. Periksa koneksi backend Anda.'
  } finally {
    searchingTx.value = false
  }
}

function selectTransaction(tx) {
  selectedTx.value = tx
  step.value = 2
}

async function selectItemForReturn(item) {
  selectedItem.value = item
  submitting.value = true
  
  try {
    // Check how much qty is already returned from backend index
    const res = await axios.get('/returns')
    const alreadyReturned = res.data
      .filter(ret => ret.transaction_item_id === item.id)
      .reduce((acc, curr) => acc + (curr.qty || 0), 0)

    maxQtyToReturn.value = item.qty - alreadyReturned
    
    if (maxQtyToReturn.value <= 0) {
      showToast('Seluruh kuantitas barang ini pada transaksi tersebut sudah diretur!', 'danger')
      submitting.value = false
      return
    }

    formData.value.qty = maxQtyToReturn.value
    formData.value.reason = ''
    step.value = 3
  } catch (error) {
    console.error(error)
    showToast('Gagal memvalidasi histori retur barang ini', 'danger')
  } finally {
    submitting.value = false
  }
}

async function submitReturn() {
  if (formData.value.qty < 1 || formData.value.qty > maxQtyToReturn.value) {
    showToast(`Jumlah retur tidak valid (1 s/d ${maxQtyToReturn.value})`, 'danger')
    return
  }
  if (!formData.value.reason.trim()) {
    showToast('Harap isi alasan retur barang!', 'danger')
    return
  }

  submitting.value = true
  try {
    await returnStore.addReturn({
      transaction_item_id: selectedItem.value.id,
      qty: formData.value.qty,
      reason: formData.value.reason
    })
    
    showToast('Retur barang berhasil diproses dan stok dikembalikan ke gudang.', 'success')
    showModal.value = false
    await returnStore.fetchReturns() // Refresh list
  } catch (err) {
    showToast(err, 'danger')
  } finally {
    submitting.value = false
  }
}

// Helpers
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

function showToast(message, type = 'success') {
  toast.value.message = message
  toast.value.type = type
  toast.value.show = true
}
</script>

<style scoped>
.retur-page { max-width: 1100px; }
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

/* Stats cards */
.retur-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--spacing-md); margin-bottom: var(--spacing-xl); }
.retur-stat-card {
  background: var(--color-card-bg);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-lg);
  padding: var(--spacing-lg) var(--spacing-xl);
  border-left: 5px solid;
  min-width: 0;
}
.retur-stat-card.left-accent.emerald { border-left-color: var(--color-primary); }
.retur-stat-card.left-accent.blue    { border-left-color: var(--color-secondary); }
.retur-stat-card.left-accent.orange  { border-left-color: var(--color-warning); }

.cs-label { font-size: var(--font-xs); font-weight: 700; color: var(--color-text-muted); letter-spacing: 0.5px; margin-bottom: 8px; }
.cs-val   { font-size: var(--font-3xl); font-weight: 800; margin-bottom: 6px; }
.cs-trend { font-size: var(--font-sm); font-weight: 600; }
.cs-trend.up      { color: var(--color-secondary); }
.cs-trend.ok      { color: var(--color-primary); }
.cs-trend.warning { color: var(--color-warning); }

/* Toolbar */
.toolbar { display: flex; gap: var(--spacing-md); margin-bottom: var(--spacing-lg); }
.search-box {
  flex: 1; display: flex; align-items: center; gap: 10px;
  background: #fff; border: 1.5px solid var(--color-border);
  border-radius: var(--radius-md); padding: 0 14px; color: var(--color-text-muted);
}
.search-box:focus-within { border-color: var(--color-primary); }
.search-box input { flex: 1; border: none; padding: 12px 0; font-size: var(--font-base); outline: none; }

/* Table */
.table-card { background: var(--color-card-bg); border: 1px solid var(--color-border); border-radius: var(--radius-lg); overflow: hidden; }
.table-head-row {
  display: grid; grid-template-columns: 1.5fr 1fr 2fr 1fr 2fr 1.2fr;
  padding: 12px 20px; background: #F8FAFC;
  border-bottom: 1px solid var(--color-border);
  font-size: var(--font-xs); font-weight: 700; color: var(--color-text-muted); letter-spacing: 0.5px;
}
.table-data-row {
  display: grid; grid-template-columns: 1.5fr 1fr 2fr 1fr 2fr 1.2fr;
  align-items: center; padding: 16px 20px;
  border-bottom: 1px solid var(--color-border);
  transition: background var(--transition-fast);
}
.table-data-row:hover { background: #F8FAFC; }

.date-col {}
.ret-date { font-size: var(--font-base); font-weight: 700; }
.ret-time { font-size: var(--font-xs); color: var(--color-text-muted); }
.badge-retur {
  display: inline-block; padding: 4px 8px; border-radius: var(--radius-sm);
  background: #FEF2F2; color: var(--color-danger); font-family: monospace; font-size: var(--font-xs); font-weight: 700;
}
.txn-ref { font-size: var(--font-xs); color: var(--color-text-secondary); margin-top: 4px; font-weight: 500; }

.col-product { display: flex; flex-direction: column; gap: 2px; }
.prod-name { font-size: var(--font-base); font-weight: 700; color: #0F172A; }
.prod-sku { font-size: var(--font-xs); color: var(--color-text-muted); }

.qty-text { font-size: var(--font-base); font-weight: 700; color: var(--color-text-primary); }
.reason-text { font-size: var(--font-sm); color: var(--color-text-secondary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; padding-right: 10px; }
.staff-text { font-size: var(--font-sm); color: var(--color-text-secondary); font-weight: 600; }

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

/* Modals & multi-step return creator */
.modal-overlay {
  position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7);
  display: flex; align-items: center; justify-content: center; z-index: 1000;
}
.modal-box {
  background: #fff; border-radius: var(--radius-xl);
  width: 480px; max-width: 95vw; padding: var(--spacing-xl);
  box-shadow: 0 25px 50px rgba(15, 23, 42, 0.3);
  animation: slideUp 0.2s ease;
  transition: width 0.3s ease;
}
.modal-box.wide-modal { width: 720px; }

@keyframes slideUp { from { transform: translateY(24px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.modal-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--spacing-lg); }
.modal-title  { font-size: var(--font-xl); font-weight: 700; color: #0F172A; }
.close-btn { color: var(--color-text-muted); padding: 6px; border-radius: var(--radius-sm); font-size: 18px; }
.close-btn:hover { background: #F1F5F9; }

.modal-desc { font-size: var(--font-sm); color: var(--color-text-secondary); margin-bottom: var(--spacing-lg); }

.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-group label { font-size: var(--font-xs); font-weight: 700; color: var(--color-text-muted); }
.form-input {
  padding: 11px 14px; border: 1.5px solid var(--color-border);
  border-radius: var(--radius-md); font-size: var(--font-base);
  transition: border-color var(--transition-fast); font-family: inherit;
  width: 100%; box-sizing: border-box;
}
.form-input:focus { outline: none; border-color: var(--color-primary); }

.search-input-wrapper { display: flex; gap: 10px; }
.btn-search-txn {
  background: var(--color-sidebar-bg); color: #fff;
  border-radius: var(--radius-md); padding: 0 20px; font-weight: 600;
  transition: background 0.15s ease;
}
.btn-search-txn:hover { background: #1E293B; }
.btn-search-txn:disabled { opacity: 0.7; }

/* Steps */
.tx-results-list { margin-top: 20px; display: flex; flex-direction: column; gap: 10px; max-height: 250px; overflow-y: auto; padding-right: 4px; }
.section-title { font-size: var(--font-xs); font-weight: 800; color: var(--color-text-muted); text-transform: uppercase; margin-bottom: 8px; }
.tx-result-card {
  border: 1px solid var(--color-border); border-radius: var(--radius-md);
  padding: 12px; cursor: pointer; transition: all 0.2s ease;
}
.tx-result-card:hover { border-color: var(--color-primary); background: #F0FDF4; }
.tx-result-header { display: flex; justify-content: space-between; margin-bottom: 8px; }
.tx-result-header .tx-id { font-weight: 700; color: #0F172A; }
.tx-result-header .tx-date { font-size: var(--font-xs); color: var(--color-text-muted); }
.tx-result-body { font-size: var(--font-sm); color: var(--color-text-secondary); display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px; }
.badge-method { background: #F1F5F9; font-size: 10px; padding: 2px 6px; border-radius: 4px; font-weight: 700; color: #475569; }
.tx-result-footer { font-size: var(--font-xs); color: var(--color-primary); font-weight: 700; margin-top: 8px; text-align: right; }

.tx-no-results { padding: 20px; text-align: center; border: 1.5px dashed var(--color-border); border-radius: var(--radius-md); font-size: var(--font-sm); color: var(--color-text-muted); }

.tx-detail-info { background: #F8FAFC; border-radius: var(--radius-md); padding: 12px; border: 1px solid var(--color-border); font-size: var(--font-sm); color: var(--color-text-secondary); }

.table-head-row.item-row, .table-data-row.item-row {
  grid-template-columns: 2fr 1fr 1fr 100px;
}
.table-head-row.item-row { padding: 10px 16px; font-size: 11px; }
.table-data-row.item-row { padding: 12px 16px; font-size: var(--font-sm); }
.btn-select-item {
  background: var(--color-primary); color: #fff; font-size: var(--font-xs); font-weight: 700;
  border-radius: var(--radius-sm); padding: 6px 14px;
}
.btn-select-item:hover { background: var(--color-primary-hover); }

/* Banner for Selected Product */
.selected-product-banner {
  background: #ECFDF5; border: 1px solid #A7F3D0; border-radius: var(--radius-md);
  padding: 16px;
}
.prod-detail { display: flex; justify-content: space-between; font-size: var(--font-sm); }
.prod-detail .label { color: #065F46; font-weight: 600; }
.prod-detail .val { color: #047857; font-weight: 700; }
.prod-detail .val.bold { font-size: var(--font-base); text-decoration: underline; }

.hint { font-size: var(--font-xs); color: var(--color-text-muted); margin-top: 4px; }

.modal-actions { display: flex; gap: 12px; margin-top: 24px; }
.btn-cancel {
  flex: 1; padding: 12px; border: 1.5px solid var(--color-border);
  border-radius: var(--radius-md); font-weight: 600; color: var(--color-text-secondary);
  text-align: center;
}
.btn-cancel:hover { border-color: var(--color-danger); color: var(--color-danger); }
.btn-confirm {
  flex: 2; padding: 12px; background: var(--color-primary); color: #fff;
  border-radius: var(--radius-md); font-weight: 700;
  transition: background var(--transition-fast);
}
.btn-confirm:hover { background: var(--color-primary-hover); }
.btn-confirm:disabled { opacity: 0.7; }

.text-truncate {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
