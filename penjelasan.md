# Pemetaan Analisis Proyek - Toko Sumber Makmur POS

---

## 1. Latar Belakang dan Tujuan Proyek

### Masalah Operasional

Toko Sumber Makmur merupakan toko retail sembako yang dalam operasional sehari-harinya menghadapi beberapa masalah fundamental:

- **Pencatatan transaksi manual** menggunakan buku tulis atau kertas struk, sehingga data rentan hilang, rusak, dan sulit diakses kembali untuk keperluan audit atau pelaporan.
- **Manajemen stok yang tidak akurat** - tidak ada pelacakan batch barang masuk, tidak ada sistem FIFO (First-In-First-Out), dan tidak ada otomatisasi penandaan produk yang stoknya menipis atau kosong.
- **Pencatatan hutang (kasbon) yang berantakan** - pelanggan yang membeli secara kredit (utang) dicatat manual tanpa batasan limit kredit, tanpa jatuh tempo, dan tanpa mekanisme pelunasan yang terstruktur.
- **Laporan keuangan yang tidak tersedia secara real-time** - pemilik toko tidak memiliki cara untuk melihat omset, laba bersih, atau penjualan per kategori produk secara instan.
- **Ketidakmampuan bekerja offline** - ketika koneksi internet terputus, seluruh aktivitas kasir terhenti.

### Tujuan Digitalisasi

Sistem POS ini dikembangkan sebagai **aplikasi desktop web berbasis Single Page Application (SPA)** dengan tujuan utama:

1. **Mengdigitalisasi seluruh aktivitas kasir** - dari pemindaian produk, pencatatan transaksi tunai maupun kasbon, hingga pencetakan struk.
2. **Mengotomasasi manajemen stok** menggunakan algoritma FIFO, sehingga batch tertua otomatis terpotong terlebih dahulu saat terjadi penjualan.
3. **Menyediakan mekanisme kredit terstruktur** - pelanggan memiliki batas kredit (debt_limit), dan sistem secara otomatis memvalidasi apakah transaksi kasbon masih dalam batas yang diizinkan.
4. **Menghasilkan laporan keuangan real-time** yang mencakup omset, HPP (Harga Pokok Penjualan), laba bersih, serta penjualan per produk/kategori/metode pembayaran.
5. **Mendukung ketahanan data offline** melalui IndexedDB, sehingga transaksi tetap dapat dicatat meskipun server backend sedang tidak tersedia, dan disinkronisasi secara otomatis saat koneksi pulih.
6. **Menerapkan otorisasi berbasis peran (RBAC)** - Owner, Admin Gudang, dan Kasir memiliki hak akses yang berbeda terhadap modul-modul yang berbeda pula.

---

## 2. Arsitektur dan Spesifikasi Teknologi

### Frontend

| Komponen | Teknologi | Versi | Fungsi |
|---|---|---|---|
| Framework UI | Vue 3 | 3.x | Composition API dengan script setup |
| Build Tool | Vite | 5.x | Dev server (port 5173), HMR, build produksi |
| State Management | Pinia | 2.x | 7 store: auth, ui, inventory, customer, transaction, debt, employee |
| Routing | Vue Router | 4.x | Client-side routing dengan auth guard berbasis role |
| HTTP Client | Axios | 1.x | Instance terkonfigurasi dengan token interceptor |
| Styling | Vanilla CSS | - | CSS Custom Properties di style.css |
| Offline Storage | IndexedDB | Browser API | Helper di utils/idb.js untuk antrean transaksi offline |

**Alasan Pemilihan Frontend:**
- Vue 3 Composition API memberikan reaktivitas yang lebih terstruktur dibanding Options API, sangat cocok untuk proyek akademis.
- Pinia dipilih karena merupakan rekomendasi resmi Vue 3 (pengganti Vuex).
- Vite dipilih karena kecepatan dev server yang jauh lebih cepat dibanding Webpack.
- Vanilla CSS dipilih untuk mendemonstrasikan pemahaman mendalam terhadap CSS Custom Properties, Flexbox, dan Grid.

### Backend

| Komponen | Teknologi | Fungsi |
|---|---|---|
| Framework | Laravel 11 | REST API dengan autentikasi Sanctum |
| Database | SQLite | File-based database di backend/database/database.sqlite |
| ORM | Eloquent | Model-model: Product, Customer, Transaction, Debt, dll. |
| Autentikasi | Laravel Sanctum | Token-based authentication, middleware auth:sanctum |

**Alasan Pemilihan Backend:**
- Laravel 11 merupakan framework PHP paling matang dan paling banyak digunakan di industri.
- SQLite dipilih karena zero-configuration, sangat ideal untuk demonstrasi akademis yang membutuhkan portabilitas tinggi.
- Sanctum dipilih karena terintegrasi natif dengan Laravel dan mendukung token-based auth yang cocok untuk SPA.

---

## 3. Struktur File dan Manajemen Folder

### Pohon Direktori Lengkap

```
Toko_SE_Semester4/
+- src/                          <- Vue 3 Frontend
|  +- App.vue                    <- Root component
|  +- main.js                    <- Entry point (createApp, router, pinia)
|  +- style.css                  <- Global CSS Design Tokens
|  +- pages/                     <- Route-Level Components (12 halaman)
|  |  +- Login.vue               <- /login
|  |  +- RoleSelection.vue       <- /select-role
|  |  +- Dashboard.vue           <- / (owner only)
|  |  +- Kasir.vue               <- /kasir (owner, kasir)
|  |  +- Inventory.vue           <- /inventory (owner, admin)
|  |  +- Pelanggan.vue           <- /pelanggan (owner, kasir)
|  |  +- LunasHutang.vue         <- /pelanggan/:id/bayar
|  |  +- Laporan.vue             <- /laporan (owner only)
|  |  +- Pengaturan.vue          <- /pengaturan (owner only)
|  |  +- Backup.vue              <- /backup (owner only)
|  |  +- PrintReceipt.vue        <- /print-receipt/:id (blank layout)
|  |  +- Karyawan.vue            <- /karyawan (owner only)
|  +- components/
|  |  +- modals/
|  |  |  +- DebtModal.vue        <- Modal kasbon di Kasir
|  |  |  +- PaymentModal.vue     <- Modal bayar tunai/QRIS
|  |  |  +- GantiUserModal.vue   <- Modal penggantian user
|  |  +- shared/
|  |     +- NotificationPanel.vue <- Slide-in notifikasi kanan
|  |     +- ToastNotification.vue <- Toast sukses/error
|  |     +- ReceiptPrint.vue     <- Komponen pencetakan struk (inline)
|  +- stores/                    <- Pinia State Management (7 stores)
|  |  +- auth.js                 <- Autentikasi, role, token, RBAC
|  |  +- ui.js                   <- shopName, searchQuery, sidebar state
|  |  +- inventory.js            <- Daftar produk, statistik stok
|  |  +- customer.js             <- Daftar pelanggan
|  |  +- transaction.js          <- Transaksi, antrean offline
|  |  +- debt.js                 <- Daftar hutang
|  |  +- employee.js             <- Daftar karyawan/user
|  +- services/                  <- Axios API Wrappers (9 services)
|  |  +- InventoryService.js
|  |  +- CustomerService.js
|  |  +- TransactionService.js
|  |  +- ReportService.js
|  |  +- SettingService.js
|  |  +- NotificationService.js
|  |  +- EmployeeService.js
|  |  +- BackupService.js
|  |  +- AuthService.js
|  +- utils/
|  |  +- idb.js                 <- IndexedDB helper (offline queue)
|  |  +- excelExport.js         <- Utilitas export CSV/Excel
|  +- plugins/
|  |  +- axios.js               <- Axios instance (base URL + token)
|  +- layouts/
|  |  +- MainLayout.vue         <- Sidebar + Header + Content wrapper
|  +- router/
|     +- index.js               <- Vue Router + Auth Guard + RBAC

+- backend/                       <- Laravel 11 Backend
|  +- app/
|  |  +- Models/                 <- 14 Eloquent Models
|  |  |  +- Product.php          <- updateLowStockStatus()
|  |  |  +- Customer.php         <- tier di fillable
|  |  |  +- Transaction.php      <- cash_paid di fillable
|  |  |  +- TransactionItem.php
|  |  |  +- StockBatch.php
|  |  |  +- StockLog.php
|  |  |  +- Debt.php
|  |  |  +- DebtPayment.php
|  |  |  +- Category.php
|  |  |  +- Supplier.php
|  |  |  +- Setting.php
|  |  |  +- FinancialReport.php
|  |  |  +- User.php
|  |  |  +- TransactionReturn.php
|  |  +- Http/Controllers/Api/  <- 14 API Controllers
|  |     +- AuthController.php
|  |     +- DashboardController.php
|  |     +- ProductController.php
|  |     +- CustomerController.php
|  |     +- TransactionController.php
|  |     +- DebtController.php
|  |     +- CategoryController.php
|  |     +- SupplierController.php
|  |     +- NotificationController.php
|  |     +- FinancialReportController.php
|  |     +- SettingController.php
|  |     +- BackupController.php
|  |     +- UserController.php
|  |     +- TransactionReturnController.php
|  +- database/
|  |  +- migrations/             <- 24 migration files
|  |  +- seeders/
|  |  |  +- DatabaseSeeder.php
|  |  |  +- ProductSeeder.php
|  |  |  +- UserSeeder.php
|  |  |  +- CustomerSeeder.php
|  |  +- database.sqlite         <- File database SQLite
|  +- routes/
|     +- api.php                 <- Semua route API
```

### Fungsi File-file Kritis

| File | Fungsi |
|---|---|
| **MainLayout.vue** | Layout utama: sidebar RBAC, topheader search, notifikasi, dropdown user. Fetch nama toko dari API saat mount. Badge offline hanya muncul di halaman Kasir saat `transactionStore.isOfflineMode = true`. |
| **router/index.js** | Route definitions + beforeEach guard: cek publik, autentikasi, role selection, role permissions. |
| **idb.js** | Helper IndexedDB: initDB(), saveOfflineTransaction(), getOfflineTransactions(), clearOfflineTransaction(). |
| **excelExport.js** | Export CSV dengan UTF-8 BOM + separator titik koma. 3 fungsi: exportTransactions, exportDaily, exportDetailed. |
| **Product.php** | updateLowStockStatus(): hitung total stok dari batch, bandingkan dengan min_stock. |
| **Customer.php** | Fillable: name, phone, address, tier, debt_limit, current_debt, is_active. Relasi hasMany Transaction dan Debt. |
| **Transaction.php** | Fillable termasuk `cash_paid`. Relasi belongsTo User/Customer, hasMany TransactionItem. |
| **ui.js** | Pinia store: shopName (reactive). setShopName() dipanggil saat save Pengaturan. |

---

## 4. Skema Database dan Relasi

### Seluruh Tabel

#### Tabel users (Laravel Default)
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | Auto-increment |
| name | string | Nama user |
| username | string, unique | Username untuk login |
| password | string | Hashed password |
| role | string | owner, admin, kasir |
| last_login | timestamp, nullable | Waktu login terakhir |
| timestamps | - | created_at, updated_at |

> Catatan: Login menggunakan **username** (bukan email). Field last_login di-update setiap kali login berhasil.

#### Tabel products
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | Auto-increment |
| name | string | Nama produk |
| sku | string, unique | Kode SKU (auto-generate jika kosong) |
| category | string | Nama kategori (text field, diisi otomatis dari category_id) |
| category_id | bigint FK | Foreign key ke categories.id |
| supplier_id | bigint FK | Foreign key ke suppliers.id |
| unit | string | Satuan (pcs, kg, liter, dll.) |
| min_stock | integer | Batas minimum stok |
| is_low_stock | boolean | Flag stok menipis (diupdate otomatis) |
| base_buy_price | decimal | Harga beli dasar |
| base_sell_price | decimal | Harga jual dasar |
| description | text, nullable | Deskripsi produk |
| image | string, nullable | Path gambar |
| timestamps | - | created_at, updated_at |

#### Tabel stock_batches
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | Auto-increment |
| product_id | bigint FK | Foreign key ke products.id |
| batch_number | string | Nomor batch (wajib) |
| qty | integer | Kuantitas awal saat batch dibuat |
| current_qty | integer | Sisa kuantitas saat ini |
| buy_price | decimal | Harga beli batch ini |
| sell_price | decimal | Harga jual batch ini |
| expired_date | date, nullable | Tanggal kedaluwarsa |
| rack_location | string, nullable | Lokasi rak penyimpanan |
| timestamps | - | created_at, updated_at |

#### Tabel stock_logs
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | Auto-increment |
| stock_batch_id | bigint FK | Foreign key ke stock_batches.id |
| type | string | RESTOCK, SALE, RETURN, ADJUSTMENT |
| quantity | integer | Positif untuk masuk, negatif untuk keluar |
| reference_id | string, nullable | ID referensi (misal: transaction ID) |
| user_id | bigint FK | User yang melakukan aksi |
| notes | text, nullable | Catatan |
| timestamps | - | created_at, updated_at |

#### Tabel categories
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | Auto-increment |
| name | string, unique | Nama kategori |
| description | text, nullable | Deskripsi kategori |
| timestamps | - | created_at, updated_at |

#### Tabel customers
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | Auto-increment |
| name | string | Nama pelanggan |
| phone | string, nullable | Nomor telepon |
| address | text, nullable | Alamat |
| tier | string | Level pelanggan (default: New Customer) |
| debt_limit | decimal | Batas kredit maksimum (default: Rp 5.000.000) |
| current_debt | decimal | Total hutang aktif saat ini (default: 0) |
| is_active | boolean | Status aktif pelanggan |
| timestamps | - | created_at, updated_at |

#### Tabel transactions
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | Auto-increment |
| user_id | bigint FK | Foreign key ke users.id |
| customer_id | bigint FK, nullable | Foreign key ke customers.id |
| payment_method | string | cash, transfer, debt |
| total_amount | decimal | Total nominal transaksi |
| cash_paid | decimal, nullable | Jumlah uang yang dibayarkan (untuk kalkulasi kembalian) |
| status | string | completed |
| transaction_date | datetime | Waktu transaksi |
| notes | text, nullable | Catatan transaksi |
| timestamps | - | created_at, updated_at |

> Catatan: Field `cash_paid` digunakan untuk menghitung kembalian pada struk. Hanya diisi untuk metode pembayaran tunai.

#### Tabel transaction_items
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | Auto-increment |
| transaction_id | bigint FK | Foreign key ke transactions.id |
| product_id | bigint FK | Foreign key ke products.id |
| qty | integer | Kuantitas terjual |
| price | decimal | Harga jual per unit |
| cost_price | decimal | Harga beli rata-rata (dihitung dari FIFO batch) |
| subtotal | decimal | qty x price |
| timestamps | - | created_at, updated_at |

#### Tabel debts
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | Auto-increment |
| customer_id | bigint FK | Foreign key ke customers.id |
| transaction_id | bigint FK | Foreign key ke transactions.id |
| total_amount | decimal | Total jumlah hutang |
| remaining_amount | decimal | Sisa hutang yang belum dibayar |
| status | string | unpaid, paid |
| due_date | date | Tanggal jatuh tempo (default: 30 hari) |
| notes | text, nullable | Catatan |
| timestamps | - | created_at, updated_at |

#### Tabel debt_payments
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | Auto-increment |
| debt_id | bigint FK | Foreign key ke debts.id |
| amount | decimal | Jumlah pembayaran |
| payment_date | date | Tanggal pembayaran |
| note | text, nullable | Catatan pembayaran |
| timestamps | - | created_at, updated_at |

#### Tabel settings
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | Auto-increment |
| key | string, unique | Kunci pengaturan |
| value | text | Nilai pengaturan |
| timestamps | - | created_at, updated_at |

#### Tabel financial_reports
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | Auto-increment |
| report_date | date, unique | Tanggal laporan |
| total_transactions | integer | Jumlah transaksi |
| total_revenue | decimal | Total pemasukan |
| total_cost | decimal | Total HPP |
| total_profit | decimal | Laba bersih |
| total_debt_payments | decimal | Total pelunasan piutang |
| new_debt_amount | decimal | Hutang baru terbentuk |
| expense_amount | decimal | Pengeluaran operasional |
| net_income | decimal | Laba bersih |
| timestamps | - | created_at, updated_at |

#### Tabel transaction_returns
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | Auto-increment |
| transaction_id | bigint FK | Foreign key ke transactions.id (cascade delete) |
| transaction_item_id | bigint FK | Foreign key ke transaction_items.id (cascade delete) |
| user_id | bigint FK | Foreign key ke users.id (cascade delete) |
| qty | integer | Kuantitas yang diretur |
| reason | text, nullable | Alasan retur |
| status | enum | pending, approved, rejected (default: pending) |
| timestamps | - | created_at, updated_at |

### Penjelasan Relasi Kritis

| Relasi | Tipe | Penjelasan |
|---|---|---|
| Product -> StockBatch | hasMany | Satu produk memiliki banyak batch stok |
| StockBatch -> StockLog | hasMany | Setiap batch memiliki catatan log pergerakan stok |
| Product -> TransactionItem | hasMany | Satu produk bisa muncul di banyak item transaksi |
| Customer -> Transaction | hasMany | Satu pelanggan bisa memiliki banyak transaksi |
| Customer -> Debt | hasMany | Satu pelanggan bisa memiliki banyak catatan hutang |
| Transaction -> Debt | hasOne | Satu transaksi kasbon menghasilkan satu catatan hutang |
| Debt -> DebtPayment | hasMany | Satu catatan hutang bisa memiliki banyak pembayaran |
| Transaction -> TransactionItem | hasMany | Satu transaksi berisi satu atau lebih item produk |
| Product -> Category | belongsTo | Setiap produk terkait dengan satu kategori |
| Transaction -> User | belongsTo | Setiap transaksi dilakukan oleh satu user |
| TransactionReturn -> Transaction | belongsTo | Setiap retur terkait dengan satu transaksi |
| TransactionReturn -> TransactionItem | belongsTo | Setiap retur terkait dengan satu item transaksi |

---

## 5. Fitur Utama dan Aturan Bisnis Teknis

### A. Modul Kasir - Input Kuantitas Langsung dan Validasi Kredit Kasbon

**Mekanisme Input Kuantitas:**
Di halaman Kasir.vue, setiap item di keranjang memiliki elemen `<input type="number">` yang terikat secara two-way dengan `v-model.number="item.qty"`. Setiap perubahan nilai memicu fungsi `validateItemQty(item)` yang memastikan kuantitas minimal bernilai 1.

```javascript
function validateItemQty(item) {
  if (!item.qty || isNaN(item.qty) || item.qty < 1) {
    item.qty = 1
  }
}
```

**Validasi Batas Kredit Kasbon (DebtModal.vue):**
1. Pengguna mengetik nama pelanggan -> dropdown muncul menampilkan max 5 hasil pencarian.
2. Computed property `overLimit` mengecek:
```javascript
const overLimit = computed(() => {
  const c = customerStore.customers.find(c => c.name.toLowerCase() === customerName.value.toLowerCase())
  if (!c) return false
  const limit = c.debt_limit || defaultDebtLimit
  return (c.current_debt + props.total) > limit
})
```
3. Jika melebihi limit: peringatan merah muncul, tombol Simpan Kasbon disabled.
4. Backend juga melakukan validasi yang sama sebagai lapisan keamanan.

### B. Antrean Sinkronisasi Offline - IndexedDB

**Arsitektur:** File src/utils/idb.js mengelola database IndexedDB `TokoSumberMakmurDB` v1 dengan object store `offline_transactions`.

**Alur Kerja:**
1. **Transaksi Online Normal**: submitTransaction() -> TransactionService.create() -> backend -> sukses -> keranjang dikosongkan.
2. **Backend Tidak Tersedia**: Error ERR_NETWORK -> isOfflineMode = true -> saveOfflineTransaction() -> IndexedDB -> toast offline.
3. **Sinkronisasi**: Tombol SYNC (n) -> syncOfflineTransactions() -> kirim satu per satu -> clearOfflineTransaction() -> refresh.

**Indikator Visual:**
- Tombol SYNC (n) berwarna kuning di header kasir ketika offlineQueueCount > 0
- Badge "Offline Mode Active" di topbar **hanya muncul di halaman Kasir** saat `transactionStore.isOfflineMode = true`

### C. Manajemen Stok - Algoritma FIFO

**Implementasi di Backend (TransactionController::store()):**
```
Untuk setiap item dalam transaksi:
1. Query batch: product_id, current_qty > 0, expired_date > now(), ORDER BY expired_date ASC
2. Iterasi: deduct = MIN(batch.current_qty, qtyToReduce)
   - Kurangi batch.current_qty sejumlah deduct
   - Catat StockLog type=SALE, quantity negatif
   - totalCostForItem += deduct * batch.buy_price
3. cost_price = totalCostForItem / item.qty
4. Simpan TransactionItem
5. Panggil product.updateLowStockStatus()
```

**Pembaruan Status Stok (Product::updateLowStockStatus()):**
```php
public function updateLowStockStatus()
{
    $totalStock = $this->getTotalStockAttribute();
    $this->is_low_stock = ($totalStock <= $this->min_stock);
    $this->save();
}
```
Method ini dipanggil di: ProductController::store(), addStock(), update(), dan TransactionController::store().

### D. Sinkronisasi Nama Toko Real-Time

1. Pinia Store ui.js menyimpan state reaktif `shopName`.
2. MainLayout.vue membaca dari store: `formattedShopName` computed.
3. Saat mount, fetch GET /api/settings -> ui.setShopName().
4. Saat save di Pengaturan.vue: uiStore.setShopName() -> sidebar langsung berubah tanpa refresh.

### E. Manajemen Pelunasan Hutang (LunasHutang.vue)

**Route**: /pelanggan/:id/bayar

1. Fetch GET /api/customers/:id -> data pelanggan.
2. Fetch GET /api/debts?customer_id=:id&status=unpaid -> daftar hutang belum lunas.
3. Form per hutang: jumlah bayar (default: remaining_amount), tanggal, catatan.
4. Submit: POST /api/debts/:debtId/pay -> DebtPayment dibuat, remaining_amount dikurangi, status=paid jika lunas, customer.current_debt dikurangi.

### F. Fitur Ekspor Laporan - CSV UTF-8 BOM

**Teknik:**
1. **UTF-8 BOM** (\uFEFF): Mencegah karakter Indonesia terbaca sampah di Excel.
2. **Pemisah Titik Koma** (;): Excel regional Indonesia menggunakan ; sebagai delimiter.
3. **toLocaleString('id-ID')**: Format angka Indonesia (titik ribuan, koma desimal).

**3 Jenis Export:**
1. exportTransactionsExcel() - Daftar transaksi: ID, tanggal, keterangan, pelanggan, metode, nominal.
2. exportDailyReportExcel() - Laporan harian: transaksi, pemasukan, pelunasan, hutang baru, laba bersih.
3. exportDetailedReportExcel() - Detail: penjualan per produk, per kategori, per metode pembayaran.

### G. Fitur Retur Transaksi (TransactionReturn)

Fitur retur diimplementasi ulang menggunakan `TransactionReturn` model dan `TransactionReturnController`.

**Alur Kerja:**
1. User memilih item transaksi yang akan diretur.
2. Input kuantitas retur dan alasan.
3. Backend memvalidasi: kuantitas retur tidak melebihi kuantitas yang dibeli dikurangi retur sebelumnya.
4. Record `TransactionReturn` dibuat dengan status `approved` (auto-approve).
5. Stok dikembalikan melalui pembuatan batch baru (`RET-{tanggal}-{id}`).
6. StockLog dicatat dengan type `RETURN`.

> Catatan: Route `/api/transaction-returns` belum didaftarkan di `routes/api.php`. Controller dan model sudah ada tapi fitur belum aktif.

### H. Mekanisme Autentikasi dan Otorisasi (RBAC)

**Proses Login (AuthController::login()):**
1. Frontend mengirim: { username, password, role } - login menggunakan username (bukan email).
2. Backend: User::where('username', $request->username) -> Hash::check().
3. Validasi role: $user->role !== $request->role -> error.
4. Update last_login, generate Sanctum token.
5. Response: { access_token, token_type: 'Bearer', user }.

**Router Guard (beforeEach):**
1. Route publik? -> Lanjut
2. Belum autentikasi? -> /select-role
3. Role belum dipilih? -> /select-role
4. Role tidak punya akses? -> Redirect ke halaman default role

**Peta Akses per Role:**
| Route | Owner | Admin | Kasir |
|---|---|---|---|
| / (Dashboard) | YES | NO | NO |
| /kasir | YES | NO | YES |
| /inventory | YES | YES | NO |
| /pelanggan | YES | NO | YES |
| /pelanggan/:id/bayar | YES | NO | YES |
| /laporan | YES | NO | NO |
| /pengaturan | YES | NO | NO |
| /backup | YES | NO | NO |
| /karyawan | YES | NO | NO |

### I. Dashboard Analytics

**Endpoint**: GET /api/dashboard/stats

| Field | Deskripsi | Kalkulasi |
|---|---|---|
| sales_today | Total omset hari ini | SUM(total_amount) |
| profit_today | Laba bersih hari ini | sales_today - SUM(cost_price x qty) |
| net_margin | Margin bersih (%) | (profit_today / sales_today) x 100 |
| transaction_count_today | Jumlah transaksi | COUNT(*) |
| low_stock_count | Produk stok menipis | sum(current_qty) <= min_stock |
| total_pending_debt | Hutang tertunda | SUM(remaining_amount) unpaid |
| chart_data | Grafik 8 bulan | Array { day, amount, profit } |
| recent_transactions | 5 transaksi terbaru | latest()->take(5) |

**Tampilan**: 4 kartu stat, grafik bar CSS, SVG growth chart, expiry alert banner.

### J. Sistem Notifikasi (3 Tipe)

| Tipe | Kondisi | Severity |
|---|---|---|
| LOW_STOCK | sum(current_qty) <= min_stock (jika min_stock=0, pakai global threshold default 10) | high (stok=0), medium (>0) |
| EXPIRY | expired_date <= now()+30 hari | high (<=7 hari), medium (8-30) |
| OVERDUE_DEBT | status=unpaid AND due_date < now() | high selalu |

**Dismiss Mechanism**: localStorage key `dismissed_notifications`, format `type:title:message`. Poll 90 detik.

### K. Sistem Backup dan Restore

**Backup**: GET /api/backup -> download database.sqlite
**Restore**: POST /api/restore -> upload .sqlite, safety backup ke .bak, replace DB, reload halaman.

### L. Modul Pembayaran (PaymentModal)

**3 Metode**: Tunai (input jumlah + kalkulasi kembalian), QRIS (placeholder), Transfer (nominal otomatis).
**Quick Cash**: Tombol cepat dihitung: total, bulatkan ke 10rb, 50rb, 100rb ke atas. Max 4 tombol.
**Pemetaan**: Frontend tunai->cash, qris/transfer->transfer. Backend hanya terima cash dan transfer.
**Kembalian**: Dihitung dari `cash_paid - total_amount` dan ditampilkan di struk.

### M. Safety Checks pada Operasi Hapus

**Produk**: Tidak bisa dihapus jika masih ada stok (current_qty > 0). Tombol hapus tersedia di halaman Inventory.
**Pelanggan**: Tidak bisa dihapus jika masih memiliki hutang (current_debt > 0).

### N. Alur Cetak Struk

**Komponen**: ReceiptPrint.vue (inline, untuk fallback offline) + PrintReceipt.vue (halaman terpisah, blank layout).
**Alur**: Transaksi sukses -> redirect /print-receipt/:id?autoprint=true -> window.print().
**Offline**: Struk dicetak dari data keranjang lokal via setTimeout(window.print, 150).
**Kembalian**: Dihitung dari `tx.cash_paid - tx.total_amount` (field `cash_paid` disimpan di database).

### O. Fitur Lengkap Direktori Pelanggan

**Summary Cards**: Total Piutang, Total Pelanggan, Pembayaran Tertunda.
**Fitur**: Search (nama/telepon/status), Filter Status (Semua/Lunas/Tertunda/Jatuh Tempo), Tambah Pelanggan (modal), Aksi Bayar (redirect ke LunasHutang).

### P. Detail Fitur Laporan Keuangan

**3 Kartu**: Total Pemasukan (terbuka), HPP (terkunci), Laba Bersih (terkunci + Buka Akses).
**Proteksi**: Password via Hash::check(). HPP = SUM(qty x cost_price). Laba = Pemasukan - HPP.
**Filter**: Tanggal, metode pembayaran, search ID transaksi.
**Paginasi**: 15 item per halaman, navigasi halaman dengan tombol prev/next/page.
**Aksi**: Kembali, Detail Penjualan, Simpan Laporan Harian (POST + download Excel), Export Excel.

### Q. Fitur Pengaturan Sistem

**Endpoint**: GET/POST /api/settings (key-value store).
**Defaults**: shop_name='Toko Sumber Makmur', shop_address, shop_phone, currency='IDR', low_stock_threshold=10.
**Flow**: Save -> Setting::updateOrCreate() + uiStore.setShopName() -> sidebar real-time update.
**Sync Badge**: Setelah save berhasil, badge "Sistem Sinkron" muncul selama 3 detik di pojok kanan bawah.
**Logo Upload**: Input file image dengan validasi max 2MB, preview ditampilkan setelah pemilihan.

### R. Fitur Hapus Produk

**Endpoint**: DELETE /api/products/:id
**Alur**: Tombol hapus (ikon trash merah) di tabel Inventory -> konfirmasi dialog -> produk dihapus jika stok = 0.
**Safety Check**: Backend memeriksa `current_qty > 0` sebelum mengizinkan penghapusan.

### S. Fitur Paginasi Laporan

**Implementasi**: Client-side pagination di `Laporan.vue`.
- `perPage = 15` item per halaman
- `currentPage` ref, `totalPages` computed, `paginatedTransactions` computed
- Tombol navigasi: prev, page numbers (max 5 visible), next
- Reset ke halaman 1 saat filter berubah

---

## 6. Rekam Jejak Pembersihan Kode / Bugfix

### Bugfix 21 Mei 2026 (20 masalah)

| No | Masalah | File | Perubahan |
|---|---|---|---|
| 1 | total_debt tidak ada di database | DebtModal.vue | c.total_debt -> c.current_debt |
| 2 | SKU wajib diisi | ProductController.php | sku: required -> nullable, auto-generate |
| 3 | Kategori hardcoded | Inventory.vue | Array statis -> computed dari GET /api/categories |
| 4 | Kasbon gagal (akar: Bug #1) | DebtModal.vue | Field total_debt tidak ditemukan |
| 5 | Status stok tidak update setelah restock | ProductController.php | Tambah updateLowStockStatus() |
| 6 | Kategori tidak bisa dikelola | Inventory.vue | Sub-modal Kelola Kategori CRUD |
| 7 | Kolom tier belum ada | Migration baru + Customer.php + CustomerController.php | Tambah tier |
| 8 | Tidak ada halaman pelunasan hutang | LunasHutang.vue (baru) + router + Pelanggan.vue | Halaman baru + route |
| 9 | Tombol cetak struk di laporan | Laporan.vue | Hapus tombol + fungsi reprintReceipt() |
| 10 | Fitur retur diimplementasi ulang | TransactionReturn model + controller | Retur baru dengan auto-approve |
| 11 | Tombol header laporan warna tidak seragam | Laporan.vue | Semua pakai class .btn-green |
| 12+14 | Export Excel tidak langsung download | excelExport.js (baru) + Laporan.vue | Utilitas CSV UTF-8 BOM |
| 13 | Simpan laporan harian tanpa file | Laporan.vue | Setelah save -> exportDailyReportExcel() |
| 15 | Notifikasi tidak bisa di-dismiss | NotificationPanel.vue + MainLayout.vue | Tombol dismiss + localStorage |
| 16 | Nama toko tidak sinkron | ui.js + MainLayout.vue + Pengaturan.vue | shopName reactive + setShopName() |
| 17 | License card tidak relevan | Pengaturan.vue | Hapus blok HTML + CSS |
| 18 | Status stok tidak update saat restock | Product.php | Tambah method updateLowStockStatus() |
| 19 | Indikator stok tidak akurat | ProductController.php | is_low_stock real-time calculation |
| 20 | Qty harus diklik +/- | Kasir.vue | span -> input type=number + validateItemQty() |

**Bug Bonus:**
| No | Masalah | Perubahan |
|---|---|---|
| A | Header notifikasi putih di atas putih | background: var(--color-sidebar-bg) -> var(--color-primary) |
| B | Tombol restock tidak terlihat | background: var(--color-sidebar-bg) -> var(--color-primary) |
| C | Null phone crash | c.phone.includes -> (c.phone || '').includes |

### Bugfix 4 Juni 2026 (8 perbaikan)

| No | Masalah | File | Perubahan |
|---|---|---|---|
| 1 | Logo Upload tidak berfungsi | Pengaturan.vue | Tambah `@change="handleLogoUpload"` + fungsi handler dengan validasi 2MB |
| 2 | Pagination Laporan non-fungsi | Laporan.vue | Implementasi client-side pagination (15 item/halaman) |
| 3 | Sync Badge tidak pernah muncul | Pengaturan.vue | `showSyncBadge = true` setelah saveAll(), auto-hide 3 detik |
| 4 | Offline Mode Indicator selalu tampil | MainLayout.vue | Badge hanya muncul jika `transactionStore.isOfflineMode = true` |
| 5 | PrintReceipt kembalian hardcode Rp 0 | PrintReceipt.vue + TransactionController | Tambah kolom `cash_paid`, kalkulasi change dari `cash_paid - total_amount` |
| 6 | Tidak ada tombol hapus produk | Inventory.vue + InventoryService.js + inventory.js | Tambah `deleteProduct()` action, tombol trash merah |
| 7 | TransactionReturnController kolom salah | TransactionReturnController.php | `initial_qty` -> `qty`, hapus `status`/`supplier_id` |
| 8 | Low Stock Threshold Global tidak dipakai | NotificationController.php | Jika `min_stock == 0`, pakai global threshold dari settings |

**Bug Kode (diperbaiki):**
| No | Masalah | File | Perubahan |
|---|---|---|---|
| D | Transaction $fillable missing cash_paid | Transaction.php | Tambah `cash_paid` ke array fillable |

---

## 7. Skenario Pengujian Akademis

### Skenario 1: Login Multi-Role
| Langkah | Aksi | Hasil |
|---|---|---|
| 1 | Buka localhost:5173 | RoleSelection muncul, 3 opsi |
| 2 | Login Kasir (kasir1/password123) | Sidebar: Kasir, Pelanggan |
| 3 | Login Admin (admin/password123) | Sidebar: Stok Barang |
| 4 | Login Owner (owner/password123) | Sidebar: Semua menu |

### Skenario 2: Transaksi Kasir Tunai
| Langkah | Aksi | Hasil |
|---|---|---|
| 1 | Buka /kasir | Katalog produk per kategori |
| 2 | Klik produk | Masuk keranjang, qty=1 |
| 3 | Ketik qty langsung (contoh: 5) | Qty=5, total update |
| 4 | Klik BAYAR (F10) | PaymentModal muncul |
| 5 | Tunai, isi nominal, Konfirmasi | Transaksi tersimpan, stok berkurang, redirect PrintReceipt |
| 6 | Perhatikan struk | KEMBALIAN = cash_paid - total_amount |
| 7 | Buka /inventory | Stok sudah berkurang |

### Skenario 3: Pencatatan Kasbon
| Langkah | Aksi | Hasil |
|---|---|---|
| 1 | Tambah produk ke keranjang | Keranjang berisi item |
| 2 | Klik CATAT UTANG | DebtModal terbuka |
| 3 | Ketik nama pelanggan baru | Tidak ada dropdown |
| 4 | Ketik nama pelanggan ada | Dropdown + info hutang |
| 5 | Melebihi limit | Peringatan merah, tombol disabled |
| 6 | Dalam limit, Simpan Kasbon | Transaksi tersimpan, Debt baru, current_debt += total |

### Skenario 4: Manajemen Inventaris dan Kategori
| Langkah | Aksi | Hasil |
|---|---|---|
| 1 | Buka /inventory | Daftar produk + indikator stok |
| 2 | Kelola Kategori | CRUD kategori berfungsi |
| 3 | Tambah Produk, kosongkan SKU | SKU auto-generate |
| 4 | Restock produk | Stok bertambah, badge update |
| 5 | Klik tombol hapus (trash) | Konfirmasi dialog muncul |
| 6 | Konfirmasi hapus (stok=0) | Produk terhapus dari daftar |
| 7 | Coba hapus produk stok>0 | Error: "Produk tidak dapat dihapus karena masih ada stok" |

### Skenario 5: Pelunasan Hutang
| Langkah | Aksi | Hasil |
|---|---|---|
| 1 | Buka /pelanggan | Daftar pelanggan |
| 2 | Klik bayar pada pelanggan berhutang | Redirect ke LunasHutang |
| 3 | Lihat nota hutang | Jumlah awal, sisa, jatuh tempo |
| 4 | Isi jumlah bayar, klik Bayar | remaining_amount berkurang, status=paid jika lunas |

### Skenario 6: Laporan dan Export Excel
| Langkah | Aksi | Hasil |
|---|---|---|
| 1 | Buka /laporan | Ringkasan: Pemasukan, HPP (locked), Laba (locked) |
| 2 | Filter tanggal | Data ter-filter |
| 3 | Export Excel | File CSV terunduh, kompatibel Excel ID |
| 4 | Detail Penjualan | Breakdown per produk/kategori/metode |
| 5 | Simpan Laporan Harian | Tersimpan ke DB + Excel terunduh |
| 6 | Navigasi halaman (paginasi) | Halaman berubah, filter reset ke halaman 1 |

### Skenario 7: Notifikasi Dismissible
| Langkah | Aksi | Hasil |
|---|---|---|
| 1 | Klik lonceng | Panel notifikasi slide-in |
| 2 | Klik dismiss (X) | Notifikasi hilang, badge berkurang |
| 3 | Refresh (F5) | Notif dismissed tidak muncul lagi |
| 4 | HAPUS SEMUA | Semua notif hilang |

### Skenario 8: Sinkronisasi Nama Toko Real-Time
| Langkah | Aksi | Hasil |
|---|---|---|
| 1 | Buka /pengaturan | Form nama toko |
| 2 | Ganti nama, Simpan | Tersimpan ke API |
| 3 | Perhatikan sidebar | Nama toko berubah tanpa refresh |
| 4 | Perhatikan pojok kanan bawah | Badge "Sistem Sinkron" muncul 3 detik |

### Skenario 9: Laporan Profit Terproteksi
| Langkah | Aksi | Hasil |
|---|---|---|
| 1 | Buka /laporan | HPP + Laba = Terkunci |
| 2 | Klik Buka Akses | Modal password |
| 3 | Masukkan password owner | Data profit terbuka |
| 4 | Password salah | Error: Password laporan salah |

### Skenario 10: Simulasi Mode Offline
| Langkah | Aksi | Hasil |
|---|---|---|
| 1 | Matikan php artisan serve | Backend mati |
| 2 | Transaksi di kasir | Tersimpan ke IndexedDB |
| 3 | Perhatikan header kasir | Tombol SYNC (1) kuning muncul |
| 4 | Perhatikan topbar | Badge "Offline Mode Active" muncul |
| 5 | Nyalakan backend | Backend tersedia |
| 6 | Klik SYNC | Data terkirim, tombol hilang |
| 7 | Buka /laporan | Transaksi tercatat |

### Skenario 11: Upload Logo (Pengaturan)
| Langkah | Aksi | Hasil |
|---|---|---|
| 1 | Buka /pengaturan | Area upload logo terlihat |
| 2 | Klik area upload | File picker terbuka |
| 3 | Pilih file > 2MB | Error: "Ukuran file maksimal 2MB" |
| 4 | Pilih file <= 2MB | Preview logo muncul, toast sukses |

---

## 8. Daftar Lengkap API Endpoint

### Auth
| Method | Endpoint | Body | Response |
|---|---|---|---|
| POST | /api/login | {username, password, role} | {access_token, user} |
| POST | /api/logout | - | {message} |
| GET | /api/me | - | {user} |

### Products
| Method | Endpoint | Body | Notes |
|---|---|---|---|
| GET | /api/products | - | total_stock, is_low_stock, batches |
| POST | /api/products | {name*, unit*, min_stock*, sku?, category?, category_id?, base_buy_price?, base_sell_price?, initial_stock?} | SKU auto-gen |
| PUT | /api/products/:id | {name?, sku?, category?, unit?, min_stock?} | updateLowStockStatus() |
| DELETE | /api/products/:id | - | Gagal jika stok > 0 |
| POST | /api/products/:id/stock | {batch_number*, qty*, buy_price*, sell_price*, expired_date?, rack_location?} | updateLowStockStatus() |
| GET | /api/products/:id/history | - | Stock logs |

### Categories
| Method | Endpoint | Body |
|---|---|---|
| GET/POST/PUT/DELETE | /api/categories | {name*} |

### Customers
| Method | Endpoint | Body | Notes |
|---|---|---|---|
| GET | /api/customers | - | Semua pelanggan |
| POST | /api/customers | {name*, ...} | debt_limit default 5.000.000 |
| PUT | /api/customers/:id | {name?, ...} | - |
| DELETE | /api/customers/:id | - | Gagal jika current_debt > 0 |
| GET | /api/customers/:id | - | Include debts + transactions |

### Transactions
| Method | Endpoint | Body | Notes |
|---|---|---|---|
| GET | /api/transactions | ?start_date&end_date&payment_method&customer_id&search | Filter |
| POST | /api/transactions | {items[], payment_method, customer_id?, cash_paid?, notes?, due_date?} | FIFO reduction, cash_paid untuk kalkulasi kembalian |
| GET | /api/transactions/:id | - | Include items.product |

### Debts
| Method | Endpoint | Body | Notes |
|---|---|---|---|
| GET | /api/debts | ?customer_id&status | unpaid|paid |
| GET | /api/debts/:id | - | Include payments |
| POST | /api/debts/:id/pay | {amount*, payment_date*, note?} | Decrement current_debt |

### Reports
| Method | Endpoint | Notes |
|---|---|---|
| POST | /api/reports/profit | Password required |
| POST | /api/reports/detailed | Per produk/kategori/metode |
| GET | /api/financial-reports | Paginated |
| GET | /api/financial-reports/generate | Generate hari ini |
| POST | /api/financial-reports | Simpan laporan |
| GET | /api/financial-reports/:id | Detail |

### Settings, Notifications, Dashboard, Backup
| Method | Endpoint | Notes |
|---|---|---|
| GET/POST | /api/settings | Key-value store |
| GET | /api/notifications | 3 tipe: LOW_STOCK, EXPIRY, OVERDUE_DEBT |
| GET | /api/dashboard/stats | Summary + chart 8 bulan |
| GET | /api/backup | Download database.sqlite |
| POST | /api/restore | Upload .sqlite file |

### Suppliers
| Method | Endpoint | Body |
|---|---|---|
| GET/POST/PUT/DELETE | /api/suppliers | {name*} |

---

*Dokumen ini disusun berdasarkan analisis menyeluruh terhadap seluruh kode sumber proyek Toko Sumber Makmur POS v2.1 pada tanggal 4 Juni 2026.*
