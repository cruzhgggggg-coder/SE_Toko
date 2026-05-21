# Narasi Bug untuk Agen AI

Dokumen ini berisi daftar bug dan kebutuhan perbaikan pada aplikasi agar Agen AI dapat memahami konteks masalah, area terdampak, serta perilaku yang diharapkan.

## Tujuan
Aplikasi perlu diperbaiki pada beberapa modul utama: data pelanggan, stok barang, kategori, hutang pelanggan, laporan, notifikasi, pengaturan toko, lisensi, restock, indikator stok, dan proses input jumlah barang.

## Daftar Bug dan Kebutuhan Perbaikan

### 1. Batas departemen pada data pelanggan
Pada data pelanggan terdapat bug terkait `dept limit`. Sistem perlu dicek apakah pembatasan departemen terlalu ketat, salah validasi, atau menyebabkan data pelanggan tidak tampil / tidak bisa dipilih dengan benar.

### 2. Input barang baru di stok barang
Saat menambahkan barang baru ke stok barang, proses input mengalami bug. Form atau proses penyimpanan barang baru perlu diperbaiki agar data bisa masuk dengan normal tanpa error.

### 3. Kategori tidak muncul di filter
Ada kategori yang tidak muncul pada fitur filter. Data kategori harus diperiksa agar semua kategori yang aktif dapat tampil dan bisa dipakai untuk penyaringan.

### 4. Bug pada catat hutang karena pelanggan tidak ditemukan
Saat mencatat hutang, sistem gagal karena pelanggan tidak ditemukan. Perlu perbaikan pada pencarian pelanggan, validasi data, dan alur pemilihan pelanggan agar proses catat hutang tidak berhenti.

### 5. Tambah stok barang bug
Fitur tambah stok barang juga mengalami bug. Proses penambahan stok perlu diperbaiki agar stok bertambah sesuai input dan status barang ikut ter-update dengan benar.

### 6. Semua kategori harus bisa diedit, tidak fixed
Kategori saat ini bersifat tetap. Buat agar semua kategori dapat diedit dan ditambah secara fleksibel, bukan hardcoded atau fixed.

### 7. Tambah pelanggan baru menyebabkan sistem bug
Saat menambah pelanggan baru, sistem mengalami gangguan. Perlu diperbaiki agar form tambah pelanggan dapat disimpan dengan aman dan data pelanggan baru langsung masuk ke database.

### 8. Aksi pada data pelanggan dibuat page baru untuk lunas hutang
Pada bagian aksi di data pelanggan, perlu dibuat halaman baru yang digunakan untuk pelunasan hutang. Alurnya: ketika pelanggan sudah membayar, user dapat menekan aksi lalu masuk ke page khusus untuk menandai hutang sebagai lunas.

### 9. Di laporan, bagian cetak struk dihapus
Pada modul laporan, fitur atau tombol `cetak struk` perlu dihapus karena tidak dibutuhkan.

### 10. Retur dihapus
Menu atau fitur retur perlu dihilangkan dari sistem.

### 11. Warna tombol simpan laporan harian, detail penjualan, dan kembali disamakan
Tombol `simpan laporan harian`, `detail penjualan`, dan `kembali` harus memakai warna yang sama, yaitu hijau.

### 12. Export Excel harus bisa langsung download laporan
Saat tombol export Excel ditekan, sistem harus langsung mengunduh file laporan dalam format Excel.

### 13. Simpan laporan harian jadi Excel
Laporan harian harus bisa disimpan ke file Excel.

### 14. Simpan data download jadi Excel
Data download juga harus tersedia dalam format Excel.

### 15. Notifikasi: logo delete harus bisa menghapus notifikasi
Pada bagian notifikasi, ikon/logo delete harus benar-benar berfungsi untuk menghapus notifikasi.

### 16. Nama toko saat diedit tidak mengubah tulisan bagian atas
Ketika nama toko diedit, tulisan yang tampil di bagian atas belum ikut berubah. Harus diperbaiki agar perubahan nama toko tersinkron dengan seluruh tampilan yang memakai nama tersebut.

### 17. Status lisensi dihapus
Bagian `status lisensi` perlu dihapus dari tampilan maupun logika yang sudah tidak diperlukan.

### 18. Status tidak ter-update saat restock
Saat restock dilakukan, status barang tidak ikut ter-update. Sistem harus memastikan status stok berubah sesuai kondisi terbaru setelah restock.

### 19. Indikator E tidak sesuai
Indikator `E` tidak sesuai dengan kondisi sebenarnya. Contohnya, tulisan sudah menunjukkan `1009`, tetapi status masih menampilkan `menipis`. Logika indikator stok perlu disesuaikan agar status benar-benar mengikuti jumlah barang.

### 20. Fitur input jumlah barang yang dibeli langsung lewat ketikan
Tambahkan fitur agar jumlah barang yang dibeli bisa diketik langsung oleh user, tanpa harus selalu lewat kontrol tambahan.

## Ringkasan Prioritas
1. Perbaiki bug yang mengganggu alur transaksi dan data master: pelanggan, hutang, stok barang, dan restock.
2. Rapikan fitur laporan: hapus `cetak struk` dan `retur`, lalu pastikan export/simpan ke Excel berjalan.
3. Benahi UI/UX: warna tombol hijau seragam, notifikasi bisa dihapus, dan nama toko berubah di semua tempat.
4. Revisi logika kategori, status lisensi, indikator stok, dan input jumlah pembelian.

## Harapan Hasil Akhir
Setelah semua perbaikan, sistem harus:
- stabil saat menambah pelanggan, barang, dan stok,
- bisa mencatat dan melunasi hutang dengan benar,
- menampilkan kategori dan status stok secara akurat,
- mendukung export dan simpan laporan dalam Excel,
- memiliki tampilan yang konsisten dan fitur yang lebih mudah digunakan.

## INFO PENTING
CEK KESELURUHAN PER DETAIL FITUR YANG ADA DI CODINGAN JIKA MASIH ADA BUG YANG TIDAK TERCANTUM PERBAIKI JUGA
SESUAIKAN DENGAN USE_CASE,ERD,SEQUENCE,CLASS,DAN ACTIVITY
