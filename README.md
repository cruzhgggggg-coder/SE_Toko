# 🛒 Toko Sumber Makmur POS (Point of Sale)

[![Vue 3](https://img.shields.io/badge/Frontend-Vue%203%20%28Vite%29-4fc08d?style=for-the-badge&logo=vue.js&logoColor=white)](https://vuejs.org/)
[![Laravel 11](https://img.shields.io/badge/Backend-Laravel%2011-ff2d20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com/)
[![SQLite](https://img.shields.io/badge/Database-SQLite-003b57?style=for-the-badge&logo=sqlite&logoColor=white)](https://www.sqlite.org/)
[![License](https://img.shields.io/badge/Academic%20Project-Software%20Engineering-blue?style=for-the-badge)](https://github.com/)

Aplikasi **Toko Sumber Makmur POS** adalah sistem kasir desktop-optimized modern berbasis Single Page Application (SPA) yang dirancang untuk kebutuhan toko grosir dan eceran. Sistem ini mengintegrasikan Vue 3 (Composition API, Pinia, Vanilla CSS) sebagai Frontend dengan Laravel 11 REST API dan database portabel SQLite sebagai Backend.

Proyek ini dirancang secara terstruktur dan mematuhi batasan diagram rancangan akademis (Use Case, ERD, Class, Activity, dan Sequence Diagram) secara presisi tanpa adanya fitur liar tambahan.

---

## 🛠️ Arsitektur Teknologi

Sistem dibangun menggunakan susunan teknologi modern yang menjamin kecepatan tinggi, keindahan visual, dan ketahanan data offline:

*   **Frontend SPA**: Vue 3 (Composition API) + Vite.
*   **State Management**: Pinia Store untuk manajemen keranjang kasir, data pengguna aktif, dan riwayat sinkronisasi.
*   **Desain Antarmuka**: Vanilla CSS (Rich Aesthetics) dengan kombinasi warna harmoni HSL, Glassmorphism, efek bayangan premium, transisi dinamis, serta tata letak responsif.
*   **Offline Data Resilience**: **IndexedDB** browser untuk menyimpan transaksi offline jika internet terputus, dilengkapi antrean sinkronisasi real-time.
*   **Backend REST API**: Laravel 11 dengan struktur MVC yang kokoh, Controller terpisah per modul, Resource API, dan request validation terpusat.
*   **Database**: SQLite (Zero-Configuration, Portable, dan sangat mudah dipindahkan antarkomputer dosen).

---

## ✨ 6 Fitur Utama (Kepatuhan Use Case Diagram)

Sistem dibatasi secara ketat hanya pada 6 modul utama sesuai rancangan akademik:

1.  **Dashboard Utama (Owner's Dashboard)**
    *   Statistik real-time: Total Omset, Jumlah Transaksi, Margin Keuntungan, dan Laba Bersih.
    *   *Profit Shield*: Informasi Harga Pokok Pembelian (HPP) & Laba Bersih dilindungi oleh modul otorisasi rahasia berbasis password Owner (`password123`).
    *   Grafik Tren Penjualan dan Laba Bulanan dinamis.
    *   Sistem notifikasi/peringatan stok kritis dan barang mendekati kedaluwarsa.
2.  **Kasir / POS Utama (Point of Sale)**
    *   Pencarian barang kilat menggunakan nama produk, kategori, atau pemindaian barcode.
    *   Switch toggle harga grosir/eceran per produk dalam keranjang secara dinamis.
    *   Dua jenis metode pembayaran: **Tunai** (dengan hitungan kembalian otomatis & cetak struk belanja thermal) dan **Kasbon** (dengan validasi limit kredit pelanggan secara ketat).
    *   Antrean offline (*Offline Queue*) berbasis browser IndexedDB dengan tombol SYNC satu tombol.
3.  **Manajemen Stok FIFO (Inventory First-In, First-Out)**
    *   Pengelompokan stok barang berdasarkan Batch tanggal masuk dan tanggal kedaluwarsa.
    *   Penjualan kasir secara otomatis memotong stok dari batch tertua terlebih dahulu (FIFO).
    *   Status indikator stok kritis (warna oranye) dan stok habis (warna merah).
    *   Riwayat alur pergerakan stok barang masuk/keluar untuk audit trail yang transparan.
4.  **Manajemen Pelanggan CRM (Customer Management)**
    *   Pencatatan data pelanggan/member toko.
    *   Pengaturan batas limit kredit hutang kasbon (*Credit Limit*) per pelanggan.
    *   Pelacakan total hutang aktif pelanggan saat ini.
5.  **Manajemen Karyawan (Role-Based Access Control / RBAC)**
    *   Pengaturan akses akun karyawan toko.
    *   Tiga peran utama terintegrasi penuh:
        *   **Owner (Admin Utama)**: Memiliki hak akses penuh ke seluruh sistem (9 menu).
        *   **Admin Gudang**: Hanya dapat mengakses modul Stok Barang (Inventory) dan Laporan Stok.
        *   **Kasir**: Hanya dapat mengakses modul Kasir POS dan mencatat Transaksi Penjualan.
6.  **Pencatatan Retur Barang (Damaged/Return Tracker)**
    *   Pencarian ID Transaksi lama pelanggan yang ingin diretur.
    *   Pemilihan item produk rusak, jumlah item retur, dan pengisian alasan retur.
    *   Sistem otomatis mengoreksi stok gudang dengan menciptakan batch penyesuaian baru berkode awalan `RET-` agar stok kembali bertambah dengan rapi.

---

## 🚀 Panduan Instalasi & Cara Menjalankan

Ikuti langkah-langkah di bawah ini untuk menjalankan aplikasi secara lokal di komputer Anda:

### 📋 Prasyarat Sistem
*   PHP >= 8.2 (direkomendasikan menggunakan Laragon / XAMPP terbaru).
*   Composer (untuk manajemen dependensi PHP).
*   Node.js >= 18 (dengan NPM).
*   Git (opsional).

---

### 1. Pengaturan Backend (Laravel REST API)

1.  Masuk ke direktori backend:
    ```bash
    cd backend
    ```
2.  Instal dependensi Composer:
    ```bash
    composer install
    ```
3.  Salin file konfigurasi `.env`:
    ```bash
    cp .env.example .env
    ```
4.  Buat file database SQLite kosong di folder `database`:
    *   Di Windows PowerShell:
        ```powershell
        New-Item -ItemType File -Path database/database.sqlite -Force
        ```
    *   Di Linux/Mac/Git Bash:
        ```bash
        touch database/database.sqlite
        ```
5.  Jalankan migrasi database dan pengisian data dummy awal (seeding):
    ```bash
    php artisan migrate:fresh --seed
    ```
    *Seeder akan mengisi database dengan 3 akun karyawan, data produk dengan batch FIFO, data pelanggan, histori penjualan, dan catatan retur untuk keperluan demo.*
6.  Nyalakan server backend Laravel:
    ```bash
    php artisan serve
    ```
    *Secara default server akan berjalan di `http://127.0.0.1:8000`.*

---

### 2. Pengaturan Frontend (Vue 3 SPA)

1.  Buka terminal baru di direktori utama proyek (`SE_Toko`):
    ```bash
    # Jika berada di folder backend, kembali ke folder root
    cd ..
    ```
2.  Instal dependensi Node modules:
    ```bash
    npm install
    ```
3.  Jalankan server lokal frontend:
    ```bash
    npm run dev
    ```
4.  Buka browser Anda dan akses alamat yang tertera di terminal:
    ```
    http://localhost:5173
    ```

---

## 🔑 Kredensial Akun Pengujian (Demo Login)

Anda dapat menggunakan akun-akun di bawah ini untuk mendemonstrasikan sistem hak akses (RBAC) saat pengujian:

| Peran (Role) | Username | Password | Otoritas Menu |
| :--- | :--- | :--- | :--- |
| **Owner (Admin Utama)** | `owner` | `password123` | Akses penuh (9 Menu Navigasi) |
| **Admin Gudang** | `admin` | `password123` | Terbatas: Stok Barang & Laporan Stok |
| **Kasir** | `kasir1` | `password123` | Terbatas: POS Kasir & Riwayat Transaksi |

> [!TIP]
> **Password Laba Bersih Dashboard**: Untuk membuka ikon gembok pada metrik laba bersih di halaman Dashboard Owner, masukkan password owner: `password123`.

---

## 📂 Struktur Repositori Utama

```
SE_Toko/
│
├── src/                      # Frontend Vue 3 SPA
│   ├── assets/               # Aset Gambar, Logo, & CSS Utama
│   ├── components/           # Komponen Reusable (Sidebar, Navbar, Struk Thermal)
│   ├── pages/                # Halaman Fitur (Dashboard, Kasir, Inventory, dll.)
│   ├── router/               # Vue Router untuk Navigasi Halaman
│   ├── services/             # Integrasi Layanan API (Axios ke Laravel)
│   └── stores/               # Pinia Stores (Keranjang POS, Auth Session)
│
├── backend/                  # Backend Laravel 11
│   ├── app/
│   │   ├── Http/Controllers/ # Controller API Terpisah per Fitur
│   │   └── Models/           # Definis Model Eloquent (FIFO Batches, Retur, dll.)
│   ├── database/
│   │   ├── migrations/       # Skema Tabel SQLite Lengkap
│   │   └── seeders/          # Pengisi Data Dummy Demo Dosen
│   └── routes/api.php        # Endpoint REST API Terstruktur
│
├── DESIGN_BLUEPRINT.md       # Panduan Sidang & Lembar Pengujian Dosen
├── README.md                 # Dokumentasi Pengoperasian Proyek (Dokumen ini)
└── .gitignore                # Penyaring File Sampah Version Control
```

---

## 📜 Lisensi & Kepatuhan Akademis
Aplikasi ini dikembangkan sebagai bagian dari tugas proyek akhir Rekayasa Perangkat Lunak (**Software Engineering**). Penulisan kode program mematuhi asas kebersihan kode (*clean code*), penanganan error sisi klien, serta keamanan transaksi database.

*Diperbarui secara berkala pada 18 Mei 2026. Siap dipresentasikan di depan Sidang Dewan Penguji.*
