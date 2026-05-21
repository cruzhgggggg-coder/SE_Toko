<template>
  <teleport to="body">
    <div class="modal-overlay" @click.self="$emit('close')">
      <div class="modal-box">
        <div class="modal-header">
          <div>
            <h2 class="modal-title">Catat Utang Pelanggan</h2>
            <p class="modal-sub">Transaksi akan disimpan sebagai bon/kasbon</p>
          </div>
          <button class="close-btn" @click="$emit('close')">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>

        <div class="debt-amount-box">
          <p class="debt-label">TOTAL HUTANG</p>
          <p class="debt-val">Rp {{ formatNum(props.total) }}</p>
        </div>

        <div class="form-section">
          <label class="input-label">NAMA PELANGGAN</label>
          <div class="input-search-wrap" style="position: relative;">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input 
              v-model="customerName" 
              class="form-input" 
              type="text" 
              placeholder="Cari atau masukkan nama pelanggan..." 
              @focus="showDropdown = true"
              @blur="handleBlur"
            />
            
            <!-- Customer Dropdown -->
            <div v-if="showDropdown && filteredCustomers.length > 0" class="customer-dropdown">
              <div 
                v-for="c in filteredCustomers" 
                :key="c.id" 
                class="dropdown-item"
                @mousedown="selectCustomer(c)"
              >
                <div class="item-info">
                  <span class="item-name">{{ c.name }}</span>
                  <span class="item-phone">{{ c.phone || 'No Phone' }}</span>
                </div>
                <span class="item-debt" v-if="c.current_debt > 0">Utang: Rp {{ formatNum(c.current_debt) }}</span>
              </div>
            </div>
          </div>

          <label class="input-label">NOMOR TELEPON (Opsional)</label>
          <input v-model="customerPhone" class="form-input" type="tel" placeholder="08xx-xxxx-xxxx" />

          <label class="input-label">TANGGAL JATUH TEMPO</label>
          <input v-model="dueDate" class="form-input" type="date" />

          <label class="input-label">CATATAN</label>
          <textarea v-model="notes" class="form-textarea" placeholder="Tambah catatan transaksi..."></textarea>

          <div v-if="overLimit" class="limit-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <span>Batas utang terlampaui! (Maks: Rp {{ formatNum(selectedCustomerLimit) }})</span>
          </div>
        </div>

        <div class="modal-actions">
          <button class="btn-cancel" @click="$emit('close')">Batalkan</button>
          <button class="btn-confirm-debt" @click="confirm" :disabled="!customerName.trim() || overLimit">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Simpan Kasbon
          </button>
        </div>
      </div>
    </div>
  </teleport>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useCustomerStore } from '@/stores/customer'

const props = defineProps({ total: Number })
const emit = defineEmits(['close', 'confirm'])

const customerStore = useCustomerStore()
const customerName = ref('')
const customerPhone = ref('')
const dueDate = ref('')
const notes = ref('')
const showDropdown = ref(false)

onMounted(() => {
  customerStore.fetchCustomers()
})

const filteredCustomers = computed(() => {
  if (!customerName.value) return []
  const query = customerName.value.toLowerCase()
  return customerStore.customers.filter(c => 
    c.name.toLowerCase().includes(query) || 
    (c.phone && c.phone.includes(query))
  ).slice(0, 5)
})

function selectCustomer(customer) {
  customerName.value = customer.name
  customerPhone.value = customer.phone || ''
  showDropdown.value = false
}

function handleBlur() {
  // Delay blur to allow mousedown to trigger on dropdown
  setTimeout(() => { showDropdown.value = false }, 200)
}

const defaultDebtLimit = 5000000

const selectedCustomerLimit = computed(() => {
  const c = customerStore.customers.find(c => c.name.toLowerCase() === customerName.value.toLowerCase())
  return c ? (c.debt_limit || defaultDebtLimit) : defaultDebtLimit
})

const overLimit = computed(() => {
  const c = customerStore.customers.find(c => c.name.toLowerCase() === customerName.value.toLowerCase())
  if (!c) return false
  const limit = c.debt_limit || defaultDebtLimit
  return (c.current_debt + props.total) > limit
})

function confirm() {
  if (customerName.value.trim()) {
    emit('confirm', { 
      customerName: customerName.value, 
      customerPhone: customerPhone.value,
      dueDate: dueDate.value, 
      total: props.total,
      notes: notes.value
    })
  }
}
function formatNum(n) { return (n || 0).toLocaleString('id-ID') }
</script>

<style scoped>
.modal-overlay {
  position: fixed; inset: 0;
  background: rgba(15, 23, 42, 0.7);
  display: flex; align-items: center; justify-content: center;
  z-index: 1000;
  animation: fadeIn 0.15s ease;
}
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
.modal-box {
  background: #fff;
  border-radius: var(--radius-xl);
  width: 480px;
  max-width: 95vw;
  padding: var(--spacing-xl);
  box-shadow: 0 25px 50px rgba(15, 23, 42, 0.3);
  animation: slideUp 0.2s ease;
}
@keyframes slideUp { from { transform: translateY(24px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.modal-header {
  display: flex; align-items: flex-start; justify-content: space-between;
  margin-bottom: var(--spacing-lg);
}
.modal-title { font-size: var(--font-xl); font-weight: 700; }
.modal-sub   { font-size: var(--font-sm); color: var(--color-text-muted); margin-top: 2px; }
.close-btn   { color: var(--color-text-muted); padding: 6px; border-radius: var(--radius-sm); }
.close-btn:hover { background: #F1F5F9; }

.debt-amount-box {
  background: var(--color-warning-light);
  border: 1.5px solid var(--color-warning);
  border-radius: var(--radius-md);
  padding: 20px;
  text-align: center;
  margin-bottom: var(--spacing-lg);
}
.debt-label { font-size: var(--font-xs); font-weight: 700; letter-spacing: 1px; color: var(--color-warning); }
.debt-val   { font-size: var(--font-3xl); font-weight: 800; color: var(--color-text-primary); margin-top: 4px; }

.form-section { display: flex; flex-direction: column; gap: var(--spacing-sm); margin-bottom: var(--spacing-lg); }
.input-label { font-size: var(--font-xs); font-weight: 700; color: var(--color-text-muted); letter-spacing: 0.5px; }
.input-search-wrap {
  display: flex; align-items: center; gap: 10px;
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius-md);
  padding: 0 14px;
  color: var(--color-text-muted);
}
.input-search-wrap:focus-within { border-color: var(--color-secondary); }
.input-search-wrap input { border: none; padding: 12px 0; font-size: var(--font-base); width: 100%; outline: none; }

.customer-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: #fff;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  margin-top: 4px;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  z-index: 50;
  max-height: 240px;
  overflow-y: auto;
}
.dropdown-item {
  padding: 10px 14px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  border-bottom: 1px solid #f8fafc;
}
.dropdown-item:last-child { border-bottom: none; }
.dropdown-item:hover { background: #f1f5f9; }
.item-info { display: flex; flex-direction: column; }
.item-name { font-weight: 600; color: var(--color-text-primary); font-size: var(--font-sm); }
.item-phone { font-size: 11px; color: var(--color-text-muted); }
.item-debt { font-size: 11px; font-weight: 700; color: var(--color-danger); background: #fef2f2; padding: 2px 8px; border-radius: 100px; }
.form-input {
  width: 100%;
  padding: 12px 14px;
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius-md);
  font-size: var(--font-base);
  transition: border-color var(--transition-fast);
}
.form-input:focus { outline: none; border-color: var(--color-secondary); }
.form-textarea {
  width: 100%;
  padding: 12px 14px;
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius-md);
  font-size: var(--font-base);
  height: 72px;
  resize: none;
  font-family: inherit;
}
.form-textarea:focus { outline: none; border-color: var(--color-secondary); }

.modal-actions { display: flex; gap: 12px; }
.btn-cancel {
  flex: 1; padding: 14px;
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius-md);
  font-weight: 600; color: var(--color-text-secondary);
}
.btn-cancel:hover { border-color: var(--color-danger); color: var(--color-danger); }
.btn-confirm-debt {
  flex: 2; display: flex; align-items: center; justify-content: center; gap: 8px;
  padding: 14px; background: var(--color-secondary); color: #fff;
  border-radius: var(--radius-md); font-weight: 700;
  transition: background var(--transition-fast);
}
.btn-confirm-debt:hover:not(:disabled) { background: #1E3A8A; }
.btn-confirm-debt:disabled { opacity: 0.4; cursor: not-allowed; }

.limit-warning {
  background: #FEF2F2;
  border: 1px solid #FCA5A5;
  color: #DC2626;
  padding: 10px;
  border-radius: var(--radius-md);
  font-size: 12px;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 8px;
}
</style>
