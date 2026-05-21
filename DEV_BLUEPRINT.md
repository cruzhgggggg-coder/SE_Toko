# 📋 DEVELOPMENT BLUEPRINT — Toko Sumber Makmur POS
> Dokumen referensi lengkap untuk pengembangan lanjutan sistem POS.
> **Terakhir diperbarui: 21 Mei 2026** — Setelah sesi bugfix komprehensif (20 bug diselesaikan).

---

## 🗂️ DAFTAR ISI
1. [Informasi Proyek & Tech Stack](#1-informasi-proyek--tech-stack)
2. [Struktur Direktori](#2-struktur-direktori)
3. [Design System & CSS Tokens](#3-design-system--css-tokens)
4. [Peta Halaman & Komponen Vue](#4-peta-halaman--komponen-vue)
5. [Pinia Stores](#5-pinia-stores)
6. [API Contract Lengkap (Laravel Backend)](#6-api-contract-lengkap-laravel-backend)
7. [Database Schema (ERD Summary)](#7-database-schema-erd-summary)
8. [Changelog: Semua Bugfix Hari Ini](#8-changelog-semua-bugfix-hari-ini)
9. [Aturan Pengembangan (Rules)](#9-aturan-pengembangan-rules)
10. [Cara Menjalankan Lokal](#10-cara-menjalankan-lokal)
11. [Skenario Pengujian Akademis](#11-skenario-pengujian-akademis)

---

## 1. Informasi Proyek & Tech Stack

| Aspek | Detail |
|---|---|
| **Nama Aplikasi** | Toko Sumber Makmur POS |
| **Versi** | 2.0 (Post-Bugfix Release) |
| **Tipe** | Desktop Web SPA (Single Page Application) |
| **Frontend** | Vue 3 + Composition API + Vite + Pinia |
| **Backend** | Laravel 11 REST API |
| **Database** | SQLite (file: `backend/database/database.sqlite`) |
| **Styling** | Vanilla CSS (TIDAK menggunakan Tailwind/Bootstrap) |
| **Offline Support** | IndexedDB via `src/utils/idb.js` |
| **Auth** | Laravel Sanctum (Token-based) |
| **Port Frontend** | `http://localhost:5173` |
| **Port Backend** | `http://localhost:8000` |

### Credentials Login Default
| Role | Username | Password |
|---|---|---|
| Owner (Admin Utama) | `owner` | `password123` |
| Admin Gudang | `admin` | `password123` |
| Kasir | `kasir1` | `password123` |

---

## 2. Struktur Direktori

```
Toko_SE_Semester4/
├── src/                          ← Vue 3 Frontend
│   ├── pages/                    ← Halaman Utama (Route-Level Components)
│   │   ├── Dashboard.vue         ← / (owner only)
│   │   ├── Kasir.vue             ← /kasir (owner, kasir)
│   │   ├── Inventory.vue         ← /inventory (owner, admin)
│   │   ├── Pelanggan.vue         ← /pelanggan (owner, kasir)
│   │   ├── LunasHutang.vue       ← /pelanggan/:id/bayar [BARU]
│   │   ├── Laporan.vue           ← /laporan (owner only)
│   │   ├── Pengaturan.vue        ← /pengaturan (owner only)
│   │   ├── Backup.vue            ← /backup (owner only)
│   │   ├── Login.vue             ← /login
│   │   ├── RoleSelection.vue     ← /select-role
│   │   └── PrintReceipt.vue      ← /print-receipt/:id (blank layout)
│   ├── components/
│   │   ├── modals/
│   │   │   ├── DebtModal.vue     ← Modal kasbon di Kasir
│   │   │   ├── PaymentModal.vue  ← Modal bayar tunai/QRIS
│   │   │   └── GantiUserModal.vue
│   │   └── shared/
│   │       ├── NotificationPanel.vue ← Slide-in notifikasi kanan
│   │       └── ToastNotification.vue ← Toast sukses/error
│   ├── stores/                   ← Pinia State Management
│   │   ├── auth.js               ← Auth, role, token, RBAC
│   │   ├── ui.js                 ← shopName, sidebar state [DIUPDATE]
│   │   ├── inventory.js          ← Products, stats
│   │   ├── customer.js           ← Customers list
│   │   ├── transaction.js        ← Transactions, offline queue
│   │   └── debt.js               ← Debts list
│   ├── services/                 ← Axios API wrappers
│   │   ├── InventoryService.js
│   │   ├── CustomerService.js
│   │   ├── TransactionService.js
│   │   ├── ReportService.js
│   │   ├── SettingService.js
│   │   └── NotificationService.js
│   ├── utils/
│   │   ├── excelExport.js        ← Utilitas download CSV/Excel [BARU]
│   │   └── idb.js                ← IndexedDB helper (offline queue)
│   ├── plugins/
│   │   └── axios.js              ← Axios instance (base URL, token interceptor)
│   ├── layouts/
│   │   └── MainLayout.vue        ← Sidebar + Header + Wrapper [DIUPDATE]
│   ├── router/
│   │   └── index.js              ← Vue Router + Auth Guard [DIUPDATE]
│   └── style.css                 ← Global CSS Design Tokens
│
├── backend/                      ← Laravel 11 Backend
│   ├── app/
│   │   ├── Models/
│   │   │   ├── Product.php       ← [DIUPDATE] updateLowStockStatus()
│   │   │   ├── Customer.php      ← [DIUPDATE] tier di fillable
│   │   │   ├── Debt.php
│   │   │   ├── DebtPayment.php
│   │   │   ├── Transaction.php
│   │   │   ├── TransactionItem.php
│   │   │   ├── StockBatch.php
│   │   │   ├── StockLog.php
│   │   │   ├── Category.php
│   │   │   ├── Supplier.php
│   │   │   ├── Setting.php
│   │   │   └── FinancialReport.php
│   │   └── Http/Controllers/Api/
│   │       ├── AuthController.php
│   │       ├── DashboardController.php
│   │       ├── ProductController.php     ← [DIUPDATE] SKU auto-gen, FIFO status
│   │       ├── CustomerController.php    ← [DIUPDATE] tier validation
│   │       ├── TransactionController.php ← [DIUPDATE] updateLowStockStatus
│   │       ├── DebtController.php        ← pay() endpoint
│   │       ├── CategoryController.php    ← Full CRUD
│   │       ├── SupplierController.php
│   │       ├── NotificationController.php
│   │       ├── FinancialReportController.php
│   │       ├── SettingController.php
│   │       └── BackupController.php
│   ├── database/
│   │   ├── migrations/           ← 22 migration files
│   │   ├── seeders/
│   │   │   ├── DatabaseSeeder.php  ← [DIUPDATE] seed categories + settings
│   │   │   ├── ProductSeeder.php   ← [DIUPDATE] link ke category ID
│   │   │   └── UserSeeder.php
│   │   └── database.sqlite       ← File database SQLite
│   └── routes/
│       └── api.php               ← Semua route API
│
├── DESIGN_BLUEPRINT.md           ← Blueprint Lama (Presentasi Akademis)
├── DEV_BLUEPRINT.md              ← Blueprint Ini (Panduan Developer)
├── narasi-bug-agen-ai.md         ← Daftar 20 Bug yang Diselesaikan
└── vite.config.js
```

---

## 3. Design System & CSS Tokens

Semua token CSS didefinisikan di `src/style.css`. **Wajib gunakan token ini, bukan hardcode hex!**

```css
/* Warna Utama */
--color-primary:       #059669  /* Hijau utama — tombol save, aksi positif */
--color-primary-hover: #047857  /* Hover state */
--color-primary-light: #D1FAE5  /* Background badge hijau muda */

--color-secondary:     #2563EB  /* Biru — info, link */
--color-danger:        #DC2626  /* Merah — error, hapus, overdue */
--color-danger-light:  #FEE2E2
--color-warning:       #D97706  /* Kuning — peringatan stok menipis */
--color-warning-light: #FEF3C7

/* Sidebar (Light Mode) */
--color-sidebar-bg:    #FFFFFF  /* ⚠️ PUTIH — bukan gelap! */
--color-sidebar-hover: #F0FDF4
--color-sidebar-text:  #475569

/* Layout */
--sidebar-width:       224px
--header-height:       64px

/* Spacing */
--spacing-xs: 4px  |  --spacing-sm: 8px  |  --spacing-md: 16px
--spacing-lg: 24px |  --spacing-xl: 32px |  --spacing-2xl: 48px

/* Border Radius */
--radius-sm: 6px  |  --radius-md: 10px  |  --radius-lg: 16px
--radius-full: 9999px

/* Typography */
--font-xs: 12px | --font-sm: 13px | --font-base: 15px
--font-md: 16px | --font-lg: 17px | --font-xl: 19px
--font-2xl: 23px | --font-3xl: 29px
```

> ⚠️ **PENTING**: `--color-sidebar-bg = #FFFFFF` (putih). Jangan gunakan variabel ini untuk background elemen yang membutuhkan teks putih! Gunakan `--color-primary` atau `#0F172A` sebagai gantinya.

---

## 4. Peta Halaman & Komponen Vue

### Diagram Halaman → Route → Role

```
/                    → Dashboard.vue       → owner
/kasir               → Kasir.vue           → owner, kasir
/inventory           → Inventory.vue       → owner, admin
/pelanggan           → Pelanggan.vue       → owner, kasir
/pelanggan/:id/bayar → LunasHutang.vue     → owner, kasir  ← BARU
/laporan             → Laporan.vue         → owner
/pengaturan          → Pengaturan.vue      → owner
/backup              → Backup.vue          → owner
/login               → Login.vue           → public
/select-role         → RoleSelection.vue   → public
/print-receipt/:id   → PrintReceipt.vue    → all (blank layout)
```

### Komponen Kritis

#### `DebtModal.vue` — Modal Kasbon Kasir
- **Props**: `:total` (number), `:cart` (array)
- **Emits**: `@confirm(debtData)`, `@close`
- **Logic**: Cek `customer.current_debt + total > customer.debt_limit` → tampilkan warning merah, disable tombol konfirmasi
- **Fix**: Gunakan `c.current_debt` (BUKAN `c.total_debt` — field tidak ada)

#### `NotificationPanel.vue` — Slide-In Notifikasi
- **Emits**: `@close`, `@update-count`
- **localStorage key**: `dismissed_notifications` (JSON array of keys)
- **Key format**: `"${type}:${title}:${message}"` — unik per konten notifikasi
- **Poll interval**: 60 detik
- ⚠️ Header background harus `--color-primary` (bukan `--color-sidebar-bg`)

#### `LunasHutang.vue` — Halaman Pelunasan Hutang [BARU]
- **Route param**: `id` (customer ID)
- **API calls**: `GET /api/customers/:id`, `GET /api/debts?customer_id=:id&status=unpaid`
- **Submit**: `POST /api/debts/:debtId/pay` → `{ amount, payment_date, note }`
- **Refresh**: Setelah bayar sukses, re-fetch customer + debts

#### `excelExport.js` — Download CSV/Excel [BARU]
```js
import { exportTransactionsExcel, exportDailyReportExcel, exportDetailedReportExcel } from '@/utils/excelExport'

// Download transaksi
exportTransactionsExcel(filteredTransactions, 'Semua Periode')

// Download setelah simpan laporan harian
exportDailyReportExcel(reportData)

// Download dari modal detail penjualan
exportDetailedReportExcel(detailedReport, dateRangeStr)
```
> Format: CSV dengan UTF-8 BOM (`\uFEFF`) + separator titik koma (`;`) → kompatibel Microsoft Excel regional Indonesia.

---

## 5. Pinia Stores

### `stores/auth.js`
```js
state: { user, token, role }
getters: { isAuthenticated, roleLabel, allowedMenus }
actions: { login(), logout(), setRole() }
```

### `stores/ui.js` ← DIUPDATE
```js
state: {
  searchQuery: '',
  isSidebarCollapsed: false,
  showNotification: false,
  shopName: 'TOKO SUMBER MAKMUR'  ← BARU: reactive shop name
}
actions: {
  setSearchQuery(query),
  setShopName(name)               ← BARU: dipanggil saat save Pengaturan
}
```

### `stores/inventory.js`
```js
state: { products[], loading, error }
getters: {
  stats: { totalSKU, lowStock, emptyStock, totalValue }
  // lowStock = products where total_stock > 0 && total_stock <= min_stock
  // emptyStock = products where total_stock === 0
}
actions: { fetchProducts(), addProduct(), editProduct(), addStock() }
```

### `stores/customer.js`
```js
state: { customers[], loading, error }
actions: {
  fetchCustomers(),
  addCustomer(data),      ← returns customer object dengan id
  updateCustomer(id, data)
}
```

### `stores/transaction.js`
```js
state: { transactions[], loading }
actions: {
  createTransaction(data),  ← supports offline queue via IndexedDB
  syncOffline(),            ← upload antrian offline ke backend
  fetchTransaction(id)
}
```

---

## 6. API Contract Lengkap (Laravel Backend)

### Auth
| Method | Endpoint | Body / Params | Response |
|---|---|---|---|
| POST | `/api/login` | `{email, password}` | `{token, user, role}` |
| POST | `/api/logout` | — | `{message}` |
| GET | `/api/me` | — | `{user}` |

### Products (Stok Barang)
| Method | Endpoint | Body / Params | Notes |
|---|---|---|---|
| GET | `/api/products` | — | Returns `total_stock`, `is_low_stock` (live calculated), `batches[]` |
| POST | `/api/products` | `{name*, unit*, min_stock*, sku?, category?, category_id?}` | SKU auto-gen jika kosong (`PRD-XXXXX`) |
| PUT | `/api/products/:id` | `{name?, sku?, category?, unit?, min_stock?}` | Calls `updateLowStockStatus()` |
| DELETE | `/api/products/:id` | — | — |
| POST | `/api/products/:id/stock` | `{batch_number*, qty*, buy_price*, sell_price*, expired_date?}` | Calls `updateLowStockStatus()` |
| GET | `/api/products/:id/history` | — | Stock logs |

### Categories (Kategori)
| Method | Endpoint | Body | Notes |
|---|---|---|---|
| GET | `/api/categories` | — | Full list |
| POST | `/api/categories` | `{name*}` | — |
| PUT | `/api/categories/:id` | `{name*}` | — |
| DELETE | `/api/categories/:id` | — | — |

### Customers (Pelanggan)
| Method | Endpoint | Body | Notes |
|---|---|---|---|
| GET | `/api/customers` | — | Semua pelanggan |
| POST | `/api/customers` | `{name*, phone?, address?, tier?, debt_limit?}` | Default `debt_limit = 5.000.000` |
| PUT | `/api/customers/:id` | `{name?, phone?, address?, tier?, debt_limit?, is_active?}` | — |
| DELETE | `/api/customers/:id` | — | Gagal jika `current_debt > 0` |
| GET | `/api/customers/:id` | — | Include `debts[]` dan `transactions[]` |

### Transactions (Transaksi)
| Method | Endpoint | Body | Notes |
|---|---|---|---|
| GET | `/api/transactions` | `?start_date&end_date&payment_method` | Filter opsional |
| POST | `/api/transactions` | `{items[{product_id, qty, price}], payment_method, customer_id?, notes?}` | FIFO stock reduction + `updateLowStockStatus` |
| GET | `/api/transactions/:id` | — | Include `items.product` |
| POST | `/api/reports/profit` | `{password*, start_date?, end_date?}` | Protected — password required |
| POST | `/api/reports/detailed` | `{start_date?, end_date?}` | Sales by product, category, payment |

### Debts (Hutang)
| Method | Endpoint | Body | Notes |
|---|---|---|---|
| GET | `/api/debts` | `?customer_id&status` | Filter: `status=unpaid|paid` |
| GET | `/api/debts/:id` | — | Include `payments[]` |
| POST | `/api/debts/:id/pay` | `{amount*, payment_date*, note?}` | Decrements `customer.current_debt`, updates debt status |

### Financial Reports
| Method | Endpoint | Notes |
|---|---|---|
| GET | `/api/financial-reports` | Laporan harian tersimpan |
| GET | `/api/financial-reports/generate` | Generate laporan hari ini (tanpa simpan) |
| POST | `/api/financial-reports` | Simpan laporan harian ke DB |
| GET | `/api/financial-reports/:id` | Detail laporan |

### Settings
| Method | Endpoint | Body | Notes |
|---|---|---|---|
| GET | `/api/settings` | — | Returns `{shop_name, shop_address, shop_phone, ...}` |
| POST | `/api/settings` | `{shop_name?, shop_address?, shop_phone?}` | Key-value store |

### Notifications
| Method | Endpoint | Notes |
|---|---|---|
| GET | `/api/notifications` | Returns 3 tipe: `LOW_STOCK`, `EXPIRY`, `OVERDUE_DEBT` |

### Dashboard
| Method | Endpoint | Notes |
|---|---|---|
| GET | `/api/dashboard/stats` | Summary cards: revenue, transactions, debts, low stocks |

---

## 7. Database Schema (ERD Summary)

### Tabel Utama

```
products
├── id, name, sku (UNIQUE), category (text), category_id (FK)
├── supplier_id (FK), unit, min_stock
├── is_low_stock (boolean) ← diupdate otomatis via updateLowStockStatus()
├── base_buy_price, base_sell_price, image
└── timestamps

stock_batches
├── id, product_id (FK), batch_number
├── qty (awal), current_qty (sisa), buy_price, sell_price
├── expired_date (nullable), rack_location (nullable)
└── timestamps

customers
├── id, name, phone, address
├── tier (default: 'New Customer')   ← KOLOM BARU (migration 21 Mei)
├── debt_limit (decimal, default: 0)
├── current_debt (decimal, default: 0)
├── is_active (boolean)
└── timestamps

transactions
├── id, user_id (FK), customer_id (FK, nullable)
├── payment_method (tunai|qris|debt)
├── total_amount, notes, transaction_date
└── timestamps

transaction_items
├── id, transaction_id (FK), product_id (FK)
├── qty, price, cost_price, subtotal
└── timestamps

debts
├── id, customer_id (FK), transaction_id (FK)
├── total_amount, remaining_amount, status (unpaid|paid)
├── due_date, notes
└── timestamps

debt_payments
├── id, debt_id (FK), amount
├── payment_date, note
└── timestamps

categories
├── id, name (UNIQUE), description (nullable)
└── timestamps

settings
├── id, key (UNIQUE), value
└── timestamps
```

### Relasi Penting
```
Product    → hasMany StockBatch
Product    → hasMany StockLog
Product    → belongsTo Category
StockBatch → belongsTo Product

Customer   → hasMany Transaction
Customer   → hasMany Debt

Transaction → belongsTo Customer
Transaction → hasMany TransactionItem
Transaction → hasOne Debt (jika kasbon)

Debt       → belongsTo Customer
Debt       → belongsTo Transaction
Debt       → hasMany DebtPayment
```

---

## 8. Changelog: Semua Bugfix Hari Ini (21 Mei 2026)

### Bug #1 — `total_debt` → `current_debt` (DebtModal)
- **File**: `src/components/modals/DebtModal.vue`
- **Ubah**: `c.total_debt` → `c.current_debt` (2 tempat: tampilan dan computed `overLimit`)

### Bug #2 — SKU Auto-Generate
- **File**: `backend/app/Http/Controllers/Api/ProductController.php`
- **Ubah**: Validasi `'sku' => 'required'` → `'sku' => 'nullable'`. Jika kosong, auto-generate: `{3huruf nama}-{5karakter unik}`

### Bug #3 — Kategori Dinamis dari API
- **File**: `src/pages/Inventory.vue`
- **Ubah**: `const categories = ['Beras', ...]` (hardcoded) → `computed(() => categoriesList.value.map(c => c.name))` dari fetch `GET /api/categories`

### Bug #4 — Lihat Bug #1 (akar masalah sama)

### Bug #5 — `updateLowStockStatus()` setelah Restock
- **File**: `backend/app/Http/Controllers/Api/ProductController.php`
- **Ubah**: Tambah `$product->updateLowStockStatus()` setelah `addStock()` berhasil

### Bug #6 — Kelola Kategori: Add/Edit/Delete
- **File**: `src/pages/Inventory.vue`
- **Tambah**: Sub-modal "Kelola Kategori" dengan CRUD via `api/categories`

### Bug #7 — Kolom `tier` di Database
- **File Baru**: `backend/database/migrations/2026_05_21_..._add_tier_to_customers_table.php`
- **File Diupdate**: `backend/app/Models/Customer.php` (tambah `tier` ke fillable)
- **File Diupdate**: `backend/app/Http/Controllers/Api/CustomerController.php` (tambah validasi `tier`)
- **File Diupdate**: `src/pages/Pelanggan.vue` (fix null phone crash)

### Bug #8 — Halaman LunasHutang
- **File Baru**: `src/pages/LunasHutang.vue`
- **File Diupdate**: `src/router/index.js` (route `/pelanggan/:id/bayar`)
- **File Diupdate**: `src/pages/Pelanggan.vue` (`payDebt()` → `router.push(...)`)

### Bug #9 — Hapus Cetak Struk dari Laporan
- **File**: `src/pages/Laporan.vue`
- **Ubah**: Hapus tombol "Cetak Struk" dan fungsi `reprintReceipt()`

### Bug #10 — Hapus Fitur Retur
- **File Dihapus**: `src/pages/Retur.vue`, `src/stores/return.js`, `src/services/ReturnService.js`
- **File Diupdate**: `src/pages/Laporan.vue` (hapus kolom Aksi, modal Retur, fungsi `submitRetur`)
- **File Diupdate**: `src/router/index.js` (hapus route `/retur`)

### Bug #11 — Tombol Laporan Seragam Hijau
- **File**: `src/pages/Laporan.vue`
- **Ubah**: Semua tombol header (`Kembali`, `Detail Penjualan`, `Simpan Laporan Harian`, `Export Excel`) pakai class `.btn-green`

### Bug #12 & #14 — Export Excel Langsung Download
- **File Baru**: `src/utils/excelExport.js`
- **File Diupdate**: `src/pages/Laporan.vue` (import dan panggil fungsi export)

### Bug #13 — Simpan Laporan Harian = Download Excel
- **File**: `src/pages/Laporan.vue`
- **Ubah**: Setelah `saveDailyReport()` sukses → otomatis panggil `exportDailyReportExcel()`

### Bug #15 — Notifikasi: Dismiss per Item
- **File**: `src/components/shared/NotificationPanel.vue`
- **Tambah**: Tombol ✕ dismiss per notifikasi, localStorage persistence, emit `update-count`
- **File Diupdate**: `src/layouts/MainLayout.vue` (`fetchNotifCount` filter dismissed keys)

### Bug #16 — Nama Toko Real-Time Sync
- **File Diupdate**: `src/stores/ui.js` (tambah `shopName`, `setShopName`)
- **File Diupdate**: `src/layouts/MainLayout.vue` (`formattedShopName` computed, fetch settings di `onMounted`)
- **File Diupdate**: `src/pages/Pengaturan.vue` (panggil `uiStore.setShopName()` saat save)

### Bug #17 — Hapus License Card
- **File**: `src/pages/Pengaturan.vue`
- **Ubah**: Hapus seluruh blok HTML `.license-card` dan semua CSS-nya

### Bug #18 — Status Update Setelah Restock
- **File**: `backend/app/Models/Product.php`
- **Tambah**: Method `updateLowStockStatus()` — dipanggil di store, addStock, update, dan transaction

### Bug #19 — Indikator Stok Live Calculation
- **File**: `backend/app/Http/Controllers/Api/ProductController.php`
- **Logic**: `is_low_stock` di response = `total_stock <= min_stock` (real-time, bukan dari DB field)

### Bug #20 — Input Qty Langsung Ketikan di Kasir
- **File**: `src/pages/Kasir.vue`
- **Ubah**: `<span>{{ item.qty }}</span>` → `<input type="number" v-model.number="item.qty">` + `validateItemQty()`

### Bonus Bug A — Notification Header Invisible (Putih di Putih)
- **File**: `src/components/shared/NotificationPanel.vue`
- **Ubah**: `.notif-header { background: var(--color-sidebar-bg) }` → `background: var(--color-primary)`

### Bonus Bug B — Restock Button Invisible
- **File**: `src/components/shared/NotificationPanel.vue`
- **Ubah**: `.notif-action-btn { background: var(--color-sidebar-bg) }` → `background: var(--color-primary)`

### Bonus Bug C — Null Phone Crash di Filter Pelanggan
- **File**: `src/pages/Pelanggan.vue`
- **Ubah**: `c.phone.includes(search)` → `(c.phone || '').includes(search)`

---

## 9. Aturan Pengembangan (Rules)

### ✅ WAJIB Dilakukan
```
1. Styling hanya menggunakan CSS token dari src/style.css
2. Jangan hardcode warna hex langsung di komponen
3. Setiap perubahan di backend yang mengubah status produk HARUS memanggil
   $product->updateLowStockStatus()
4. Format uang Indonesia: .toLocaleString('id-ID')
5. Download Excel gunakan src/utils/excelExport.js (UTF-8 BOM + semicolon separator)
6. Notifikasi yang di-dismiss disimpan ke localStorage key: 'dismissed_notifications'
7. Key dismiss format: "${type}:${title}:${message}"
8. Setelah save nama toko di Pengaturan → wajib panggil uiStore.setShopName()
```

### ❌ DILARANG
```
1. Menggunakan Tailwind CSS atau Bootstrap
2. Menggunakan c.total_debt — field ini tidak ada, gunakan c.current_debt
3. Membuat route /retur — fitur ini sudah dihapus
4. Menampilkan License Card / Status Lisensi
5. Menampilkan tombol "Cetak Struk" di Laporan
6. Mengakses c.phone langsung tanpa null check (gunakan c.phone || '')
7. Menjadikan SKU required di backend validation
```

### Pola Kategori (Category Pattern)
```php
// Saat create/update produk:
if (!empty($validated['category_id'])) {
    // pakai category_id → ambil nama dari DB
} elseif (!empty($validated['category'])) {
    // cari atau buat category dari nama string
} else {
    // default 'Umum'
}
$product->updateLowStockStatus(); // selalu dipanggil!
```

### Pola Error Handling Axios (Frontend)
```js
try {
  const res = await axios.post('/endpoint', data)
  showToast('Berhasil!')
} catch (err) {
  showToast(err.response?.data?.message || 'Terjadi kesalahan', 'error')
}
```

---

## 10. Cara Menjalankan Lokal

### Setup Pertama Kali
```bash
# Clone / buka folder project
cd Toko_SE_Semester4

# Install frontend dependencies
npm install

# Setup backend
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed    # Buat tabel + data awal
cd ..
```

### Menjalankan Setiap Hari
```bash
# Terminal 1 — Backend API
cd backend
php artisan serve
# Berjalan di http://localhost:8000

# Terminal 2 — Frontend Dev Server
npm run dev
# Berjalan di http://localhost:5173
```

### Reset Database (Fresh)
```bash
cd backend
php artisan migrate:fresh --seed
```

### Build Production
```bash
npm run build
# Output di /dist (deploy ke hosting statis)
```

---

## 11. Skenario Pengujian Akademis

### ✅ Skenario 1: Login Multi-Role
1. Buka `http://localhost:5173`
2. Login sebagai **Kasir** → sidebar hanya tampil: Kasir, Pelanggan
3. Login sebagai **Admin** → sidebar hanya tampil: Inventory
4. Login sebagai **Owner** → sidebar tampil semua menu

### ✅ Skenario 2: Transaksi Kasir Tunai
1. Buka `/kasir`
2. Klik produk → masuk keranjang
3. **Ketik langsung jumlah** di field qty (bukan klik +/-)
4. Klik Bayar → isi nominal → Konfirmasi
5. Stok otomatis berkurang

### ✅ Skenario 3: Kasbon / Debt (Bug #4 fixed)
1. Di Kasir → klik Catat Kasbon
2. Ketik nama pelanggan yang **tidak ada** → sistem otomatis daftar pelanggan baru
3. Ketik nama pelanggan yang ada → sistem cek limit kredit
4. Jika melebihi limit → tombol konfirmasi disabled (terkunci)

### ✅ Skenario 4: Manajemen Inventaris & Kategori
1. Buka `/inventory`
2. Klik **Kelola Kategori** → tambah/edit/hapus kategori
3. Tambah produk baru → **kosongkan SKU** → sistem auto-generate
4. Restock → stok bertambah + status badge langsung update

### ✅ Skenario 5: Pelunasan Hutang (LunasHutang.vue)
1. Buka `/pelanggan`
2. Cari pelanggan yang punya hutang → klik ikon bayar
3. Redirect ke `/pelanggan/:id/bayar`
4. Lihat daftar nota hutang belum lunas
5. Isi jumlah bayar + tanggal → klik Bayar
6. Sisa hutang berkurang, status berubah jika lunas penuh

### ✅ Skenario 6: Laporan & Export Excel
1. Buka `/laporan`
2. Filter tanggal → data laporan berubah
3. Klik **Export Excel** → file `.csv` langsung terunduh (buka di Excel)
4. Klik **Detail Penjualan** → lihat breakdown per produk/kategori
5. Klik **Download Excel** di modal → file detail terunduh
6. Klik **Simpan Laporan Harian** → tersimpan ke DB + file Excel langsung terunduh

### ✅ Skenario 7: Notifikasi Dismissible
1. Klik ikon lonceng di header
2. Slide-in notification panel muncul dari kanan
3. Klik ✕ pada salah satu notifikasi → menghilang + badge count berkurang
4. Refresh halaman → notifikasi yang sudah di-dismiss tidak muncul lagi (localStorage)
5. Klik **HAPUS SEMUA** → semua notifikasi hilang

### ✅ Skenario 8: Nama Toko Real-Time Sync
1. Buka `/pengaturan`
2. Ganti nama toko → klik Simpan
3. Sidebar logo langsung berubah (tanpa refresh!)

### ✅ Skenario 9: Laporan Profit Terproteksi
1. Buka `/laporan`
2. HPP & Laba Bersih tampil dengan ikon 🔒
3. Klik Buka Akses → masukkan password owner
4. Data profit terbuka secara dinamis

### ✅ Skenario 10: Offline Mode
1. Matikan `php artisan serve` (simulasi backend mati)
2. Buka Kasir, lakukan transaksi
3. Toast "Koneksi Terputus" muncul, transaksi tersimpan offline (IndexedDB)
4. Hidupkan backend kembali → klik SYNC → data terkirim

---

*Blueprint ini dibuat otomatis oleh AI Developer pada **21 Mei 2026** setelah sesi bugfix komprehensif.*
*Untuk pertanyaan teknis, rujuk ke file masing-masing sesuai peta direktori di atas.*
