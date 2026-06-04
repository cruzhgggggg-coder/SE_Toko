<template>
  <div class="receipt-print-container">
    <div class="receipt-header">
      <h2 class="store-name">TOKO SUMBER MAKMUR</h2>
      <p class="store-address">Jl. Contoh Alamat No. 123</p>
      <p class="store-contact">Telp: 0812-3456-7890</p>
      <div class="divider"></div>
      <p class="receipt-date">{{ currentDate }}</p>
      <p class="receipt-cashier">Kasir: {{ auth.user?.name || 'Admin' }}</p>
      <div class="divider"></div>
    </div>

    <div class="receipt-body">
      <div v-for="item in cart" :key="item.id" class="receipt-item">
        <p class="item-name">{{ item.name }}</p>
        <div class="item-calc">
          <span>{{ item.qty }} {{ item.unit }} x {{ formatNum(item.price) }}</span>
          <span>{{ formatNum(item.price * item.qty) }}</span>
        </div>
      </div>
    </div>

    <div class="divider"></div>

    <div class="receipt-footer">
      <div class="summary-row">
        <span>Subtotal:</span>
        <span>{{ formatNum(subtotal) }}</span>
      </div>
      <div class="summary-row" v-if="discount > 0">
        <span>Diskon:</span>
        <span>-{{ formatNum(discount) }}</span>
      </div>
      <div class="summary-row total-row">
        <span>Total:</span>
        <span>Rp {{ formatNum(total) }}</span>
      </div>
      <div class="summary-row" v-if="paymentMethod !== 'debt'">
        <span>Tunai:</span>
        <span>Rp {{ formatNum(cashGiven) }}</span>
      </div>
      <div class="summary-row" v-if="paymentMethod !== 'debt'">
        <span>Kembali:</span>
        <span>Rp {{ formatNum(change) }}</span>
      </div>
      <div class="summary-row" v-if="paymentMethod === 'debt'">
        <span>Metode:</span>
        <span>KASBON (Utang)</span>
      </div>

      <div class="divider"></div>
      <p class="thank-you">Terima Kasih</p>
      <p class="thank-you-sub">Barang yang sudah dibeli tidak dapat ditukar/dikembalikan</p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'

const props = defineProps({
  cart: { type: Array, required: true },
  subtotal: { type: Number, required: true },
  discount: { type: Number, required: true },
  total: { type: Number, required: true },
  cashGiven: { type: Number, default: 0 },
  change: { type: Number, default: 0 },
  paymentMethod: { type: String, default: 'cash' }
})

const auth = useAuthStore()

const currentDate = computed(() => {
  const d = new Date()
  return d.toLocaleDateString('id-ID') + ' ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
})

function formatNum(n) {
  return n?.toLocaleString('id-ID') ?? '0'
}
</script>

<style scoped>
/* Hidden on screen, only visible when printing */
.receipt-print-container {
  display: none;
}

@media print {
  @page {
    margin: 0;
    size: 58mm auto; /* Typical thermal printer width */
  }

  body * {
    visibility: hidden;
  }

  .receipt-print-container, .receipt-print-container * {
    visibility: visible;
  }

  .receipt-print-container {
    display: block;
    position: absolute;
    left: 0;
    top: 0;
    width: 58mm;
    padding: 2mm;
    font-family: 'Courier New', Courier, monospace; /* Monospaced is best for receipts */
    font-size: 12px;
    line-height: 1.2;
    color: #000;
  }

  .receipt-header {
    text-align: center;
    margin-bottom: 4mm;
  }

  .store-name {
    font-size: 14px;
    font-weight: bold;
    margin: 0 0 2mm 0;
  }

  .store-address, .store-contact, .receipt-date, .receipt-cashier {
    margin: 0 0 1mm 0;
    font-size: 10px;
  }

  .divider {
    border-top: 1px dashed #000;
    margin: 2mm 0;
  }

  .receipt-item {
    margin-bottom: 2mm;
  }

  .item-name {
    margin: 0 0 1mm 0;
    font-weight: bold;
  }

  .item-calc {
    display: flex;
    justify-content: space-between;
    margin: 0;
    padding-left: 2mm;
  }

  .summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 1mm;
  }

  .total-row {
    font-weight: bold;
    font-size: 14px;
    margin-top: 2mm;
  }

  .thank-you {
    text-align: center;
    font-weight: bold;
    margin: 2mm 0 1mm 0;
  }

  .thank-you-sub {
    text-align: center;
    font-size: 10px;
    margin: 0;
  }
}
</style>
