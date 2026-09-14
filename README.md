# SKE Parking Management System

Dokumentasi lengkap, panduan instalasi, dan struktur sistem untuk **SKE Parking**—aplikasi manajemen parkir berbasis web yang terintegrasi dengan database MySQL (`gl_parkir`).
**algoritma**[algoritma](https://canva.link/7fqo865t646y7lw)
**flowchart**[flowchart](https://raw.githubusercontent.com/Alan28615/parkirUKK/refs/heads/main/flowcart.jpeg)
**mockup**[mockup](https://raw.githubusercontent.com/Alan28615/parkirUKK/refs/heads/main/mockup.jpeg)

---

## 📋 Daftar Isi
1. [Tentang Proyek](#tentang-proyek)
2. [Fitur Utama](#fitur-utama)
3. [Struktur Database](#struktur-database)
4. [Persyaratan Sistem](#persyaratan-sistem)
5. [Panduan Instalasi & Penggunaan](#panduan-instalasi--penggunaan)
6. [Hak Akses & Kredensial](#hak-akses--kredensial)

---

## 🚗 Tentang Proyek
**SKE Parking System** adalah aplikasi sistem kontrol dan manajemen parkir pintar (*Smart Control System*) yang dirancang untuk memantau kapasitas zona parkir secara *real-time*, mencatat arus kendaraan masuk/keluar, mengelola transaksi pembayaran, serta menyediakan panel kontrol khusus untuk administrator.

---

## ✨ Fitur Utama
* **Dashboard Publik (Live Index):** Memantau kapasitas total, jumlah slot terisi, slot kosong, serta persentase penggunaan di berbagai zona (Basement Mobil, Motor, dan VIP).
* **Autentikasi & Kontrol Sesi:** Sistem login berbasis peran (*role-based*) yang membedakan akses antara pengguna umum/member dan administrator.
* **Panel Kontrol Admin:** 
  * Ringkasan pendapatan harian dan statistik kendaraan aktif.
  * Manajemen area dan slot parkir (`tb_area_parkir`).
  * Log data transaksi kendaraan & pembayaran masuk/keluar (`tb_transaksi`, `tb_pembayaran`).
  * Manajemen data petugas (`tb_petugas`) dan member terdaftar (`tb_member`).
* **Visualisasi Grafik:** Grafik interaktif menggunakan Chart.js untuk memantau arus kendaraan harian.
* **Galeri & Video:** Dokumentasi visual fasilitas parkir serta integrasi pemutar video sistem.

---

## 🗄️ Struktur Database (`gl_parkir`)
Sistem ini dirancang untuk terhubung dengan database MySQL bernama `gl_parkir`, dengan tabel-tabel utama pendukung sebagai berikut:
* `tb_area_parkir`: Menyimpan data zona, kapasitas, dan jumlah slot tersedia.
* `tb_transaksi`: Mencatat data kendaraan masuk, keluar, plat nomor, dan durasi.
* `tb_pembayaran`: Mencatat rincian biaya dan status pembayaran parkir.
* `tb_petugas`: Menyimpan data akun dan shift petugas parkir.
* `tb_member`: Menyimpan data pelanggan tetap (member) beserta saldo digital.

---

## ⚙️ Persyaratan Sistem
* **Web Server:** Apache / Nginx (XAMPP, Laragon, atau WampServer disarankan).
* **PHP:** Versi 8.0 atau yang lebih baru (dengan ekstensi `mysqli` aktif).
* **Database:** MySQL / MariaDB.
* **Browser Modern:** Google Chrome, Mozilla Firefox, Microsoft Edge, atau Safari.

---

## 🚀 Panduan Instalasi & Penggunaan
1. **Pindahkan Berkas:** Letakkan berkas kode sumber PHP (`index.php`) ke dalam direktori server lokal Anda (contoh: `htdocs/parkirske/` di XAMPP).
2. **Konfigurasi Database:**
   * Buat database baru di MySQL dengan nama `gl_parkir`.
   * Sesuaikan konfigurasi koneksi database pada bagian atas kode PHP jika diperlukan:
     ```php
     $host = 'localhost';
     $user = 'root';
     $pass = '';
     $db   = 'gl_parkir';
     ```
3. **Jalankan Server:** Aktifkan layanan Apache dan MySQL melalui Control Panel XAMPP/Laragon.
4. **Akses Aplikasi:** Buka browser dan ketikkan alamat berikut:
   ```text
   http://localhost/parkirske/
