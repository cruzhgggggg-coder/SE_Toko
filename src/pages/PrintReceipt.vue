<template>
  <div class="print-page-wrapper">
    <!-- Screen View (Hidden when printing) -->
    <div class="screen-container no-print">
      <div class="action-sidebar">
        <div class="brand-info">
          <div class="logo-box">🏪</div>
          <div>
            <h3 class="sidebar-title">TOKO SUMBER MAKMUR</h3>
            <p class="sidebar-sub">POS Receipt System</p>
          </div>
        </div>

        <div class="divider-line"></div>

        <div class="actions-section">
          <p class="section-label">AKSI STRUK</p>
          <button class="action-btn print-btn" @click="triggerPrint">
            <span class="btn-icon">🖨️</span>
            Cetak Struk (Print)
          </button>
          
          <div class="navigation-group">
            <p class="section-label">NAVIGASI</p>
            <button class="action-btn nav-btn" @click="goBackToKasir">
              <span class="btn-icon">🛒</span>
              Kembali ke Kasir
            </button>
            <button class="action-btn nav-btn secondary" @click="goBackToLaporan">
              <span class="btn-icon">📊</span>
              Kembali ke Laporan
            </button>
          </div>
        </div>

        <div class="info-footer">
          <p>Mendukung printer thermal lebar 58mm secara optimal.</p>
        </div>
      </div>

      <div class="preview-area">
        <div v-if="loading" class="shimmer-receipt">
          <div class="shimmer-line header"></div>
          <div class="shimmer-line sub"></div>
          <div class="shimmer-line divider"></div>
          <div class="shimmer-line item"></div>
          <div class="shimmer-line item"></div>
          <div class="shimmer-line divider"></div>
          <div class="shimmer-line total"></div>
        </div>

        <div v-else-if="error" class="error-card">
          <div class="error-icon">⚠️</div>
          <h4>Gagal Memuat Struk</h4>
          <p>{{ error }}</p>
          <button class="retry-btn" @click="loadTransaction">Coba Lagi</button>
        </div>

        <!-- Simulated Paper Receipt Preview -->
        <div v-else class="paper-receipt-simulated animate-fade-in">
          <div class="receipt-body-content">
            <div class="store-info-section">
              <h2 class="store-name">TOKO SUMBER MAKMUR</h2>
              <p class="store-address">Jl. Contoh Alamat No. 123</p>
              <p class="store-contact">Telp: 0812-3456-7890</p>
              <div class="dashed-divider"></div>
              <div class="metadata-grid">
                <div>No: #TXN-{{ tx.id }}</div>
                <div>Tgl: {{ formatDate(tx.transaction_date) }}</div>
                <div>Jam: {{ formatTime(tx.transaction_date) }}</div>
                <div>Kasir: {{ tx.user?.name || 'Admin' }}</div>
              </div>
              <div class="dashed-divider"></div>
            </div>

            <div class="items-section">
              <table class="receipt-table">
                <tbody>
                  <tr v-for="item in tx.items" :key="item.id">
                    <td colspan="2">
                      <div class="item-name">{{ item.product?.name }}</div>
                      <div class="item-calculation">
                        <span>{{ item.qty }} {{ item.product?.unit || 'pcs' }} x {{ formatNum(item.price) }}</span>
                        <span class="item-subtotal">Rp {{ formatNum(item.subtotal) }}</span>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
              <div class="dashed-divider"></div>
            </div>

            <div class="totals-section">
              <div class="summary-row-flex">
                <span>SUBTOTAL</span>
                <span>Rp {{ formatNum(subtotal) }}</span>
              </div>
              <div v-if="discount > 0" class="summary-row-flex">
                <span>DISKON</span>
                <span>-Rp {{ formatNum(discount) }}</span>
              </div>
              <div class="summary-row-flex total-row-bold">
                <span>TOTAL</span>
                <span>Rp {{ formatNum(tx.total_amount) }}</span>
              </div>
              
              <template v-if="tx.payment_method !== 'debt'">
                <div class="summary-row-flex">
                  <span>BAYAR ({{ getPaymentMethodLabel(tx.payment_method) }})</span>
                  <span>Rp {{ formatNum(tx.total_amount) }}</span>
                </div>
                <div class="summary-row-flex">
                  <span>KEMBALIAN</span>
                  <span>Rp 0</span>
                </div>
              </template>
              <template v-else>
                <div class="summary-row-flex method-badge-debt">
                  <span>METODE</span>
                  <span>KASBON (Hutang)</span>
                </div>
                <div v-if="tx.customer" class="summary-row-flex text-muted-receipt">
                  <span>PELANGGAN</span>
                  <span>{{ tx.customer.name }}</span>
                </div>
              </template>
            </div>

            <div class="dashed-divider"></div>
            
            <div class="receipt-footer-section">
              <p class="thank-you-text">TERIMA KASIH</p>
              <p class="footer-note">Barang yang sudah dibeli tidak dapat ditukar/dikembalikan</p>
            </div>
          </div>
          <div class="jagged-edge"></div>
        </div>
      </div>
    </div>

    <!-- Print Only Element (Visible only in print mode) -->
    <div v-if="tx" class="print-receipt-thermal print-only">
      <h2 class="store-name">TOKO SUMBER MAKMUR</h2>
      <p class="store-address">Jl. Contoh Alamat No. 123</p>
      <p class="store-contact">Telp: 0812-3456-7890</p>
      <div class="dashed-divider"></div>
      
      <p>No: #TXN-{{ tx.id }}</p>
      <p>Tgl: {{ formatDate(tx.transaction_date) }} {{ formatTime(tx.transaction_date) }}</p>
      <p>Kasir: {{ tx.user?.name || 'Admin' }}</p>
      
      <div class="dashed-divider"></div>

      <div v-for="item in tx.items" :key="item.id" class="receipt-item-print">
        <p class="item-title">{{ item.product?.name }}</p>
        <div class="item-details-row">
          <span>{{ item.qty }} {{ item.product?.unit || 'pcs' }} x {{ formatNum(item.price) }}</span>
          <span>{{ formatNum(item.subtotal) }}</span>
        </div>
      </div>

      <div class="dashed-divider"></div>

      <div class="summary-row-print">
        <span>Subtotal:</span>
        <span>{{ formatNum(subtotal) }}</span>
      </div>
      <div v-if="discount > 0" class="summary-row-print">
        <span>Diskon:</span>
        <span>-{{ formatNum(discount) }}</span>
      </div>
      <div class="summary-row-print total-row-print">
        <span>Total:</span>
        <span>Rp {{ formatNum(tx.total_amount) }}</span>
      </div>
      
      <template v-if="tx.payment_method !== 'debt'">
        <div class="summary-row-print">
          <span>Bayar ({{ getPaymentMethodLabel(tx.payment_method) }}):</span>
          <span>Rp {{ formatNum(tx.total_amount) }}</span>
        </div>
        <div class="summary-row-print">
          <span>Kembali:</span>
          <span>Rp 0</span>
        </div>
      </template>
      <template v-else>
        <div class="summary-row-print">
          <span>Metode:</span>
          <span>KASBON (Utang)</span>
        </div>
        <div v-if="tx.customer" class="summary-row-print">
          <span>Pelanggan:</span>
          <span>{{ tx.customer.name }}</span>
        </div>
      </template>

      <div class="dashed-divider"></div>
      <p class="thank-you-print">TERIMA KASIH</p>
      <p class="thank-you-sub-print">Barang yang sudah dibeli tidak dapat ditukar/dikembalikan</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from '@/plugins/axios'

const route = useRoute()
const router = useRouter()

const tx = ref(null)
const loading = ref(true)
const error = ref(null)

const subtotal = computed(() => {
  if (!tx.value || !tx.value.items) return 0
  return tx.value.items.reduce((sum, item) => sum + parseFloat(item.subtotal), 0)
})

const discount = computed(() => {
  if (!tx.value) return 0
  return Math.max(0, subtotal.value - parseFloat(tx.value.total_amount))
})

const loadTransaction = async () => {
  loading.value = true
  error.value = null
  try {
    const res = await axios.get(`/transactions/${route.params.id}`)
    tx.value = res.data
    
    // Auto print if requested
    if (route.query.autoprint === 'true') {
      setTimeout(() => {
        triggerPrint()
      }, 500)
    }
  } catch (err) {
    console.error('Error fetching transaction detail:', err)
    error.value = err.response?.data?.message || 'Transaksi tidak ditemukan di database.'
  } finally {
    loading.value = false
  }
}

const triggerPrint = () => {
  window.print()
}

const goBackToKasir = () => {
  router.push('/kasir')
}

const goBackToLaporan = () => {
  router.push('/laporan')
}

const getPaymentMethodLabel = (method) => {
  if (method === 'cash') return 'TUNAI'
  if (method === 'transfer') return 'TRANSFER/QRIS'
  if (method === 'debt') return 'KASBON'
  return method?.toUpperCase() || ''
}

const formatNum = (n) => {
  return parseFloat(n || 0).toLocaleString('id-ID')
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}

const formatTime = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
}

onMounted(() => {
  loadTransaction()
})
</script>

<style scoped>
/* PREMIUM SCREEN CONTAINER STYLING */
.print-page-wrapper {
  min-height: 100vh;
  background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
  color: #F8FAFC;
}

.screen-container {
  display: grid;
  grid-template-columns: 350px 1fr;
  min-height: 100vh;
}

/* SIDEBAR ACTIONS */
.action-sidebar {
  background: rgba(30, 41, 59, 0.7);
  backdrop-filter: blur(16px);
  border-right: 1px solid rgba(255, 255, 255, 0.08);
  padding: 32px 24px;
  display: flex;
  flex-direction: column;
  box-shadow: 10px 0 30px rgba(0,0,0,0.25);
}

.brand-info {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 24px;
}

.logo-box {
  width: 48px;
  height: 48px;
  background: var(--color-primary, #059669);
  color: white;
  border-radius: 12px;
  font-size: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 15px rgba(5, 150, 105, 0.4);
}

.sidebar-title {
  font-size: 16px;
  font-weight: 800;
  letter-spacing: 0.5px;
  color: #FFF;
}

.sidebar-sub {
  font-size: 11px;
  font-weight: 600;
  color: #94A3B8;
  margin-top: 2px;
}

.divider-line {
  height: 1px;
  background: rgba(255, 255, 255, 0.08);
  margin: 12px 0 24px 0;
}

.actions-section {
  display: flex;
  flex-direction: column;
  gap: 16px;
  flex-grow: 1;
}

.section-label {
  font-size: 11px;
  font-weight: 800;
  color: #64748B;
  letter-spacing: 1px;
  margin-bottom: 6px;
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
  padding: 16px 20px;
  border-radius: 12px;
  font-weight: 700;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  border: 1px solid transparent;
}

.print-btn {
  background: var(--color-primary, #059669);
  color: white;
  box-shadow: 0 4px 20px rgba(5, 150, 105, 0.3);
}

.print-btn:hover {
  background: #047857;
  transform: translateY(-2px);
  box-shadow: 0 6px 24px rgba(5, 150, 105, 0.45);
}

.navigation-group {
  margin-top: 24px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.nav-btn {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.08);
  color: #E2E8F0;
}

.nav-btn:hover {
  background: rgba(255, 255, 255, 0.1);
  color: white;
  transform: translateX(4px);
  border-color: rgba(255, 255, 255, 0.15);
}

.nav-btn.secondary {
  background: transparent;
  border: 1px solid transparent;
  color: #94A3B8;
}

.nav-btn.secondary:hover {
  background: rgba(255, 255, 255, 0.03);
  color: #E2E8F0;
}

.btn-icon {
  font-size: 18px;
}

.info-footer {
  font-size: 11px;
  color: #64748B;
  text-align: center;
  line-height: 1.5;
  background: rgba(0,0,0,0.15);
  padding: 12px;
  border-radius: 8px;
  border: 1px solid rgba(255,255,255,0.02);
}

/* PREVIEW AREA */
.preview-area {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px;
  overflow-y: auto;
}

/* REALISTIC PAPER THERMAL RECEIPT */
.paper-receipt-simulated {
  background: #FFF;
  color: #000;
  width: 320px;
  box-shadow: 0 25px 60px rgba(0, 0, 0, 0.6), 0 16px 20px rgba(0, 0, 0, 0.4);
  position: relative;
  font-family: 'Courier New', Courier, monospace;
  font-size: 13px;
  line-height: 1.3;
}

.receipt-body-content {
  padding: 24px 20px 8px 20px;
}

.store-info-section {
  text-align: center;
  margin-bottom: 12px;
}

.store-name {
  font-size: 15px;
  font-weight: 900;
  letter-spacing: 0.5px;
  margin: 0 0 6px 0;
}

.store-address, .store-contact {
  font-size: 11px;
  color: #333;
  margin: 2px 0;
}

.dashed-divider {
  border-top: 1px dashed #333;
  margin: 10px 0;
  width: 100%;
}

.metadata-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  font-size: 11px;
  color: #111;
  gap: 4px;
  text-align: left;
}

/* items table */
.receipt-table {
  width: 100%;
  border-collapse: collapse;
}

.item-name {
  font-weight: bold;
  text-align: left;
}

.item-calculation {
  display: flex;
  justify-content: space-between;
  margin-top: 2px;
  margin-bottom: 8px;
  padding-left: 8px;
}

.item-subtotal {
  font-weight: bold;
}

/* totals styling */
.summary-row-flex {
  display: flex;
  justify-content: space-between;
  margin: 4px 0;
}

.total-row-bold {
  font-weight: bold;
  font-size: 15px;
  border-top: 1px dashed #333;
  padding-top: 8px;
  margin-top: 8px;
}

.method-badge-debt {
  color: #B45309;
  font-weight: bold;
}

.text-muted-receipt {
  color: #444;
  font-size: 12px;
}

.receipt-footer-section {
  text-align: center;
  margin-top: 16px;
  margin-bottom: 12px;
}

.thank-you-text {
  font-weight: bold;
  font-size: 14px;
  margin: 0 0 4px 0;
  letter-spacing: 1px;
}

.footer-note {
  font-size: 10px;
  color: #444;
  margin: 0;
}

/* Jagged edge paper look */
.jagged-edge {
  height: 12px;
  background-image: linear-gradient(-135deg, transparent 50%, #FFF 50%),
                    linear-gradient(135deg, transparent 50%, #FFF 50%);
  background-position: left bottom;
  background-repeat: repeat-x;
  background-size: 12px 12px;
  position: absolute;
  bottom: -11px;
  left: 0;
  right: 0;
  width: 100%;
}

/* SHIMMER SKELETON RECEIPT LOADING */
.shimmer-receipt {
  width: 320px;
  background: rgba(30, 41, 59, 0.4);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.shimmer-line {
  height: 12px;
  background: linear-gradient(90deg, rgba(255,255,255,0.05) 25%, rgba(255,255,255,0.12) 50%, rgba(255,255,255,0.05) 75%);
  background-size: 200% 100%;
  animation: loading-shimmer 1.5s infinite;
  border-radius: 4px;
}

.shimmer-line.header { height: 24px; width: 60%; margin: 0 auto; }
.shimmer-line.sub { height: 10px; width: 40%; margin: 0 auto; }
.shimmer-line.divider { height: 2px; background: rgba(255,255,255,0.05); width: 100%; animation: none; }
.shimmer-line.item { height: 16px; width: 90%; }
.shimmer-line.total { height: 20px; width: 100%; }

@keyframes loading-shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* ERROR STATE CARD */
.error-card {
  width: 320px;
  background: rgba(239, 68, 68, 0.08);
  border: 1px solid rgba(239, 68, 68, 0.2);
  border-radius: 16px;
  padding: 32px 24px;
  text-align: center;
  backdrop-filter: blur(8px);
}

.error-icon {
  font-size: 40px;
  margin-bottom: 16px;
}

.error-card h4 {
  color: #FCA5A5;
  font-size: 16px;
  font-weight: 700;
  margin-bottom: 8px;
}

.error-card p {
  color: #F87171;
  font-size: 13px;
  line-height: 1.5;
  margin-bottom: 20px;
}

.retry-btn {
  background: #EF4444;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 700;
  font-size: 13px;
  cursor: pointer;
  transition: background 0.2s;
}

.retry-btn:hover {
  background: #DC2626;
}

/* FADE IN ANIMATION */
.animate-fade-in {
  animation: slideUpFade 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes slideUpFade {
  from {
    transform: translateY(20px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

/* PRINT ONLY ELEMENT: HIDE BY DEFAULT */
.print-receipt-thermal {
  display: none;
}

.print-only {
  display: none;
}

/* PRINT MEDIA STYLES */
@media print {
  /* Hide the screen layouts */
  .no-print {
    display: none !important;
  }

  body * {
    visibility: hidden;
    background: transparent !important;
    box-shadow: none !important;
  }

  /* Only show print elements */
  .print-receipt-thermal, .print-receipt-thermal * {
    visibility: visible;
  }

  .print-receipt-thermal {
    display: block;
    position: absolute;
    left: 0;
    top: 0;
    width: 58mm; /* Standard receipt width */
    padding: 1mm;
    font-family: 'Courier New', Courier, monospace;
    font-size: 10px;
    line-height: 1.2;
    color: #000;
    background: #FFF !important;
  }

  @page {
    margin: 0;
    size: 58mm auto; /* Automatically extend paper length based on content */
  }

  /* Reset layout borders for pure printing */
  .store-name {
    font-size: 12px;
    font-weight: bold;
    text-align: center;
    margin: 0 0 2mm 0;
  }

  .store-address, .store-contact {
    text-align: center;
    font-size: 8px;
    margin: 0 0 1mm 0;
  }

  .dashed-divider {
    border-top: 1px dashed #000 !important;
    margin: 1.5mm 0;
    height: 0;
  }

  .receipt-item-print {
    margin-bottom: 1.5mm;
  }

  .item-title {
    font-weight: bold;
    margin: 0;
  }

  .item-details-row {
    display: flex;
    justify-content: space-between;
    padding-left: 2mm;
  }

  .summary-row-print {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.8mm;
  }

  .total-row-print {
    font-weight: bold;
    font-size: 12px;
    margin-top: 1.5mm;
  }

  .thank-you-print {
    text-align: center;
    font-weight: bold;
    font-size: 11px;
    margin: 2mm 0 0.5mm 0;
  }

  .thank-you-sub-print {
    text-align: center;
    font-size: 8px;
    margin: 0;
  }
}
</style>
