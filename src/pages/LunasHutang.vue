<template>
  <div class="pay-debt-page">
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-left">
        <button class="btn-back" @click="$router.push('/pelanggan')">
          <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
          Kembali ke Direktori
        </button>
        <h1 class="page-title">Pelunasan Hutang</h1>
        <p class="page-subtitle">Kelola dan tandai lunas transaksi hutang pelanggan secara transparan.</p>
      </div>
    </div>

    <!-- Main Grid -->
    <div class="grid-container" v-if="customer">
      <!-- Customer Card -->
      <div class="card customer-summary-card">
        <div class="avatar-large" :style="{ background: avatarColor }">{{ initials }}</div>
        <h2 class="customer-name">{{ customer.name }}</h2>
        <p class="customer-tier">{{ customer.tier || 'Regular Customer' }}</p>
        
        <div class="divider"></div>

        <div class="stats-box">
          <div class="stat-item">
            <span class="stat-label">TOTAL HUTANG AKTIF</span>
            <span class="stat-val danger-text">Rp {{ formatNum(customer.current_debt) }}</span>
          </div>
          <div class="stat-item">
            <span class="stat-label">BATAS KREDIT (LIMIT)</span>
            <span class="stat-val">Rp {{ formatNum(customer.debt_limit) }}</span>
          </div>
        </div>

        <div class="divider"></div>

        <div class="contact-info">
          <div class="info-row">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            <span>{{ customer.phone || 'Tidak ada nomor telepon' }}</span>
          </div>
          <div class="info-row">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><circle cx="12" cy="12" r="1.5"/></svg>
            <span class="address-text">{{ customer.address || 'Tidak ada alamat' }}</span>
          </div>
        </div>
      </div>

      <!-- Debts List Card -->
      <div class="debts-container">
        <h3 class="section-title">Daftar Nota Hutang Belum Lunas</h3>
        
        <div v-if="loading" class="loading-state">
          <div class="spinner"></div>
          <p>Memuat daftar hutang...</p>
        </div>

        <div v-else-if="unpaidDebts.length === 0" class="empty-state">
          <div class="empty-icon">🎉</div>
          <h4>Semua Hutang Telah Lunas!</h4>
          <p>Pelanggan ini tidak memiliki catatan hutang yang tertunda saat ini.</p>
        </div>

        <div v-else class="debts-grid">
          <div v-for="d in unpaidDebts" :key="d.id" class="card debt-card">
            <div class="debt-header">
              <div>
                <span class="debt-tag">NOTA HUTANG #{{ d.id }}</span>
                <p class="debt-date">Tanggal Transaksi: {{ formatDate(d.created_at) }}</p>
              </div>
              <span class="due-badge" :class="{ 'overdue': isOverdue(d.due_date) }">
                Jatuh Tempo: {{ formatDate(d.due_date) }}
              </span>
            </div>

            <div class="debt-amounts">
              <div class="amount-block">
                <span class="amount-label">Jumlah Awal</span>
                <span class="amount-val">Rp {{ formatNum(d.amount) }}</span>
              </div>
              <div class="amount-block">
                <span class="amount-label">Sisa Hutang</span>
                <span class="amount-val remaining">Rp {{ formatNum(d.remaining_amount) }}</span>
              </div>
            </div>

            <!-- Pay Form -->
            <div class="pay-form-wrapper">
              <h4 class="form-title">Catat Cicilan / Pelunasan</h4>
              <div class="form-grid">
                <div class="form-group">
                  <label>Jumlah Bayar (Rp)</label>
                  <input 
                    type="number" 
                    class="form-input" 
                    v-model.number="paymentAmounts[d.id]" 
                    :max="d.remaining_amount"
                    min="1"
                    placeholder="Masukkan nominal bayar..."
                  />
                </div>
                <div class="form-group">
                  <label>Tanggal Pembayaran</label>
                  <input type="date" class="form-input" v-model="paymentDates[d.id]" />
                </div>
              </div>
              <div class="form-group margin-top-sm">
                <label>Catatan (Opsional)</label>
                <input type="text" class="form-input" v-model="paymentNotes[d.id]" placeholder="Keterangan tambahan..." />
              </div>
              <div class="form-actions">
                <button class="btn-pay-action" @click="processPayment(d)" :disabled="submitting[d.id]">
                  <span v-if="submitting[d.id]">Memproses...</span>
                  <span v-else>Bayar Nota Ini</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- System Sync Toast -->
    <ToastNotification v-if="toast.show" :message="toast.message" :type="toast.type" @hide="toast.show = false" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axios from '@/plugins/axios'
import ToastNotification from '@/components/shared/ToastNotification.vue'

const route = useRoute()
const customerId = route.params.id

const customer = ref(null)
const unpaidDebts = ref([])
const loading = ref(true)

// Form states mapped by debt ID
const paymentAmounts = ref({})
const paymentDates = ref({})
const paymentNotes = ref({})
const submitting = ref({})

const toast = ref({ show: false, message: '', type: 'success' })

function showToast(message, type = 'success') {
  toast.value = { show: true, message, type }
}

const initials = computed(() => {
  if (!customer.value) return ''
  return customer.value.name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase()
})

const avatarColor = computed(() => {
  if (!customer.value) return '#059669'
  let hash = 0
  const str = customer.value.name
  for (let i = 0; i < str.length; i++) {
    hash = str.charCodeAt(i) + ((hash << 5) - hash)
  }
  const colors = ['#1E40AF', '#374151', '#059669', '#DC2626', '#7C3AED', '#B45309', '#6366F1']
  return colors[Math.abs(hash) % colors.length]
})

async function fetchCustomerDetails() {
  try {
    const res = await axios.get(`/customers/${customerId}`)
    customer.value = res.data
  } catch (err) {
    console.error(err)
    showToast('Gagal memuat data pelanggan', 'error')
  }
}

async function fetchUnpaidDebts() {
  loading.value = true
  try {
    const res = await axios.get('/debts', {
      params: {
        customer_id: customerId,
        status: 'unpaid'
      }
    })
    unpaidDebts.value = res.data
    
    // Initialize form states
    const today = new Date().toISOString().substring(0, 10)
    unpaidDebts.value.forEach(d => {
      paymentAmounts.value[d.id] = d.remaining_amount
      paymentDates.value[d.id] = today
      paymentNotes.value[d.id] = ''
      submitting.value[d.id] = false
    })
  } catch (err) {
    console.error(err)
    showToast('Gagal memuat daftar hutang', 'error')
  } finally {
    loading.value = false
  }
}

async function processPayment(debt) {
  const amount = paymentAmounts.value[debt.id]
  const paymentDate = paymentDates.value[debt.id]
  const note = paymentNotes.value[debt.id]

  if (!amount || amount <= 0 || amount > debt.remaining_amount) {
    showToast('Jumlah bayar tidak valid atau melebihi sisa hutang.', 'error')
    return
  }

  submitting.value[debt.id] = true
  try {
    await axios.post(`/debts/${debt.id}/pay`, {
      amount,
      payment_date: paymentDate,
      note
    })
    showToast('Pembayaran hutang berhasil dicatat!')
    
    // Refresh page data
    await fetchCustomerDetails()
    await fetchUnpaidDebts()
  } catch (err) {
    console.error(err)
    showToast(err.response?.data?.message || 'Gagal membayar hutang', 'error')
  } finally {
    submitting.value[debt.id] = false
  }
}

function isOverdue(dueDateStr) {
  if (!dueDateStr) return false
  return new Date(dueDateStr) < new Date()
}

function formatNum(n) { return (n || 0).toLocaleString('id-ID') }

function formatDate(dStr) {
  if (!dStr) return '-'
  const date = new Date(dStr)
  return date.toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' })
}

onMounted(async () => {
  await fetchCustomerDetails()
  await fetchUnpaidDebts()
})
</script>

<style scoped>
.pay-debt-page {
  max-width: 1100px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: var(--spacing-xl);
}

.btn-back {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: transparent;
  color: var(--color-primary);
  font-weight: 700;
  font-size: var(--font-sm);
  padding: 8px 12px;
  border-radius: var(--radius-md);
  margin-bottom: var(--spacing-sm);
  transition: all var(--transition-fast);
  border: 1px solid transparent;
}

.btn-back:hover {
  background: var(--color-primary-light);
  border-color: var(--color-primary-light);
}

.page-title {
  font-size: var(--font-3xl);
  font-weight: 700;
  color: var(--color-text-primary);
}

.page-subtitle {
  font-size: var(--font-sm);
  color: var(--color-text-muted);
  margin-top: 4px;
}

/* Grid Layout */
.grid-container {
  display: grid;
  grid-template-columns: 320px 1fr;
  gap: var(--spacing-lg);
  align-items: start;
}

/* Customer Summary Card */
.customer-summary-card {
  padding: var(--spacing-xl);
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.avatar-large {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  color: #fff;
  font-weight: 800;
  font-size: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: var(--spacing-md);
  box-shadow: var(--shadow-md);
}

.customer-name {
  font-size: var(--font-xl);
  font-weight: 700;
  color: var(--color-text-primary);
  margin-bottom: 4px;
}

.customer-tier {
  font-size: var(--font-sm);
  color: var(--color-text-muted);
  font-weight: 600;
}

.stats-box {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: var(--spacing-md);
  text-align: left;
}

.stat-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.stat-label {
  font-size: 10px;
  font-weight: 800;
  color: var(--color-text-muted);
  letter-spacing: 0.5px;
}

.stat-val {
  font-size: var(--font-lg);
  font-weight: 800;
  color: var(--color-text-primary);
}

.stat-val.danger-text {
  color: var(--color-danger);
  font-size: var(--font-xl);
}

.contact-info {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 12px;
  text-align: left;
  font-size: var(--font-sm);
  color: var(--color-text-secondary);
}

.info-row {
  display: flex;
  align-items: flex-start;
  gap: 10px;
}

.info-row svg {
  color: var(--color-text-muted);
  flex-shrink: 0;
  margin-top: 2px;
}

.address-text {
  line-height: 1.4;
}

/* Debts List Area */
.debts-container {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-md);
}

.section-title {
  font-size: var(--font-lg);
  font-weight: 700;
  color: var(--color-text-primary);
}

.loading-state, .empty-state {
  background: var(--color-card-bg);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-lg);
  padding: 48px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.empty-icon {
  font-size: 48px;
  margin-bottom: var(--spacing-md);
}

.empty-state h4 {
  font-size: var(--font-lg);
  font-weight: 700;
  margin-bottom: 8px;
}

.empty-state p {
  font-size: var(--font-sm);
  color: var(--color-text-muted);
  max-width: 360px;
}

/* Spinner */
.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid var(--color-border);
  border-top-color: var(--color-primary);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: var(--spacing-md);
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.debts-grid {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-md);
}

.debt-card {
  padding: var(--spacing-xl);
  display: flex;
  flex-direction: column;
  gap: var(--spacing-md);
}

.debt-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  border-bottom: 1px dashed var(--color-border);
  padding-bottom: var(--spacing-md);
}

.debt-tag {
  font-size: var(--font-xs);
  font-weight: 800;
  color: var(--color-primary);
  letter-spacing: 0.5px;
}

.debt-date {
  font-size: var(--font-sm);
  color: var(--color-text-muted);
  margin-top: 2px;
}

.due-badge {
  padding: 4px 10px;
  border-radius: var(--radius-sm);
  font-size: var(--font-xs);
  font-weight: 700;
  background: var(--color-warning-light);
  color: var(--color-warning);
}

.due-badge.overdue {
  background: var(--color-danger-light);
  color: var(--color-danger);
}

.debt-amounts {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--spacing-md);
  background: #F8FAFC;
  padding: var(--spacing-md);
  border-radius: var(--radius-md);
}

.amount-block {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.amount-label {
  font-size: 10px;
  font-weight: 800;
  color: var(--color-text-muted);
  letter-spacing: 0.5px;
}

.amount-val {
  font-size: var(--font-md);
  font-weight: 700;
}

.amount-val.remaining {
  color: var(--color-danger);
  font-size: var(--font-lg);
  font-weight: 800;
}

/* Pay Form inside debt card */
.pay-form-wrapper {
  background: #FFFFFF;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  padding: var(--spacing-md);
  margin-top: var(--spacing-xs);
}

.form-title {
  font-size: var(--font-sm);
  font-weight: 700;
  color: var(--color-text-secondary);
  margin-bottom: var(--spacing-md);
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--spacing-md);
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-group label {
  font-size: 11px;
  font-weight: 700;
  color: var(--color-text-muted);
}

.form-input {
  padding: 10px 12px;
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius-md);
  font-size: var(--font-sm);
  background: #F8FAFC;
  width: 100%;
}

.form-input:focus {
  outline: none;
  border-color: var(--color-primary);
  background: #fff;
}

.margin-top-sm {
  margin-top: var(--spacing-sm);
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: var(--spacing-md);
}

.btn-pay-action {
  padding: 10px 20px;
  background: var(--color-primary);
  color: #fff;
  font-weight: 700;
  font-size: var(--font-sm);
  border-radius: var(--radius-md);
  transition: all var(--transition-fast);
}

.btn-pay-action:hover:not(:disabled) {
  background: var(--color-primary-hover);
}

.btn-pay-action:disabled {
  background: var(--color-text-muted);
  cursor: not-allowed;
  opacity: 0.7;
}
</style>
