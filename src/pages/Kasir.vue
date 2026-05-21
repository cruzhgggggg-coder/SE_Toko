<template>
  <div class="kasir-page">

    <!-- Katalog Area -->
    <div class="catalog-area">
      <div class="catalog-header">
        <div class="header-left">
          <h1 class="catalog-title">Katalog Sembako</h1>
          <p class="catalog-subtitle">PILIH ITEM UNTUK DITAMBAHKAN KE KERANJANG</p>
        </div>
        
        <div class="header-actions" style="display:flex; gap:12px; align-items:center;">
          <button 
            v-if="transactionStore.offlineQueueCount > 0" 
            class="view-btn" 
            style="color: #D97706; border: 1px solid #FDE68A; background: #FFFBEB;"
            @click="syncOffline"
          >
            <i class="fas fa-sync" :class="{'fa-spin': transactionStore.loading}"></i> 
            SYNC ({{ transactionStore.offlineQueueCount }})
          </button>
          <div class="view-toggle">
            <button class="view-btn active">
              <i class="fas fa-list"></i> LIST
            </button>
          </div>
        </div>
      </div>

      <!-- Product List (Grouped by Category) -->
      <div class="catalog-content" ref="catalogContentRef">
        <div v-if="filteredGroups.length === 0" class="empty-results">
          <div class="empty-icon">🔍</div>
          <p>Produk tidak ditemukan</p>
          <span>Coba kata kunci lain atau periksa filter kategori</span>
        </div>

        <div v-for="group in filteredGroups" :key="group.name" class="category-group">
          <div class="category-header-row">
            <div class="cat-title">
              <div class="cat-icon-dot"></div>
              <h3 class="category-name">{{ group.name.toUpperCase() }}</h3>
            </div>
            <div class="cat-line"></div>
          </div>

          <div class="product-list-container">
            <div 
              v-for="product in group.products" 
              :key="product.id" 
              class="product-item-row"
              @click="addToCart(product)"
            >
              <div class="card-top">
                <div class="product-img-box">
                  <img v-if="product.image" :src="product.image" alt="" />
                  <i v-else class="fas fa-box"></i>
                </div>
                <div class="stock-badge-container">
                  <span class="stock-badge-new" :class="getStockClass(product.stock)">
                    {{ product.stock }} {{ product.unit }}
                  </span>
                </div>
              </div>

              <div class="card-body">
                <p class="sku-text">{{ product.sku }}</p>
                <p class="p-name">{{ product.name }}</p>
                <p class="p-desc">{{ product.category || 'Sembako' }}</p>
              </div>

              <div class="card-footer">
                <div class="price-box">
                  <p class="p-ecer">Rp {{ formatNum(product.price_ecer) }}</p>
                  <p class="p-grosir">Grosir: Rp {{ formatNum(product.price_grosir) }}</p>
                </div>
                <button class="add-cart-btn" @click.stop="addToCart(product)">
                  <i class="fas fa-plus"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Cart Sidebar -->
    <div class="cart-sidebar">
      <div class="cart-header">
        <h2 class="cart-title">KERANJANG BELANJA</h2>
        <span class="item-count-pill">{{ cartItemsCount }} ITEM</span>
      </div>

      <div class="cart-items-list">
        <div v-if="cart.length === 0" class="empty-cart-state">
          <i class="fas fa-shopping-basket"></i>
          <p>Belum ada belanjaan</p>
        </div>

        <div v-for="item in cart" :key="item.id" class="cart-item-card">
          <div class="item-card-header">
            <div class="item-main-info">
              <h4 class="item-name">{{ item.name }}</h4>
              <p class="item-sub-price">
                RP {{ formatNum(item.isGrosir ? item.price_grosir : item.price_ecer) }} X {{ item.qty }} {{ item.unit }}
              </p>
            </div>
            <div class="item-total-price">
              Rp {{ formatNum((item.isGrosir ? item.price_grosir : item.price_ecer) * item.qty) }}
            </div>
          </div>
          <div class="item-card-actions">
            <div class="qty-control-box">
              <button class="qty-action" @click="changeQty(item.id, -1)"><i class="fas fa-minus"></i></button>
              <span class="qty-number">{{ item.qty }}</span>
              <button class="qty-action" @click="changeQty(item.id, 1)"><i class="fas fa-plus"></i></button>
            </div>
            <div class="item-meta-actions">
              <button class="delete-item-btn" @click="removeFromCart(item.id)">
                <i class="far fa-trash-alt"></i>
              </button>
              <button 
                class="grosir-toggle-btn" 
                :class="{ active: item.isGrosir }"
                @click="item.isGrosir = !item.isGrosir"
              >
                GROSIR
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="cart-summary-footer">
        <div class="summary-details">
          <div class="summary-row">
            <span>SUBTOTAL</span>
            <span class="val-text">RP {{ formatNum(subtotal) }}</span>
          </div>
          <div class="summary-row">
            <span>DISKON</span>
            <span class="val-text text-danger">- RP {{ formatNum(discount) }}</span>
          </div>
        </div>

        <div class="total-banner-card">
          <p class="total-label">TOTAL BAYAR</p>
          <h1 class="total-amount">Rp {{ formatNum(total) }}</h1>
        </div>

        <div class="cart-action-buttons">
          <button class="btn-debt-new" @click="showDebt = true" :disabled="cart.length === 0">
            <i class="fas fa-book"></i> CATAT UTANG
          </button>
          <button class="btn-pay-new" @click="showPayment = true" :disabled="cart.length === 0">
            <i class="fas fa-cash-register"></i> BAYAR (F10)
          </button>
        </div>
      </div>
    </div>

    <!-- Modals & Print -->
    <PaymentModal v-if="showPayment" :total="total" @close="showPayment = false" @confirm="onPayConfirm" />
    <DebtModal v-if="showDebt" :total="total" @close="showDebt = false" @confirm="onDebtConfirm" />
    <ToastNotification v-if="toast.show" :message="toast.message" :type="toast.type" @hide="toast.show = false" />
    
    <ReceiptPrint 
      :cart="printCart" 
      :subtotal="printSubtotal" 
      :discount="printDiscount" 
      :total="printTotal" 
      :cashGiven="printCashGiven"
      :change="printChange"
      :paymentMethod="printPaymentMethod"
    />

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useInventoryStore } from '@/stores/inventory'
import { useTransactionStore } from '@/stores/transaction'
import { useCustomerStore } from '@/stores/customer'
import { useUIStore } from '@/stores/ui'
import PaymentModal from '@/components/modals/PaymentModal.vue'
import DebtModal from '@/components/modals/DebtModal.vue'
import ToastNotification from '@/components/shared/ToastNotification.vue'
import ReceiptPrint from '@/components/shared/ReceiptPrint.vue'

const router = useRouter()
const inventoryStore = useInventoryStore()
const transactionStore = useTransactionStore()
const customerStore = useCustomerStore()
const ui = useUIStore()

const showPayment = ref(false)
const showDebt = ref(false)
const cart = ref([])
const discount = ref(0)
const toast = ref({ show: false, message: '', type: 'success' })

// Print Data
const printCart = ref([])
const printSubtotal = ref(0)
const printDiscount = ref(0)
const printTotal = ref(0)
const printCashGiven = ref(0)
const printChange = ref(0)
const printPaymentMethod = ref('cash')

onMounted(async () => {
  await inventoryStore.fetchProducts()
  await customerStore.fetchCustomers()
  
  window.addEventListener('keydown', (e) => {
    if (e.key === 'F10' && cart.value.length > 0) {
      e.preventDefault()
      showPayment.value = true
    }
  })
})

const filteredGroups = computed(() => {
  const query = ui.searchQuery.toLowerCase()
  const groups = {}
  
  inventoryStore.products.forEach(p => {
    if (query && !p.name.toLowerCase().includes(query) && !p.sku.toLowerCase().includes(query)) return
    
    const cat = p.category || 'LAINNYA'
    if (!groups[cat]) groups[cat] = { name: cat, products: [] }
    groups[cat].products.push(p)
  })
  
  return Object.values(groups)
})

const cartItemsCount = computed(() => cart.value.reduce((s, i) => s + i.qty, 0))

function getStockClass(stock) {
  if (stock === 0) return 'stock-empty'
  if (stock <= 5) return 'stock-low'
  return 'stock-ok'
}

function addToCart(product) {
  const existing = cart.value.find(i => i.id === product.id)
  if (existing) {
    existing.qty++
  } else {
    cart.value.push({ 
      ...product, 
      qty: 1, 
      isGrosir: false 
    })
  }
}

function removeFromCart(id) {
  cart.value = cart.value.filter(i => i.id !== id)
}

function changeQty(id, delta) {
  const item = cart.value.find(i => id === i.id)
  if (item) {
    item.qty += delta
    if (item.qty <= 0) removeFromCart(id)
  }
}

const subtotal = computed(() => cart.value.reduce((s, i) => {
  const price = i.isGrosir ? i.price_grosir : i.price_ecer
  return s + price * i.qty
}, 0))

const total = computed(() => Math.max(0, subtotal.value - discount.value))

async function onPayConfirm(paymentData) {
  try {
    // Map payment method to database valid enums (cash, transfer, debt)
    let backendMethod = 'cash'
    if (paymentData.method === 'transfer' || paymentData.method === 'qris') {
      backendMethod = 'transfer'
    } else if (paymentData.method === 'tunai') {
      backendMethod = 'cash'
    }

    const payload = {
      customer_id: null,
      payment_method: backendMethod,
      items: cart.value.map(item => ({
        product_id: item.id,
        qty: item.qty,
        price: item.isGrosir ? item.price_grosir : item.price_ecer
      }))
    }
    const response = await transactionStore.submitTransaction(payload)
    
    if (response && response.id) {
      showPayment.value = false
      cart.value = []
      discount.value = 0
      await inventoryStore.fetchProducts()
      // Redirect to dedicated receipt page
      router.push({ name: 'PrintReceipt', params: { id: response.id }, query: { autoprint: 'true' } })
    } else {
      // Offline fallback
      printCart.value = [...cart.value]
      printSubtotal.value = subtotal.value
      printDiscount.value = discount.value
      printTotal.value = total.value
      printCashGiven.value = paymentData.cashPaid || total.value
      printChange.value = (paymentData.cashPaid || total.value) - total.value
      printPaymentMethod.value = paymentData.method

      showPayment.value = false
      cart.value = []
      discount.value = 0
      toast.value = { show: true, message: response?.message || 'Disimpan offline', type: 'success' }
      await inventoryStore.fetchProducts()

      // Trigger Print
      setTimeout(() => window.print(), 150)
    }
  } catch (err) {
    toast.value = { show: true, message: err, type: 'error' }
  }
}

async function onDebtConfirm(debtData) {
  try {
    const customer = customerStore.customers.find(c => c.name.toLowerCase() === debtData.customerName.toLowerCase())
    if (!customer) throw 'Pelanggan tidak ditemukan. Silakan tambahkan pelanggan terlebih dahulu.'
    const payload = {
      customer_id: customer.id,
      payment_method: 'debt',
      items: cart.value.map(item => ({
        product_id: item.id,
        qty: item.qty,
        price: item.isGrosir ? item.price_grosir : item.price_ecer
      })),
      notes: debtData.notes,
      due_date: debtData.dueDate
    }
    const response = await transactionStore.submitTransaction(payload)
    
    if (response && response.id) {
      showDebt.value = false
      cart.value = []
      discount.value = 0
      await inventoryStore.fetchProducts()
      // Redirect to dedicated receipt page
      router.push({ name: 'PrintReceipt', params: { id: response.id }, query: { autoprint: 'true' } })
    } else {
      // Offline fallback
      printCart.value = [...cart.value]
      printSubtotal.value = subtotal.value
      printDiscount.value = discount.value
      printTotal.value = total.value
      printCashGiven.value = 0
      printChange.value = 0
      printPaymentMethod.value = 'debt'

      showDebt.value = false
      cart.value = []
      discount.value = 0
      toast.value = { show: true, message: response?.message || 'Kasbon berhasil dicatat offline.', type: 'success' }
      await inventoryStore.fetchProducts()

      // Trigger Print
      setTimeout(() => window.print(), 150)
    }
  } catch (err) {
    toast.value = { show: true, message: err, type: 'error' }
  }
}

function formatNum(n) {
  return n?.toLocaleString('id-ID') ?? '0'
}

async function syncOffline() {
  try {
    const count = await transactionStore.syncOfflineTransactions()
    if (count > 0) {
      toast.value = { show: true, message: `Berhasil sync ${count} transaksi offline.`, type: 'success' }
    } else {
      toast.value = { show: true, message: 'Tidak ada transaksi offline untuk di-sync.', type: 'success' }
    }
  } catch (err) {
    toast.value = { show: true, message: err.message || 'Gagal sync transaksi', type: 'error' }
  }
}
</script>

<style scoped>
.kasir-page {
  display: grid;
  grid-template-columns: 1fr 380px;
  height: 100%;
  overflow: hidden;
  background: #F8FAFC;
}

@media print {
  .kasir-page {
    display: none !important;
  }
}

/* CATALOG AREA */
.catalog-area {
  display: flex;
  flex-direction: column;
  padding: 32px;
  overflow: hidden;
}

.catalog-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 24px;
}

.catalog-title {
  font-size: 28px;
  font-weight: 800;
  color: #0F172A;
  margin-bottom: 4px;
}

.catalog-subtitle {
  font-size: 11px;
  font-weight: 700;
  color: #64748B;
  letter-spacing: 0.5px;
}

.view-toggle {
  background: #E2E8F0;
  padding: 4px;
  border-radius: 8px;
}

.view-btn {
  padding: 6px 16px;
  font-size: 11px;
  font-weight: 800;
  color: #0F172A;
  background: #FFF;
  border-radius: 6px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
  display: flex;
  align-items: center;
  gap: 8px;
}

.catalog-content {
  flex: 1;
  overflow-y: auto;
  padding-right: 8px;
}

.category-group {
  margin-bottom: 32px;
}

.category-header-row {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 16px;
}

.cat-title {
  display: flex;
  align-items: center;
  gap: 10px;
}

.cat-icon-dot {
  width: 8px;
  height: 8px;
  background: #0F172A;
  border-radius: 2px;
}

.category-name {
  font-size: 13px;
  font-weight: 800;
  color: #0F172A;
  letter-spacing: 1px;
}

.cat-line {
  flex: 1;
  height: 1px;
  background: #E2E8F0;
}

/* PRODUCT GRID LAYOUT */
.product-list-container {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 16px;
}

.product-item-row {
  background: #FFF;
  border: 1px solid #E2E8F0;
  border-radius: 12px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  cursor: pointer;
  transition: all 0.2s;
  position: relative;
  overflow: hidden;
}

.product-item-row:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  border-color: #CBD5E1;
  background: #F8FAFC;
}

.card-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
}

.card-body {
  display: flex;
  flex-direction: column;
  gap: 4px;
  flex-grow: 1;
}

.card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  margin-top: auto;
  border-top: 1px dashed #E2E8F0;
  padding-top: 12px;
}

.product-cell {
  display: flex;
  align-items: center;
  gap: 14px;
}

.product-img-box {
  width: 42px;
  height: 42px;
  background: #F8FAFC;
  border: 1px solid #E2E8F0;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.product-img-box img { width: 100%; height: 100%; object-fit: cover; }
.product-img-box i { color: #CBD5E1; }

.p-name { font-size: 14px; font-weight: 700; color: #0F172A; }
.p-desc { font-size: 11px; color: #64748B; margin-top: 2px; }

.sku-text { font-family: monospace; color: #64748B; font-size: 12px; }

.stock-badge-new {
  font-size: 11px;
  font-weight: 800;
  padding: 4px 10px;
  border-radius: 100px;
}
.stock-ok { background: #DCFCE7; color: #166534; }
.stock-low { background: #FEF3C7; color: #92400E; }
.stock-empty { background: #FEE2E2; color: #991B1B; }

.price-box .p-ecer { font-size: 14px; font-weight: 800; color: #0F172A; }
.price-box .p-grosir { font-size: 11px; font-weight: 700; color: #059669; }

.add-cart-btn {
  width: 32px;
  height: 32px;
  background: #0F172A;
  color: #FFF;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* CART SIDEBAR */
.cart-sidebar {
  background: #F1F5F9;
  border-left: 1px solid #E2E8F0;
  display: flex;
  flex-direction: column;
}

.cart-header {
  padding: 24px;
  background: #FFF;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #E2E8F0;
}

.cart-title { font-size: 14px; font-weight: 900; color: #0F172A; }
.item-count-pill {
  background: #0F172A;
  color: #FFF;
  font-size: 10px;
  font-weight: 800;
  padding: 4px 10px;
  border-radius: 100px;
}

.cart-items-list {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.cart-item-card {
  background: #FFF;
  border-radius: 12px;
  padding: 16px;
  border: 1px solid #E2E8F0;
}

.item-card-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 12px;
}

.item-name { font-size: 14px; font-weight: 700; color: #0F172A; }
.item-sub-price { font-size: 11px; font-weight: 700; color: #64748B; margin-top: 2px; }
.item-total-price { font-size: 15px; font-weight: 800; color: #0F172A; }

.item-card-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.qty-control-box {
  display: flex;
  align-items: center;
  gap: 12px;
  background: #F8FAFC;
  padding: 4px;
  border-radius: 8px;
  border: 1px solid #E2E8F0;
}

.qty-action { width: 24px; height: 24px; color: #0F172A; }
.qty-number { font-size: 13px; font-weight: 800; }

.item-meta-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.grosir-toggle-btn {
  font-size: 9px;
  font-weight: 800;
  padding: 4px 8px;
  border-radius: 4px;
  background: #F1F5F9;
  color: #94A3B8;
  border: 1px solid #E2E8F0;
}

.grosir-toggle-btn.active {
  background: #DCFCE7;
  color: #059669;
  border-color: #059669;
}

.delete-item-btn { color: #CBD5E1; }
.delete-item-btn:hover { color: #EF4444; }

/* SUMMARY FOOTER */
.cart-summary-footer {
  background: #FFF;
  padding: 24px;
  border-top: 1px solid #E2E8F0;
}

.summary-details { margin-bottom: 16px; }
.summary-row {
  display: flex;
  justify-content: space-between;
  font-size: 12px;
  font-weight: 700;
  color: #64748B;
  margin-bottom: 6px;
}
.summary-row .val-text { color: #0F172A; }
.text-danger { color: #EF4444; }

.total-banner-card {
  background: #0F172A;
  border-radius: 12px;
  padding: 20px;
  text-align: center;
  margin-bottom: 16px;
  color: #FFF;
}

.total-label { font-size: 12px; font-weight: 700; opacity: 0.7; margin-bottom: 4px; }
.total-amount { font-size: 32px; font-weight: 900; }

.cart-action-buttons {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.btn-debt-new {
  width: 100%;
  padding: 14px;
  background: #DBEAFE;
  color: #2563EB;
  border-radius: 12px;
  font-weight: 800;
  font-size: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
}

.btn-pay-new {
  width: 100%;
  padding: 16px;
  background: #065F46;
  color: #FFF;
  border-radius: 12px;
  font-weight: 800;
  font-size: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
}

.empty-cart-state {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #CBD5E1;
  gap: 12px;
  padding-top: 100px;
}

.empty-cart-state i { font-size: 48px; }
.empty-cart-state p { font-size: 14px; font-weight: 600; }
</style>
