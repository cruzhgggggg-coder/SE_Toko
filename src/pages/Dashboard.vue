<template>
  <div class="dashboard-page">

    <!-- Page Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Laporan Keuangan</h1>
        <p class="page-subtitle">Q3 Fiscal Overview • Terminal 01</p>
      </div>
      <div class="header-badge">
        <span class="role-badge">{{ authStore.user?.role?.toUpperCase() || 'OWNER' }} ACCESS</span>
      </div>
    </div>

    <!-- Expiry Alerts -->
    <div v-if="expiryAlerts.length > 0" class="dashboard-alert">
      <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
      <div class="alert-content">
        <strong>Peringatan!</strong> Terdapat {{ expiryAlerts.length }} batch produk yang kedaluwarsa atau mendekati kedaluwarsa.
        <button class="btn-link" @click="$emit('open-notifications')">Cek Notifikasi</button>
      </div>
    </div>

    <!-- Stat Cards -->
    <div class="stats-grid">
      <!-- Total Omset -->
      <div class="stat-card">
        <div class="stat-card-top">
          <div class="stat-icon light-blue">
            <i class="fas fa-money-bill-wave"></i>
          </div>
        </div>
        <div class="stat-info">
          <p class="stat-label">Total Omset</p>
          <h2 class="stat-value">{{ formatCurrency(stats.sales_today) }}</h2>
        </div>
      </div>

      <!-- Total Profit -->
      <div class="stat-card">
        <div class="stat-card-top">
          <div class="stat-icon light-emerald">
            <i class="fas fa-hand-holding-usd"></i>
          </div>
        </div>
        <div class="stat-info">
          <p class="stat-label">Total Profit</p>
          <h2 class="stat-value">{{ formatCurrency(stats.profit_today) }}</h2>
        </div>
      </div>

      <!-- Net Margin -->
      <div class="stat-card">
        <div class="stat-card-top">
          <div class="stat-icon light-orange">
            <i class="fas fa-chart-pie"></i>
          </div>
        </div>
        <div class="stat-info">
          <p class="stat-label">Net Margin</p>
          <h2 class="stat-value">{{ stats.net_margin.toFixed(1) }}%</h2>
        </div>
      </div>

      <!-- Total Transaksi -->
      <div class="stat-card">
        <div class="stat-card-top">
          <div class="stat-icon light-indigo">
            <i class="fas fa-receipt"></i>
          </div>
        </div>
        <div class="stat-info">
          <p class="stat-label">Total Transaksi</p>
          <h2 class="stat-value">{{ stats.transaction_count_today }}</h2>
        </div>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="charts-row">
      <!-- Main Bar Chart -->
      <div class="chart-card main-chart">
        <div class="chart-header">
          <div class="header-text">
            <h3 class="chart-title">Laporan Omset & Profit</h3>
            <p class="chart-subtitle">Performa bulanan tahun berjalan {{ new Date().getFullYear() }}</p>
          </div>
          <div class="chart-legend">
            <div class="legend-item">
              <span class="legend-dot dot-omset"></span>
              <span>Omset</span>
            </div>
            <div class="legend-item">
              <span class="legend-dot dot-profit"></span>
              <span>Profit</span>
            </div>
          </div>
        </div>
        
        <div class="bar-chart-container">
          <div class="bar-group" v-for="(m, i) in chartData" :key="i">
            <div class="bar-stack">
              <div class="bar bar-omset" :style="{ height: m.omset + '%' }"></div>
              <div class="bar bar-profit" :style="{ height: m.profit + '%' }"></div>
            </div>
            <span class="bar-label">{{ m.label }}</span>
          </div>
        </div>
      </div>

      <!-- Analysis Growth Card -->
      <div class="chart-card growth-card">
        <div class="growth-header">
          <h3 class="chart-title">Analisis Pertumbuhan</h3>
          <p class="chart-subtitle">Tren profitabilitas berkelanjutan</p>
        </div>
        
        <div class="growth-visual-container">
          <svg viewBox="0 0 200 100" class="growth-line-svg">
            <path d="M0,80 Q25,75 50,60 T100,45 T150,25 T200,10 L200,100 L0,100 Z" fill="url(#growthGradient)" opacity="0.1" />
            <path d="M0,80 Q25,75 50,60 T100,45 T150,25 T200,10" fill="none" stroke="#059669" stroke-width="4" stroke-linecap="round" />
            <defs>
              <linearGradient id="growthGradient" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#059669" />
                <stop offset="100%" stop-color="#059669" stop-opacity="0" />
              </linearGradient>
            </defs>
          </svg>
        </div>

        <button class="btn-report-full" @click="$router.push('/laporan')">
          <i class="fas fa-eye"></i>
          Buka Laporan Keuangan Lengkap
        </button>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import axios from '@/plugins/axios'

const authStore = useAuthStore()
const isLoading = ref(true)
const expiryAlerts = ref([])

const stats = ref({
  sales_today: 0,
  profit_today: 0,
  net_margin: 0,
  transaction_count_today: 0
})

const chartData = ref([])

const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value || 0)
}

const fetchDashboardStats = async () => {
  try {
    isLoading.value = true
    const response = await axios.get('/dashboard/stats')
    stats.value = response.data
    
    // Process chart data to get percentage for bars
    if (response.data.chart_data && response.data.chart_data.length > 0) {
      const maxAmount = Math.max(...response.data.chart_data.map(d => d.amount)) || 1
      chartData.value = response.data.chart_data.map(d => ({
        label: d.day,
        omset: (d.amount / maxAmount) * 100,
        profit: (d.profit / maxAmount) * 100
      }))
    } else {
      chartData.value = []
    }
  } catch (error) {
    console.error('Failed to fetch dashboard stats:', error)
  } finally {
    isLoading.value = false
  }
}

const fetchDashboardNotifications = async () => {
  try {
    const response = await axios.get('/notifications')
    const notifications = response.data
    expiryAlerts.value = notifications.filter(n => n.type === 'EXPIRY' && n.severity === 'high')
  } catch (error) {
    console.error('Failed to fetch notifications:', error)
  }
}

onMounted(() => {
  fetchDashboardStats()
  fetchDashboardNotifications()
})
</script>

<style scoped>
.dashboard-page {
  padding: 0;
  width: 100%;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
}

.page-title {
  font-size: 28px;
  font-weight: 800;
  color: #0F172A;
  margin-bottom: 4px;
}

.page-subtitle {
  font-size: 14px;
  color: #64748B;
  font-weight: 500;
}

.role-badge {
  background: #0F172A;
  color: #fff;
  padding: 6px 14px;
  border-radius: 100px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.5px;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 24px;
  margin-bottom: 32px;
}

.stat-card {
  background: #fff;
  border-radius: 20px;
  padding: 24px;
  border: 1px solid #E2E8F0;
  display: flex;
  flex-direction: column;
  gap: 20px;
  transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px rgba(0,0,0,0.05);
}

.stat-card-top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.stat-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
}

.light-blue { background: #EFF6FF; color: #3B82F6; }
.light-emerald { background: #ECFDF5; color: #10B981; }
.light-orange { background: #FFF7ED; color: #F59E0B; }
.light-indigo { background: #EEF2FF; color: #6366F1; }

.stat-trend {
  font-size: 12px;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: 100px;
}

.stat-trend.up { background: #DCFCE7; color: #166534; }
.stat-trend.down { background: #FEE2E2; color: #991B1B; }


.stat-label {
  font-size: 14px;
  color: #64748B;
  font-weight: 600;
  margin-bottom: 6px;
}

.stat-value {
  font-size: 24px;
  font-weight: 900;
  color: #0F172A;
}

/* Charts Row */
.charts-row {
  display: grid;
  grid-template-columns: 1fr 360px;
  gap: 24px;
}

.chart-card {
  background: #fff;
  border-radius: 20px;
  border: 1px solid #E2E8F0;
  padding: 32px;
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 40px;
}

.chart-title {
  font-size: 18px;
  font-weight: 800;
  color: #0F172A;
  margin-bottom: 4px;
}

.chart-subtitle {
  font-size: 14px;
  color: #94A3B8;
  font-weight: 500;
}

.chart-legend {
  display: flex;
  gap: 20px;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
  font-weight: 700;
  color: #0F172A;
}

.legend-dot {
  width: 12px;
  height: 12px;
  border-radius: 4px;
}

.dot-omset { background: #E2E8F0; }
.dot-profit { background: #059669; }

/* Bar Chart Container */
.bar-chart-container {
  display: flex;
  align-items: stretch;
  justify-content: space-between;
  height: 240px;
  padding-bottom: 20px;
}

.bar-group {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  height: 100%;
}

.bar-stack {
  width: 100%;
  display: flex;
  align-items: flex-end;
  justify-content: center;
  gap: 6px;
  flex: 1;
}

.bar {
  width: 24px;
  border-radius: 6px 6px 0 0;
  transition: transform 0.2s;
}

.bar:hover { transform: scaleY(1.05); }

.bar-omset { background: #E2E8F0; }
.bar-profit { background: #059669; }

.bar-label {
  font-size: 11px;
  font-weight: 800;
  color: #94A3B8;
}

/* Growth Card */
.growth-card {
  display: flex;
  flex-direction: column;
}

.growth-header {
  margin-bottom: 32px;
}

.growth-visual-container {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px 0;
}

.growth-line-svg {
  width: 100%;
  height: auto;
  filter: drop-shadow(0 8px 16px rgba(5, 150, 105, 0.15));
}

.btn-report-full {
  width: 100%;
  background: #0F172A;
  color: #fff;
  padding: 16px;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  transition: background 0.2s;
  margin-top: 32px;
}

.btn-report-full:hover {
  background: #1E293B;
}

/* Alert Styling */
.dashboard-alert {
  background: #FEF2F2;
  border: 1px solid #FECACA;
  border-radius: 12px;
  padding: 16px 20px;
  margin-bottom: 24px;
  display: flex;
  align-items: center;
  gap: 16px;
  color: #991B1B;
}

.alert-icon {
  font-size: 24px;
  color: #DC2626;
}

.alert-content {
  font-size: 14px;
}

.btn-link {
  background: none;
  border: none;
  color: #DC2626;
  font-weight: 700;
  text-decoration: underline;
  cursor: pointer;
  padding: 0;
  margin-left: 8px;
}
</style>
