<template>
  <teleport to="body">
    <div class="modal-overlay" @click.self="$emit('close')">
      <div class="modal-box">
        <div class="modal-header">
          <div>
            <h2 class="modal-title">Proses Pembayaran</h2>
            <p class="modal-sub">Pilih metode dan masukkan jumlah bayar</p>
          </div>
          <button class="close-btn" @click="$emit('close')">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>

        <!-- Payment Methods -->
        <div class="method-grid">
          <button
            v-for="method in methods"
            :key="method.key"
            class="method-btn"
            :class="{ active: activeMethod === method.key }"
            @click="activeMethod = method.key"
          >
            <span class="method-icon">{{ method.icon }}</span>
            <span class="method-label">{{ method.label }}</span>
          </button>
        </div>

        <!-- Total & Cash Input -->
        <div class="total-display">
          <p class="total-label">TOTAL TAGIHAN</p>
          <p class="total-val">Rp {{ formatNum(props.total) }}</p>
        </div>

        <div v-if="activeMethod === 'tunai'" class="cash-section">
          <label class="input-label">JUMLAH BAYAR (TUNAI)</label>
          <input
            v-model.number="cashPaid"
            type="number"
            class="cash-input"
            placeholder="0"
            @focus="$event.target.select()"
          />
          <!-- Quick Cash Buttons -->
          <div class="quick-cash">
            <button v-for="val in quickCash" :key="val" class="quick-btn" @click="cashPaid = val">
              Rp {{ formatNum(val) }}
            </button>
          </div>
          <div class="change-box" :class="{ positive: change >= 0, negative: change < 0 }">
            <span>KEMBALIAN</span>
            <span class="change-val">Rp {{ formatNum(change) }}</span>
          </div>
        </div>

        <div v-else class="qris-section">
          <div class="qris-placeholder">
            <svg width="80" height="80" fill="none" viewBox="0 0 24 24" stroke="#CBD5E1" stroke-width="1">
              <rect x="2" y="2" width="8" height="8"/><rect x="14" y="2" width="8" height="8"/><rect x="2" y="14" width="8" height="8"/>
              <line x1="14" y1="14" x2="14" y2="14.01"/><line x1="18" y1="14" x2="18" y2="14.01"/>
              <line x1="22" y1="14" x2="22" y2="14.01"/><line x1="14" y1="18" x2="14" y2="18.01"/>
              <line x1="18" y1="18" x2="22" y2="18.01"/>
            </svg>
            <p>Scan QRIS untuk membayar</p>
            <p class="qris-amount">Rp {{ formatNum(props.total) }}</p>
          </div>
        </div>

        <div class="modal-actions">
          <button class="btn-cancel" @click="$emit('close')">Batalkan</button>
          <button class="btn-confirm" @click="confirm" :disabled="!canConfirm">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Konfirmasi & Cetak Struk
          </button>
        </div>
      </div>
    </div>
  </teleport>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({ total: Number })
const emit = defineEmits(['close', 'confirm'])

const activeMethod = ref('tunai')
const cashPaid = ref(props.total)

const methods = [
  { key: 'tunai', label: 'Tunai',  icon: '💵' },
  { key: 'qris',  label: 'QRIS',   icon: '📱' },
  { key: 'transfer', label: 'Transfer', icon: '🏦' },
]

const quickCash = computed(() => {
  const t = props.total
  const vals = [t, 
    Math.ceil(t / 10000) * 10000,
    Math.ceil(t / 50000) * 50000,
    Math.ceil(t / 100000) * 100000,
  ]
  return [...new Set(vals)].slice(0, 4)
})

const change = computed(() => (cashPaid.value || 0) - props.total)
const canConfirm = computed(() => {
  if (activeMethod.value === 'tunai') return cashPaid.value >= props.total
  return true
})

function confirm() {
  if (canConfirm.value) {
    emit('confirm', {
      method: activeMethod.value,
      cashPaid: activeMethod.value === 'tunai' ? cashPaid.value : props.total
    })
  }
}

function formatNum(n) {
  return (n || 0).toLocaleString('id-ID')
}
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
  width: 520px;
  max-width: 95vw;
  padding: var(--spacing-xl);
  box-shadow: 0 25px 50px rgba(15, 23, 42, 0.3);
  animation: slideUp 0.2s ease;
}
@keyframes slideUp { from { transform: translateY(24px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

.modal-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: var(--spacing-lg);
}
.modal-title { font-size: var(--font-xl); font-weight: 700; }
.modal-sub   { font-size: var(--font-sm); color: var(--color-text-muted); margin-top: 2px; }
.close-btn   { color: var(--color-text-muted); padding: 6px; border-radius: var(--radius-sm); }
.close-btn:hover { background: #F1F5F9; color: var(--color-text-primary); }

.method-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
  margin-bottom: var(--spacing-lg);
}
.method-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  padding: 14px;
  border: 2px solid var(--color-border);
  border-radius: var(--radius-md);
  transition: all var(--transition-fast);
}
.method-btn.active { border-color: var(--color-primary); background: var(--color-primary-light); }
.method-icon  { font-size: 24px; }
.method-label { font-size: var(--font-sm); font-weight: 600; }

.total-display {
  background: var(--color-sidebar-bg);
  color: #fff;
  border-radius: var(--radius-md);
  padding: 20px;
  text-align: center;
  margin-bottom: var(--spacing-lg);
}
.total-label { font-size: var(--font-xs); opacity: 0.7; letter-spacing: 1px; margin-bottom: 6px; }
.total-val   { font-size: var(--font-3xl); font-weight: 800; }

.input-label { font-size: var(--font-xs); font-weight: 700; color: var(--color-text-muted); letter-spacing: 0.5px; display: block; margin-bottom: 8px; }
.cash-input {
  width: 100%;
  padding: 14px;
  border: 2px solid var(--color-border);
  border-radius: var(--radius-md);
  font-size: var(--font-xl);
  font-weight: 700;
  text-align: right;
  transition: border-color var(--transition-fast);
  margin-bottom: 10px;
}
.cash-input:focus { outline: none; border-color: var(--color-primary); }

.quick-cash {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 8px;
  margin-bottom: var(--spacing-md);
}
.quick-btn {
  padding: 8px 4px;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  font-size: var(--font-xs);
  font-weight: 600;
  color: var(--color-text-secondary);
  transition: all var(--transition-fast);
}
.quick-btn:hover { border-color: var(--color-primary); color: var(--color-primary); }

.change-box {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 14px 16px;
  border-radius: var(--radius-md);
  font-size: var(--font-sm);
  font-weight: 700;
  margin-bottom: var(--spacing-md);
}
.change-box.positive { background: var(--color-primary-light); color: var(--color-primary); }
.change-box.negative { background: var(--color-danger-light); color: var(--color-danger); }
.change-val { font-size: var(--font-xl); font-weight: 800; }

.qris-placeholder {
  border: 2px dashed var(--color-border);
  border-radius: var(--radius-md);
  padding: var(--spacing-xl);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  margin-bottom: var(--spacing-lg);
  color: var(--color-text-muted);
  font-size: var(--font-sm);
}
.qris-amount { font-size: var(--font-xl); font-weight: 700; color: var(--color-text-primary); }

.modal-actions {
  display: flex;
  gap: 12px;
  margin-top: var(--spacing-md);
}
.btn-cancel {
  flex: 1;
  padding: 14px;
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius-md);
  font-weight: 600;
  color: var(--color-text-secondary);
}
.btn-cancel:hover { border-color: var(--color-danger); color: var(--color-danger); }
.btn-confirm {
  flex: 2;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 14px;
  background: var(--color-primary);
  color: #fff;
  border-radius: var(--radius-md);
  font-size: var(--font-base);
  font-weight: 700;
  transition: background var(--transition-fast);
}
.btn-confirm:hover:not(:disabled) { background: var(--color-primary-hover); }
.btn-confirm:disabled { opacity: 0.4; cursor: not-allowed; }
</style>
