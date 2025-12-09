# 📊 SLiMS Plugin - Statistik Siddiq

**Statistik Siddiq** adalah plugin dashboard untuk [SLiMS (Senayan Library Management System)](https://slims.web.id/web/) yang menyajikan statistik dan visualisasi data perpustakaan secara komprehensif dan interaktif.

Plugin ini dirancang untuk membantu pustakawan dan admin perpustakaan dalam menganalisis data koleksi, keanggotaan, dan transaksi peminjaman dengan tampilan visual yang menarik dan mudah dipahami.

> **Terinspirasi dari**: Postingan Pak Hendro Wicaksono di grup WhatsApp SLiMS Community

---

## ✨ Fitur Utama

### 📚 Statistik Koleksi
• **Total Koleksi** - Menampilkan jumlah total judul bibliografi yang ada di perpustakaan  
• **Total Koleksi Bereksemplar** - Jumlah biblio yang memiliki eksemplar fisik  
• **Total Eksemplar** - Jumlah total item/eksemplar yang tersedia  
• **Total Eksemplar Tanpa Judul** - Deteksi eksemplar yang tidak terhubung dengan data biblio

### 📈 Visualisasi Data Interaktif
• **Diagram Distribusi GMD** - Visualisasi donut chart untuk melihat distribusi koleksi berdasarkan General Material Designation (Buku, Video, CD-ROM, Audio, dll)  
• **Diagram Distribusi Subjek** - Visualisasi donut chart untuk analisis koleksi berdasarkan topik/subjek  
• **Chart Interaktif** - Menggunakan Chart.js dengan animasi smooth dan responsif

### 👥 Statistik Keanggotaan
• **Total Anggota** - Jumlah seluruh anggota yang terdaftar di sistem  
• **Total Anggota Aktif** - Anggota yang pernah melakukan transaksi peminjaman

### 📋 Statistik Transaksi
• **Total Transaksi** - Jumlah total transaksi peminjaman yang pernah terjadi  
• **Total Transaksi Aktif** - Peminjaman yang masih berjalan (belum dikembalikan)

### 📅 Progres Peminjaman Bulanan
• **Tabel Breakdown** - Progres peminjaman per bulan untuk tahun berjalan  
• **Kategori Anggota** - Statistik berdasarkan tipe anggota (Mahasiswa, Dosen, Pegawai, dll)

### 🔍 Filter Data Berdasarkan Tahun
• **Filter Fleksibel** - Tampilkan statistik hingga tahun tertentu  
• **Reset Cepat** - Tombol untuk mengembalikan ke tampilan default

---

## 🛠️ Instalasi & Pemasangan

Panduan lengkap untuk memasang plugin ini dapat ditemukan pada tautan berikut:

➡️ [Panduan Pemasangan Plugin SLiMS](https://github.com/adeism/belajarslims/blob/main/belajar-pasang-plugin.md)

### Langkah Singkat:
1. Download atau clone repository ini
2. Copy folder `statistik-siddiq` ke direktori `plugins/` di instalasi SLiMS Anda
3. Login ke admin SLiMS
4. Akses menu **Reporting** → **Statistik Siddiq**

---

## 💻 Kebutuhan Sistem

• ✅ **SLiMS**: Versi 9.6.1 (Bulian) atau lebih tinggi  
• ✅ **PHP**: 7.4 atau lebih tinggi  
• ✅ **Database**: MySQL/MariaDB  
• ✅ **Hak Akses**: Peran pengguna sebagai Admin atau Pustakawan dengan akses menu Reporting

---

## 🚀 Cara Penggunaan

1. Masuk ke area admin SLiMS Anda
2. Arahkan kursor ke menu **Reporting** → **Statistik Siddiq**
3. Gunakan filter **"Data s.d Tahun"** untuk melihat statistik hingga tahun tertentu
4. Klik tombol **"Terapkan"** untuk menerapkan filter
5. Klik tombol **"Reset"** untuk kembali ke tampilan default

### Tips:
- Dashboard secara otomatis menampilkan data tahun berjalan saat pertama kali dibuka
- Semua chart dapat dilihat detail dengan hover mouse di atas segmen
- Data diambil secara real-time dari database perpustakaan

---

## 📁 Struktur File

```
statistik-siddiq/
├── dashboard.plugin.php    # File registrasi plugin
├── index.php               # Interface admin dashboard dengan filter
├── assets/
│   └── dashboard.css       # Styling dashboard dan print layout
└── README.md               # Dokumentasi plugin
```

---

## 🔒 Keamanan

Plugin ini mengikuti best practices keamanan SLiMS:

✅ **Authentication Check** - Menggunakan `INDEX_AUTH` untuk verifikasi login  
✅ **Authorization Check** - Menggunakan `utility::havePrivilege()` untuk cek hak akses  
✅ **SQL Injection Protection** - Menggunakan prepared statements  
✅ **XSS Prevention** - Sanitasi output data

---

## 🎨 Teknologi yang Digunakan

• **PHP** - Backend logic dan database query  
• **Chart.js** - Library untuk visualisasi chart interaktif  
• **CSS3** - Styling responsive dan print-friendly  
• **JavaScript** - Interaktivitas dan filter dinamis

---

## 📝 Changelog

### Version 1.0.0
- ✅ Initial release
- ✅ Dashboard statistik koleksi, anggota, dan transaksi
- ✅ Visualisasi chart GMD dan Subjek
- ✅ Tabel progres peminjaman bulanan
- ✅ Filter berdasarkan tahun
- ✅ Print-friendly layout

---

## 👨‍💻 Author

**Ade Ismail Siregar**  
📧 Email: [adeismailbox@gmail.com](mailto:adeismailbox@gmail.com)  
🐙 GitHub: [https://github.com/adeism](https://github.com/adeism)

---

## ⚠️ Disclaimer

> **JANGAN** langsung pasang DI SLiMS Operasional (tes di PC/SLiMS lain). Gunakan dengan risiko Anda sendiri.

