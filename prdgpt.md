# PRD SikolaNata3
## Sistem Manajemen Hotel Terintegrasi — InSitu Hotel Syariah Garut

**Status dokumen:** Baseline PRD untuk implementasi  
**Versi:** 1.0  
**Bahasa:** Indonesia  
**Produk:** SikolaNata3  
**Properti:** InSitu Hotel Syariah Garut  
**Platform:** Aplikasi web responsif berbasis Laravel dan MySQL  
**Target deployment:** Shared hosting cPanel, tanpa dukungan symbolic link  
**Pengguna utama:** Manager, Resepsionis, Housekeeping  
**Catatan penting untuk implementer:** Seluruh kebutuhan pada dokumen ini merupakan persyaratan produk. Jika ada kendala teknis, implementer harus menjelaskan kendala dan memberikan alternatif yang kompatibel; jangan diam-diam menghilangkan fitur atau mengasumsikan infrastruktur yang tidak tersedia.

---

# 1. Ringkasan Eksekutif

SikolaNata3 adalah sistem manajemen operasional hotel untuk InSitu Hotel Syariah Garut. Aplikasi mengintegrasikan website publik hotel, pemesanan kamar, reservasi dari berbagai kanal, front office, kendali kamar, housekeeping, meeting room, fine dining, transaksi dan pembayaran, akuntansi, laporan keuangan, inventaris, aset tetap, analitik pelanggan, serta keamanan dan audit trail.

Aplikasi harus ringan, responsif di desktop/tablet/ponsel, dapat dikonfigurasi oleh Manager, dan layak dipasang pada shared hosting cPanel yang **tidak mendukung symbolic link**. Karena itu, deployment tidak boleh membutuhkan `php artisan storage:link`, symlink ke `public/storage`, atau symbolic link lain. File privat harus tetap terlindungi tanpa harus diekspos ke direktori publik.

## 1.1 Tujuan utama

1. Menyatukan seluruh reservasi kamar, meeting room, dan fine dining dalam satu sistem.
2. Menyediakan kendali kamar interaktif yang memudahkan resepsionis dan housekeeping melihat serta memperbarui status.
3. Mencegah double booking dan menjaga konsistensi data kamar, transaksi, pembayaran, dan akuntansi.
4. Mendukung empat sumber reservasi kamar: Walk-In, OTA, Website, dan WhatsApp.
5. Mengelola harga weekday, weekend, high season, promo, diskon, pajak, service charge, dan layanan tambahan melalui konfigurasi.
6. Menghasilkan laporan keuangan berbasis akuntansi akrual dan jurnal berpasangan.
7. Mengelola aset tetap, persediaan, pengadaan, pemakaian stok, serta maintenance.
8. Menyediakan jejak audit yang bisa menelusuri tindakan sensitif oleh pengguna.
9. Mengurangi ketergantungan operasional pada pencatatan di luar aplikasi.

## 1.2 Prinsip desain

- **Sumber data tunggal:** Reservasi, pembayaran, layanan, dan jurnal tidak boleh dicatat ganda untuk transaksi yang sama.
- **Konfigurasi, bukan hard-code:** Tarif, jenis kamar, nomor kamar, jam check-in/out, metode pembayaran, pajak, service charge, prefix dokumen, dan batas kebijakan harus dapat diatur sesuai hak akses.
- **Status dipisahkan:** Status reservasi, okupansi, operasional kamar, dan kebersihan kamar adalah dimensi berbeda.
- **Akuntabilitas:** Pembatalan atau koreksi transaksi tidak menghapus jejak transaksi asli.
- **Privasi:** Dokumen identitas dan bukti transfer disimpan privat; QR verifikasi tidak membocorkan data pribadi.
- **Shared-hosting-first:** Hindari ketergantungan pada daemon, WebSocket, Redis, worker persisten, atau symbolic link.
- **Mobile-friendly:** Alur harian resepsionis dan housekeeping harus mudah digunakan dari ponsel.
- **Rupiah tanpa desimal:** Semua nominal yang terlihat kepada pengguna menggunakan format IDR tanpa angka desimal, misalnya `Rp 1.250.000`.

---

# 2. Ruang Lingkup Produk

## 2.1 Termasuk dalam versi pertama

1. Landing page hotel dan booking engine website yang bisa diaktifkan/nonaktifkan.
2. Master data hotel, kamar, tipe kamar, meeting room, layanan, menu/paket, harga, pajak, service charge, dan metode pembayaran.
3. Reservasi kamar dari Walk-In, OTA manual, Website, dan WhatsApp manual.
4. Kalender reservasi terpadu dan kalender/filter khusus layanan.
5. Alokasi kamar fleksibel: pada saat reservasi atau saat check-in.
6. Check-in, extend, pemindahan kamar, checkout, pembatalan, No-Show, late checkout, dan histori tamu.
7. Dashboard interaktif status kamar, housekeeping, PMR, dan maintenance.
8. Pembayaran DP dan pelunasan bertahap, invoice, kwitansi, QR verifikasi, dan bukti pembayaran.
9. Katalog layanan kamar tambahan dan item custom.
10. Reservasi meeting room serta fine dining.
11. Akuntansi akrual, jurnal otomatis/manual berotorisasi, buku besar, laporan lengkap, kasir per shift, pengeluaran, dan penutupan hari.
12. Modul aset tetap, stok persediaan, pemasok, pembelian/penerimaan, mutasi stok, dan persetujuan koreksi.
13. Analitik pelanggan dan laporan operasional.
14. Pengelolaan role dan permission granular.
15. Audit trail, notifikasi internal, ekspor PDF/XLSX/CSV, backup, dan pengaturan deployment yang sesuai shared hosting.

## 2.2 Di luar cakupan versi pertama

Hal berikut tidak dibuat sebagai integrasi otomatis kecuali disetujui pada fase lanjutan:

- Integrasi API OTA dan sinkronisasi inventori/harga dua arah dengan OTA.
- Integrasi WhatsApp Business API; komunikasi WhatsApp menggunakan tindakan manual/tautan.
- Payment gateway otomatis; website menggunakan pembayaran manual dengan unggah bukti dan verifikasi petugas.
- POS restoran penuh dengan meja, kitchen display, resep bahan baku, dan pencatatan produksi makanan secara menyeluruh. Modul fine dining versi pertama berfokus pada reservasi, pax, menu/paket, item tambahan, dan transaksi.
- Akuntansi pajak yang menggantikan konsultasi profesional; konfigurasi pajak harus dapat disesuaikan dan diverifikasi pihak hotel.
- Multi-properti/multi-hotel; aplikasi versi pertama hanya untuk satu properti.
- Aplikasi mobile native; produk berupa web responsif.
- Kunci kamar digital, smart lock, sistem telepon kamar, dan integrasi perangkat hotel.
- Otomasi aturan khusus hotel syariah yang belum ditentukan oleh manajemen. Aplikasi boleh menyediakan konfigurasi/catatan kebijakan, tetapi tidak boleh mengarang persyaratan penerimaan tamu.

---

# 3. Profil Hotel dan Konfigurasi Awal

## 3.1 Identitas properti

- Nama: **InSitu Hotel Syariah Garut**.
- Satu properti/lokasi.
- Jumlah kamar awal: **20 kamar**.
- Jumlah tipe kamar awal: **4 tipe kamar**.
- Seluruh jumlah, nama, nomor, fasilitas, kapasitas, tarif, dan status kamar harus dapat diubah melalui menu master data; angka 20 dan 4 bukan batas maksimal kode program.
- Zona waktu: `Asia/Jakarta`.
- Bahasa antarmuka: Bahasa Indonesia.
- Format angka: Indonesia.
- Mata uang: Rupiah Indonesia (IDR).
- Jam check-in default: **13:00**.
- Jam checkout default: **12:00**.
- Pajak daerah: nilai awal konfigurasi **10%**, dapat diubah per kategori dan tanggal berlaku. Tarif harus diverifikasi hotel sesuai ketentuan yang berlaku sebelum digunakan untuk transaksi riil.
- Batas awal persetujuan pengeluaran: pengeluaran **di atas Rp 500.000** memerlukan persetujuan Manager.
- Diskon Resepsionis: batas awal maksimum **10%**, dapat disesuaikan Manager.
- Masa tahan booking Website yang belum dikonfirmasi: **2 jam**, setelah itu kedaluwarsa otomatis jika masih memenuhi kondisi pending.
- Polling dashboard: nilai awal yang direkomendasikan **30 detik**, dapat disesuaikan bila diperlukan.
- Retensi backup: nilai awal yang direkomendasikan **30 hari**, dapat diatur sesuai kapasitas/kebijakan.

## 3.2 Nilai yang belum disediakan

Manager akan mengisi kemudian melalui master data/pengaturan:

- Nomor dan nama setiap kamar.
- Nama empat tipe kamar, kapasitas standar/maksimum, fasilitas, foto, dan tarif dasar.
- Harga weekday/weekend/high season untuk setiap tipe kamar.
- Daftar meeting room, kapasitas, fasilitas, dan tarif.
- Daftar menu, paket fine dining, layanan tambahan, dan tarif awal.
- Kebijakan usia anak/bayi dan biaya penghuni tambahan.
- Daftar akun akuntansi serta saldo awal.
- Data pemasok, aset, persediaan awal, dan lokasi penyimpanan barang.
- Nama OTA yang digunakan, detail komisi, dan jadwal settlement.
- Tarif pajak/service charge final per kategori, tanggal berlaku, serta perlakuan yang telah diverifikasi.
- Ketentuan khusus hotel syariah yang disetujui manajemen.
- Detail paket shared hosting, versi PHP, versi database, dan jalur deployment.

Aplikasi tidak boleh memasukkan data fiktif untuk menggantikan data operasional riil. Data contoh hanya boleh digunakan di lingkungan development/demo dan harus mudah dibedakan dari data produksi.

---

# 4. Role, Hak Akses, dan Audit

## 4.1 Role dasar

### Manager

Memiliki akses penuh ke semua modul dan konfigurasi, termasuk:

- Dashboard dan seluruh laporan.
- Master data dan konfigurasi hotel.
- Reservasi dan perubahan alokasi kamar.
- Harga, high season, promo, diskon, pajak, dan service charge.
- Persetujuan refund, pembatalan keuangan, pengeluaran, koreksi stok, serta status maintenance/disable.
- Akuntansi, jurnal manual, bagan akun, periode akuntansi, tutup kas, dan penutupan harian.
- Aset, pengadaan, stok, pemasok, serta penyusutan.
- Pengaturan role/permission, nomor dokumen, backup/retensi, notifikasi, dan identitas website.

### Resepsionis

Hak bawaan berfokus pada front office:

- Membuat/mengubah reservasi sesuai status dan izin.
- Memeriksa kalender dan ketersediaan kamar.
- Mengalokasikan kamar saat reservasi atau check-in sesuai kewenangan.
- Memproses check-in, extend, room move, checkout, dan layanan tambahan.
- Mencatat pembayaran, DP, pelunasan, metode bayar, serta mengunggah/memeriksa bukti bayar.
- Mencetak invoice dan kwitansi.
- Membuat/memperbarui profil pelanggan dengan akses data sesuai kebutuhan.
- Membuat permintaan housekeeping/PMR/maintenance.
- Mengelola diskon sampai batas yang ditentukan Manager.

Secara default, Resepsionis tidak boleh mengubah bagan akun, membuat jurnal manual, mengubah tarif pajak global, menyetujui refund sendiri, mengotorisasi koreksi stok, atau mengubah konfigurasi keamanan. Akses ringkasan keuangan hanya diberikan jika Manager mengaktifkan permission khusus.

### Housekeeping

Hak bawaan hanya untuk operasional:

- Melihat daftar kamar dan prioritas kerja yang relevan.
- Memperbarui status kebersihan sesuai alur yang diizinkan.
- Menerima dan memperbarui tugas PMR.
- Melaporkan kerusakan dan pekerjaan maintenance.
- Menambah catatan/foto pekerjaan jika diizinkan.
- Melihat jadwal check-in/check-out dan informasi minimum yang diperlukan untuk tugas.

Housekeeping tidak boleh melihat nominal tagihan, pembayaran, laporan keuangan lengkap, data identitas penuh, atau mengubah harga dan reservasi finansial.

## 4.2 Permission granular

Manager dapat mengatur permission per modul dan tindakan, minimal:

- `view`, `create`, `update`, `delete/archive` jika relevan.
- `approve`, `cancel`, `refund`, `close`, `reopen` jika relevan.
- `export`, `print`, `view_sensitive_data` jika relevan.
- Hak melihat nominal finansial harus dapat dibatasi secara terpisah dari hak operasional.

Penerapan izin wajib dilakukan pada backend/controller/policy/service, bukan hanya dengan menyembunyikan tombol di antarmuka. Pengguna tidak boleh dapat melewati pembatasan dengan mengakses URL atau endpoint secara langsung. Perubahan role/permission wajib tercatat di audit trail.

## 4.3 Akun dan sesi

- Akun pengguna memiliki nama, username/email, role, status aktif/nonaktif, dan informasi audit.
- Password disimpan menggunakan mekanisme hashing yang aman dari framework.
- Terapkan validasi password kuat, pembatasan percobaan login, penundaan/lockout setelah percobaan berulang, session timeout yang dapat dikonfigurasi, logout, dan alur reset password yang aman.
- Hindari menampilkan apakah email/username tertentu terdaftar ketika proses reset password.
- Akun nonaktif tidak boleh login.
- 2FA bukan kewajiban baseline, tetapi arsitektur tidak boleh menghalangi penambahan 2FA pada fase berikutnya.
- Gunakan CSRF protection, validasi request, escaping output, perlindungan SQL injection melalui query binding/ORM, dan pembatasan mass assignment.

## 4.4 Audit trail

Catat minimal:

- Login berhasil/gagal, logout, perubahan akun/role/permission.
- Pembuatan dan perubahan reservasi, alokasi kamar, extend, room move, check-in/check-out, pembatalan dan No-Show.
- Perubahan tarif, promo, diskon, pajak, service charge, override harga, serta alasan perubahan.
- Pembayaran, refund/reversal, verifikasi bukti, perubahan metode/akun pembayaran.
- Jurnal manual, pembatalan jurnal, perubahan akun, pengeluaran dan persetujuannya.
- Pembelian, penerimaan barang, pengeluaran barang, koreksi stok, pemindahan, serta persetujuan.
- Perubahan status kamar, maintenance, PMR, dan inspeksi housekeeping.
- Ekspor data, pengunduhan dokumen sensitif, perubahan konfigurasi keamanan, serta aktivitas backup/restore jika dapat dicatat.

Setiap log mencakup pengguna, waktu, tindakan, objek terkait, nilai sebelum/sesudah bila relevan, alasan, serta identitas request/session yang sesuai. Audit log tidak dapat diubah oleh pengguna biasa; koreksi data dilakukan melalui transaksi baru/reversal, bukan menghapus riwayat.

---

# 5. Modul Website Publik dan Booking Engine

## 5.1 Landing page

Landing page harus memiliki:

- Identitas hotel, logo, alamat, kontak, dan tombol WhatsApp.
- Galeri/foto hotel dan fasilitas.
- Informasi tipe kamar, deskripsi, kapasitas, fasilitas, dan harga indikatif bila disetujui Manager.
- Penjelasan kebijakan check-in/check-out serta kebijakan penting yang dikelola hotel.
- Informasi meeting room dan fine dining bila diaktifkan.
- Tombol reservasi yang mudah digunakan pada ponsel.
- Pengaturan konten dasar agar Manager dapat mengubah informasi yang sering berubah tanpa mengedit kode, sejauh masuk dalam CMS ringan aplikasi.
- SEO dasar, metadata, halaman kontak, dan halaman kebijakan privasi.

Semua foto dan aset publik harus melalui pengelolaan file yang aman. Jangan membuka direktori upload privat hanya demi memudahkan tampilan website.

## 5.2 Mengaktifkan/nonaktifkan booking Website

- Manager memiliki sakelar `Website Booking Enabled`.
- Saat aktif, pengguna dapat mengirim permintaan booking melalui website.
- Saat nonaktif, tombol/form booking disembunyikan atau diganti pesan informasi, tetapi landing page tetap dapat diakses.
- Endpoint backend harus menolak pembuatan booking Website saat fitur nonaktif, walaupun request dikirim manual.
- Nonaktifnya Website booking tidak menonaktifkan input reservasi Walk-In, OTA, atau WhatsApp oleh petugas.

## 5.3 Alur reservasi Website

1. Calon tamu memilih tanggal check-in/check-out, tipe kamar, dan jumlah tamu.
2. Sistem memeriksa ketersediaan berdasarkan jumlah malam, status kamar, inventori tipe kamar, dan reservasi aktif.
3. Tampilkan tarif per malam/per periode, promo yang memenuhi syarat, pajak/service charge, dan estimasi total.
4. Calon tamu mengisi data tamu dan informasi yang wajib.
5. Sistem membuat reservasi berstatus menunggu verifikasi/konfirmasi dengan masa tahan 2 jam sesuai konfigurasi.
6. Jika pembayaran manual diwajibkan untuk konfirmasi, tamu dapat mengikuti instruksi transfer dan mengunggah bukti bayar.
7. Resepsionis memverifikasi bukti, jumlah, tanggal, rekening tujuan, dan ketersediaan sebelum mengonfirmasi.
8. Setelah verifikasi dan persyaratan konfirmasi terpenuhi, booking menjadi terkonfirmasi.
9. Jika dua jam berlalu dan booking masih pending tanpa persyaratan konfirmasi yang terpenuhi, sistem menandai booking kedaluwarsa dan melepaskan hold sesuai aturan.
10. Reservasi kedaluwarsa tetap tercatat untuk audit; tidak dihapus permanen.

Jika fitur pembayaran atau persetujuan diatur berbeda oleh Manager, status booking dan pembayaran tetap dipisahkan. Status `paid` tidak otomatis berarti booking disetujui jika ada pemeriksaan operasional yang belum selesai.

## 5.4 Masa tahan booking dan scheduler

- Nilai awal: 2 jam sejak booking pending dibuat.
- Timestamp kedaluwarsa disimpan di database.
- Proses kedaluwarsa harus idempotent: menjalankannya berulang kali tidak boleh menggandakan dampak atau membatalkan booking yang telah dikonfirmasi.
- Scheduler harus dijalankan melalui cron yang tersedia di cPanel/shared hosting. Dokumentasi implementasi harus menyertakan konfigurasi cron yang konkret berdasarkan lokasi instalasi nyata.
- Jika cron tidak tersedia, implementer harus menyatakan konsekuensi dan menyediakan fallback operasional yang aman. Jangan mengklaim expiry otomatis berjalan tanpa penjadwal yang benar-benar aktif.

---

# 6. Data Pelanggan dan Sumber Reservasi

## 6.1 Data tamu saat reservasi

Data minimum yang diminta:

- Nama tamu — wajib.
- Nomor WhatsApp — wajib, dinormalisasi secara konsisten (misalnya format internasional Indonesia `62...` tanpa duplikasi awalan).
- Jenis identitas — pilihan SIM, KTP, atau Paspor.
- Nomor identitas — opsional; akses dibatasi.
- Jumlah dewasa, anak, bayi — wajib.
- Asal instansi — opsional.
- Alamat tamu — opsional.
- Unggah bukti booking — opsional; gambar atau dokumen yang diizinkan.
- Tipe tamu/sumber reservasi — Walk-In, OTA, Website, atau WhatsApp.

Tambahan yang disarankan: catatan reservasi, nama pemesan bila berbeda dari tamu menginap, kontak alternatif bila dibutuhkan, serta preferensi/permintaan khusus yang relevan. Informasi sensitif harus dikumpulkan hanya jika diperlukan.

## 6.2 Data dan integritas pelanggan

- Profil pelanggan terpadu menghubungkan beberapa reservasi dengan pelanggan yang sama.
- Nomor WhatsApp dan/atau nomor identitas dapat membantu menemukan kandidat duplikasi, tetapi sistem harus menghindari penggabungan otomatis yang berisiko menggabungkan orang berbeda.
- Jika ditemukan kandidat duplikat, tampilkan pilihan kepada petugas yang memiliki izin.
- Perubahan data pelanggan tidak boleh mengubah snapshot nama/alamat yang sudah tercetak pada dokumen transaksi historis bila data snapshot diperlukan untuk audit.
- Riwayat pelanggan dapat menampilkan jumlah kunjungan, sumber booking, tipe layanan, nilai transaksi sesuai hak akses, dan catatan operasional yang relevan.
- Housekeeping hanya melihat informasi minimum yang diperlukan.

## 6.3 Kanal reservasi

### Walk-In

- Dibuat petugas ketika tamu datang langsung.
- Petugas memeriksa ketersediaan, mengisi profil, menentukan kamar/tipe, menetapkan tarif, dan mencatat pembayaran atau piutang.
- Tetap mengikuti aturan kapasitas, status kamar, harga, pajak, dan audit.

### OTA manual

- Petugas memasukkan reservasi dari OTA tanpa API.
- Simpan nama OTA/kanal, nomor referensi OTA, harga jual/bruto, komisi, potongan lain, dana bersih yang diharapkan, dana bersih yang diterima, status settlement, tanggal settlement, dan catatan bila tersedia.
- Tarif OTA boleh berbeda dari tarif langsung hotel; nilai yang diterima dari data OTA tidak boleh dipaksa sama dengan harga publik.
- Tidak ada sinkronisasi otomatis ketersediaan atau harga ke OTA pada versi ini. Petugas bertanggung jawab melakukan pembaruan di portal OTA eksternal.
- Laporan harus memisahkan pendapatan/penjualan bruto, komisi/potongan, piutang settlement, dan kas bersih diterima agar tidak terjadi pendapatan ganda.

### Website

- Diciptakan melalui booking engine internal.
- Booking dikonfirmasi manual setelah petugas memeriksa persyaratan dan bukti pembayaran sesuai kebijakan.

### WhatsApp

- Percakapan dilakukan manual.
- Petugas membuat reservasi di aplikasi dan mencatat sumber WhatsApp.
- Tombol/link WhatsApp boleh digunakan untuk membuka aplikasi WhatsApp dengan nomor dan pesan yang telah disiapkan, tetapi tidak melakukan pengiriman otomatis tanpa API.

---

# 7. Modul Kamar dan Reservasi

## 7.1 Master tipe kamar dan kamar fisik

Master tipe kamar sekurang-kurangnya mencakup:

- Nama/kode tipe.
- Deskripsi dan fasilitas.
- Kapasitas standar serta kapasitas maksimum yang diizinkan.
- Kebijakan dewasa/anak/bayi.
- Biaya penghuni tambahan jika berlaku.
- Foto dan status aktif/nonaktif.
- Harga dasar dan aturan tarif.

Master kamar fisik sekurang-kurangnya mencakup:

- Nomor/kode kamar unik.
- Tipe kamar.
- Lantai/lokasi opsional.
- Fasilitas/catatan khusus.
- Status operasional: aktif, maintenance, atau disable.
- Status kebersihan.
- Riwayat perubahan status dan pekerjaan.

Jumlah kamar dan tipe kamar dapat ditambah, dinonaktifkan, atau diubah oleh Manager. Kamar yang mempunyai reservasi aktif tidak boleh dihapus permanen; gunakan nonaktif/archive setelah penanganan reservasi yang terkait.

## 7.2 Empat dimensi status kamar

Status perlu dipisah untuk menghindari konflik:

### A. Status reservasi

Contoh: pending, menunggu verifikasi, terkonfirmasi, checked-in, checked-out, cancelled, expired, No-Show.

### B. Status okupansi

Contoh: tersedia secara waktu, akan datang, ditempati, atau sudah ditinggalkan. Ini berasal dari rentang reservasi dan waktu aktual check-in/check-out, bukan sekadar input manual.

### C. Status operasional

- **Aktif:** dapat dipertimbangkan untuk reservasi jika syarat lain terpenuhi.
- **Maintenance:** tidak boleh dialokasikan selama tidak layak digunakan.
- **Disable:** dinonaktifkan oleh Manager dan tidak ditawarkan sebagai inventori.

### D. Status kebersihan

- **Kotor:** perlu dibersihkan.
- **Sedang dibersihkan:** pekerjaan sedang berlangsung.
- **Bersih:** pembersihan selesai tetapi belum tentu lolos pemeriksaan akhir.
- **Diperiksa/siap:** inspeksi selesai dan kamar layak digunakan.
- Jika diperlukan di fase implementasi, tambahkan `perlu_dibersihkan_ulang`/rejected inspection.

Status operasional dan kebersihan tidak boleh saling menimpa. Kamar occupied bisa diminta PMR; kamar reserved bisa bersih atau kotor; kamar maintenance tidak boleh siap untuk alokasi meskipun bersih.

## 7.3 Alokasi kamar fleksibel

- Booking dapat menjamin tipe kamar terlebih dahulu, tanpa nomor kamar fisik, atau dapat menetapkan kamar fisik lebih awal.
- Jika nomor kamar belum ditetapkan, sistem menghitung ketersediaan berdasarkan inventori tipe kamar untuk seluruh rentang tanggal.
- Sebelum konfirmasi/alokasi/check-in, backend wajib memeriksa ulang ketersediaan untuk mencegah dua petugas membuat booking pada kamar/inventori yang sama.
- Jika kamar fisik sudah dialokasikan, sistem mencegah tumpang-tindih tanggal dengan reservasi yang statusnya memblokir inventori.
- Kamar maintenance/disable tidak masuk inventori tersedia untuk tanggal yang terdampak.
- Untuk menghindari race condition, gunakan transaksi database dan mekanisme penguncian/validasi yang sesuai pada proses konfirmasi/alokasi. Jangan hanya mengandalkan kalender frontend.
- Sistem harus dapat menyarankan kamar bersih dan siap untuk tamu yang akan check-in, terutama apabila tipe kamar sesuai.

## 7.4 Harga kamar

Harga dasar dihitung per kamar per malam. Mendukung:

- Harga weekday.
- Harga weekend.
- Harga high season berdasarkan periode.
- Harga khusus tanggal/periode.
- High season dapat memakai **harga khusus** atau **persentase kenaikan** dari harga dasar.
- Harga dapat berbeda per tipe kamar.
- Periode tarif tidak boleh tumpang tindih secara ambigu tanpa aturan prioritas.
- Harga yang dikonfirmasi pada reservasi disimpan sebagai snapshot harga. Perubahan master tarif berikutnya tidak boleh mengubah tagihan yang sudah terkunci, kecuali transaksi perubahan yang berotorisasi.
- Tarif extend untuk tanggal tambahan mengikuti tarif yang berlaku pada tanggal tambahan (`date_based`), lalu disimpan sebagai baris biaya tambahan/penyesuaian dengan jejak audit.
- Jika manager memberi override harga secara manual, simpan harga sebelum/sesudah serta alasan dan pengguna yang melakukan.

Urutan prioritas tarif yang direkomendasikan: override yang disetujui untuk reservasi > tarif khusus tanggal/periode > tarif high season yang aktif > tarif weekend/weekday > harga dasar tipe kamar. Prioritas ini harus dijelaskan pada UI dan dapat diuji.

## 7.5 Promo dan diskon

- Promo dapat mempunyai nama/kode, tanggal mulai/akhir, tipe diskon (persentase/nominal), batas nilai, layanan/tipe kamar yang memenuhi syarat, serta status aktif.
- Diskon resepsionis maksimum awal 10%, dapat diubah Manager.
- Diskon lebih tinggi dari batas memerlukan persetujuan Manager.
- Override manual wajib mencantumkan alasan.
- Default awal: satu promo utama diterapkan untuk suatu komponen transaksi; promo tidak ditumpuk dengan diskon manual kecuali Manager secara eksplisit mengatur aturan stacking. Detail prioritas/stacking harus dikonfigurasi dan diuji.
- Diskon tidak boleh menyebabkan nilai negatif.
- Snapshot diskon tersimpan pada transaksi.

## 7.6 Kapasitas dan tamu anak/bayi

- Kebijakan usia anak/bayi dapat dikonfigurasi per hotel dan/atau tipe kamar.
- Harga dasar mencakup kapasitas standar kamar.
- Aturan extra guest dapat dikonfigurasi per tipe kamar. Manager menetapkan kapasitas maksimum serta biaya jika penghuni melebihi kapasitas standar namun masih diperbolehkan.
- Sistem harus mencegah jumlah tamu melebihi kapasitas maksimum yang disetujui.
- Jumlah dewasa, anak, bayi disimpan terpisah.
- Definisi usia anak/bayi dan ketentuan gratis/berbayar harus dikonfigurasi sebelum transaksi produksi; jangan menanamkan batas usia arbitrer.

## 7.7 Pembayaran DP dan pelunasan

Sistem mendukung:

- DP sesuai persentase atau nominal.
- Full payment di awal.
- Pelunasan penuh saat masa inap berlangsung.
- Pelunasan saat checkout.
- Pembayaran parsial berkali-kali.
- Satu reservasi dapat memiliki banyak pembayaran.
- Nilai tagihan, total pembayaran sah, refund, dan saldo outstanding dihitung dari data transaksi, bukan isian status manual.
- Status keuangan minimal: belum dibayar, DP/parsial, lunas, refund sebagian, refund penuh, atau ada saldo kredit jika kebijakan mengizinkan.
- Status pembayaran terpisah dari status reservasi.
- Pembayaran manual harus mencatat tanggal/waktu, metode, akun kas/bank tujuan, referensi, jumlah, petugas, status verifikasi dan bukti bila ada.
- Sistem tidak boleh menyimpan data kartu pembayaran sensitif.
- Perubahan pembayaran atau refund dicatat melalui transaksi reversal/refund; transaksi asal tetap ada.

## 7.8 Check-in, extend, room move, checkout

### Check-in

- Hanya dapat dilakukan pada reservasi yang memenuhi status/izin.
- Verifikasi data tamu dan syarat yang dikonfigurasi hotel.
- Tetapkan kamar fisik jika belum dialokasikan.
- Kamar harus aktif, tidak maintenance/disable, tidak bentrok, dan memenuhi persyaratan kebersihan/inspeksi.
- Jika kamar belum siap, tampilkan alasan dan arahkan ke daftar prioritas housekeeping.
- Status check-in menyimpan waktu aktual dan petugas.

### Extend

- Petugas memilih tanggal checkout baru.
- Sistem mengecek ketersediaan kamar/tipenya untuk malam tambahan.
- Harga malam tambahan dihitung berdasarkan tanggal tambahan.
- Jika tamu tidak dapat mempertahankan kamar yang sama, sistem harus menawarkan room move dengan histori yang jelas.
- Total, pajak/service charge sesuai konfigurasi, saldo, dan dokumen transaksi diperbarui melalui transaksi tambahan; catatan sebelumnya tidak dihapus.

### Room move

- Pilih kamar baru dan alasan.
- Cek ketersediaan, tipe, kapasitas, dan kebersihan.
- Simpan waktu, kamar asal/tujuan, petugas, serta penyesuaian harga bila ada.
- Histori okupansi dan tagihan tetap terhubung pada reservasi yang sama.

### Checkout

- Tampilkan ringkasan biaya kamar per malam, layanan tambahan, pajak/service charge, diskon, pembayaran, dan saldo.
- Pelunasan boleh diselesaikan saat checkout sesuai kebijakan. Jika masih ada piutang yang diizinkan, catat jelas dan minta otorisasi sesuai aturan.
- Setelah checkout berhasil, status reservasi menjadi checked-out dan status kebersihan kamar berubah menjadi kotor secara otomatis.
- Kamar tidak dianggap siap untuk tamu selanjutnya sampai alur housekeeping selesai dan inspeksi/aturan kebersihan terpenuhi.

## 7.9 Late checkout

- Manager menetapkan toleransi, batas waktu, skema biaya, dan apakah biaya berdasarkan nominal, setengah malam, satu malam, atau penetapan manual.
- Default berupa konfigurasi, bukan denda yang di-hard-code.
- Resepsionis mencatat persetujuan/override dan alasannya.
- Keterlambatan yang berpengaruh pada reservasi berikutnya harus menandai potensi konflik dan memicu tugas operasional.

## 7.10 Pembatalan dan No-Show

- Manager mengatur tenggat pembatalan, biaya/penalti, refund, dan aturan per tarif/promo bila diperlukan.
- Pembatalan wajib menyimpan alasan, pelaku, waktu, status biaya dan refund.
- No-Show harus ditetapkan oleh petugas berwenang berdasarkan aturan yang dikonfigurasi, bukan terjadi hanya karena dugaan.
- Untuk website booking yang pending, expiry setelah dua jam adalah alur tersendiri dan tidak sama dengan pembatalan reservasi terkonfirmasi.
- Reservasi dibatalkan/No-Show tetap tersimpan.

---

# 8. Dashboard Manager, Kendali Kamar, dan Kalender

## 8.1 Dashboard Manager terpadu

Dashboard menampilkan widget yang datanya relevan dengan rentang waktu yang jelas, minimal:

- Jumlah kamar aktif dan inventori tersedia hari ini.
- Okupansi hari ini dan tren periode pilihan.
- Check-in yang dijadwalkan hari ini, tamu sedang menginap, dan checkout hari ini.
- Kamar bersih/siap, kotor, sedang dibersihkan, maintenance, dan disable.
- Kamar kotor dengan reservasi check-in hari ini sebagai prioritas tinggi.
- Booking Website menunggu verifikasi/pembayaran.
- Ringkasan pembayaran, piutang, dan pendapatan untuk periode terpilih; label harus menjelaskan apakah angka berupa kas diterima atau pendapatan akrual.
- Jadwal meeting room dan fine dining hari ini.
- Tugas PMR dan maintenance yang belum selesai.
- Persetujuan yang menunggu Manager: refund, diskon di atas batas, pengeluaran, koreksi stok, jurnal manual, atau tindakan sensitif lain.
- Stok minimum/stockout, jika modul inventaris diaktifkan.
- Status penutupan kasir/hari operasional.

Widget mengikuti permission. Housekeeping tidak melihat dashboard keuangan.

## 8.2 Kendali kamar

Tersedia tampilan:

- **Kartu kamar** untuk tindakan cepat.
- **Tabel kamar** dengan pencarian, pengurutan dan filter.
- **Room timeline** untuk memahami periode reservasi dan benturan jadwal.
- Filter tipe kamar, lantai/lokasi bila tersedia, status operasional, status kebersihan, okupansi, reservasi hari ini, dan status maintenance.
- Tindakan langsung sesuai permission: lihat detail, alokasi, check-in/out, ubah status kebersihan, buat PMR, laporkan kerusakan, atau membuka detail reservasi.
- Indikator visual konsisten, disertai label teks agar tidak bergantung pada warna saja.
- Layout mobile: kartu ringkas dan tombol yang mudah disentuh; tabel dapat beralih menjadi tampilan kartu.

### Aturan prioritas housekeeping

1. Kamar kotor yang mempunyai reservasi check-in hari ini ditampilkan sebagai prioritas paling tinggi.
2. Setelahnya, kamar check-in hari ini lainnya berdasarkan waktu kedatangan/jadwal operasional.
3. Tugas PMR harus terlihat sebagai pekerjaan kamar occupied, tidak mengubah okupansi menjadi kosong.
4. Kamar maintenance/disable tidak boleh diberi label siap walaupun status kebersihan menunjukkan bersih.
5. Dashboard menunjukkan waktu terakhir pembaruan data bila diperlukan untuk mencegah petugas mengandalkan tampilan yang usang.

## 8.3 Polling dashboard

- Default pembaruan otomatis setiap 30 detik.
- Polling hanya mengambil data ringkasan yang diperlukan; hindari query mahal dan request untuk setiap kartu satu per satu.
- Interval dapat disesuaikan dalam konfigurasi.
- Perubahan penting selalu divalidasi kembali oleh backend saat aksi disimpan.
- Bila hosting terbatas, pertahankan polling sederhana dan hindari WebSocket sebagai ketergantungan versi pertama.

## 8.4 Kalender reservasi terpadu

- Kalender gabungan dengan filter untuk kamar, meeting room, fine dining, status, dan sumber booking.
- Mendukung tampilan hari/minggu/bulan atau rentang yang layak bagi kebutuhan operasional.
- Perbedaan jenis layanan/status dibedakan dengan label dan warna konsisten.
- Klik item menampilkan detail sesuai permission.
- Membuat atau memindahkan jadwal harus memeriksa ulang konflik di backend.
- Untuk kamar, kalender menghormati tanggal check-in sampai sebelum tanggal checkout (checkout date tidak dihitung sebagai malam inap baru, kecuali late checkout/aturan khusus memerlukan blok waktu).
- Untuk meeting room/fine dining, konflik diperiksa berdasarkan rentang waktu dan buffer/persiapan jika dikonfigurasi.

---

# 9. Meeting Room

## 9.1 Master data

Setiap ruang meeting sekurang-kurangnya memiliki kode/nama, kapasitas, deskripsi, fasilitas, foto opsional, status aktif, dan catatan operasional. Jumlah ruang tidak dikunci pada kode.

## 9.2 Metode harga

Keduanya wajib didukung:

1. **Paket all-in/negosiasi:** contoh satu hari dengan makanan dan fasilitas senilai Rp3.000.000 all-in. Petugas dapat memasukkan total paket hasil negosiasi tanpa memaksa pemecahan harga per pax.
2. **Harga per pax:** harga dikalikan jumlah peserta, dengan catatan yang menjelaskan komponen yang termasuk seperti makan siang, kudapan, coffee break, sound system, proyektor, atau fasilitas lain.

Durasi fleksibel: per jam, setengah hari, satu hari, atau rentang custom. Manager dapat menetapkan default dan harga untuk setiap durasi. Harga custom disimpan sebagai snapshot.

## 9.3 Fasilitas dan item tambahan

- Sediakan template/paket fasilitas yang dapat dipilih dan diedit.
- Izinkan menambah fasilitas/layanan custom secara manual.
- Setiap item berisi nama/keterangan, kuantitas, satuan, harga satuan, diskon bila ada, pajak/service charge yang berlaku, dan subtotal.
- Paket dan item harus menyimpan rincian saat reservasi agar perubahan template berikutnya tidak mengubah transaksi lama.

## 9.4 Reservasi dan pembayaran

- Simpan pemesan, kontak WhatsApp, instansi opsional, tanggal, waktu mulai/selesai, jumlah pax, ruang, paket, fasilitas, harga, pajak/service charge, pembayaran dan catatan.
- Cek kapasitas ruang dan benturan waktu.
- Default kebijakan DP/pembatalan mengikuti konfigurasi umum, namun boleh di-override untuk kategori meeting room atau reservasi tertentu dengan otorisasi.
- Dukung pembayaran bertahap dan saldo terutang.
- Pembayaran masuk ke sistem akuntansi yang sama dengan sumber pendapatan teridentifikasi sebagai meeting room.
- Kalender menandai acara yang menunggu pembayaran, terkonfirmasi, berlangsung, selesai, atau dibatalkan.

---

# 10. Fine Dining

## 10.1 Tujuan dan batasan

Fine dining pada versi pertama berfokus pada reservasi tempat/makan, jumlah pax, paket/menu, biaya tambahan custom, pembayaran, dan laporan. Ini bukan kewajiban membangun POS restoran lengkap.

## 10.2 Reservasi

Data sekurang-kurangnya mencakup pemesan, kontak, tanggal, jam, durasi, jumlah pax, kapasitas/area, nomor meja opsional, catatan, status reservasi, serta rincian harga.

- Kalender memeriksa kapasitas pada slot waktu yang dipilih.
- Jika meja tidak digunakan sebagai sumber daya eksplisit, sistem tetap harus memeriksa kapasitas total/area.
- Reservasi tidak boleh melebihi kapasitas yang dikonfigurasi tanpa override berotorisasi.

## 10.3 Menu, paket dan item custom

- Master menu/paket mempunyai nama, deskripsi, harga per pax atau paket, status, dan informasi menu yang termasuk.
- Pesanan dapat menyertakan keterangan menu makan.
- Mendukung item tambahan manual: nama/keterangan × QTY × harga satuan.
- Harga custom pada transaksi tidak mengubah harga default katalog.
- Simpan snapshot seluruh item/harga pada reservasi.
- Pajak dan service charge mengikuti konfigurasi kategori fine dining.

## 10.4 Pembayaran dan laporan

- Gunakan modul pembayaran bersama, termasuk DP dan pelunasan jika kebijakan mengizinkan.
- Sumber pendapatan dicatat sebagai fine dining.
- Ekspor laporan dapat difilter berdasarkan tanggal, status, jumlah pax, paket, dan metode bayar sesuai permission.

---

# 11. Housekeeping, PMR, dan Maintenance

## 11.1 Alur kebersihan

Alur default:

`Kotor → Sedang Dibersihkan → Bersih → Diperiksa/Siap`

Jika hasil inspeksi tidak memenuhi standar, petugas berwenang dapat mengembalikan status ke `Sedang Dibersihkan` atau `Perlu Dibersihkan Ulang`.

- Checkout otomatis menjadikan kamar kotor.
- Perubahan menjadi bersih/siap dilakukan petugas yang berhak.
- Kamar hanya dapat dialokasikan untuk check-in jika seluruh aturan status operasional, ketersediaan, dan kebersihan terpenuhi.
- Catat petugas, timestamp, dan catatan inspeksi jika diperlukan.

## 11.2 PMR (Please Make Up Room)

- Permintaan dibuat tamu melalui resepsionis/kanal yang disepakati, atau petugas membuatnya saat menerima permintaan.
- Data: kamar, reservasi/tamu terkait secukupnya, waktu request, catatan, prioritas, status, petugas, waktu mulai dan selesai.
- Status: diminta, diterima/ditugaskan, sedang dikerjakan, selesai, dibatalkan bila diizinkan.
- PMR tidak mengubah status okupansi dan tidak boleh menampilkan nilai tagihan kepada Housekeeping.
- Dashboard memprioritaskan permintaan yang mendekati waktu yang dijanjikan jika SLA digunakan.

## 11.3 Maintenance

- Resepsionis/Housekeeping dapat melaporkan masalah dengan deskripsi, foto opsional, tingkat urgensi, dan kamar/aset terkait.
- Manager menyetujui atau menetapkan kamar ke status maintenance/disable sesuai otorisasi.
- Work order mencakup status, penanggung jawab, jadwal, deskripsi pekerjaan, biaya bila ada, dan penyelesaian.
- Kamar maintenance/disable tidak dapat dipilih untuk reservasi selama status berlangsung.
- Pengaktifan ulang memerlukan verifikasi bahwa perbaikan selesai dan kamar layak digunakan.
- Riwayat maintenance tersimpan untuk kamar dan aset yang relevan.

---

# 12. Layanan Tambahan Kamar

## 12.1 Katalog layanan

Contoh: extra breakfast, extra bed, amenities tambahan, laundry bila dipakai, permintaan khusus, dan item lain yang ditentukan Manager.

Setiap layanan memiliki nama, kategori, deskripsi, satuan, harga default, status aktif/nonaktif, aturan pajak/service charge, dan opsi pemetaan ke stok bila relevan.

## 12.2 Custom item dan override

- Petugas yang berhak dapat memilih layanan katalog dan mengganti harga untuk transaksi tertentu.
- Override tidak mengubah harga katalog.
- Petugas juga dapat membuat item custom dengan nama, QTY, satuan, harga satuan, subtotal, catatan, alasan dan pengguna.
- Override dapat memerlukan persetujuan jika melebihi batas yang ditentukan Manager.
- Item tambahan terhubung ke reservasi tamu, masuk ke total tagihan, memperhitungkan pajak/service charge, dan menghasilkan jurnal sesuai konfigurasi akuntansi.
- Jika item terhubung ke stok, pemakaian mengurangi stok hanya berdasarkan kuantitas yang dikonfigurasi/dicatat; item tanpa pemetaan stok tidak boleh otomatis mengurangi stok.

---

# 13. Harga, Rupiah, Pajak dan Service Charge

## 13.1 Format uang

- Input dan tampilan nominal memakai format IDR dengan pemisah ribuan titik dan tanpa angka desimal, misalnya `Rp 1.250.000`.
- Pengguna dapat mengetik angka tanpa pemisah; UI memformat ribuan saat input/blur tanpa merusak nilai numerik.
- Server harus memvalidasi dan menormalisasi nilai; jangan mempercayai hasil kalkulasi dari browser.
- Hindari floating-point untuk kalkulasi uang. Simpan nilai nominal IDR sebagai integer/big integer atau tipe numerik yang menjamin tidak ada kesalahan floating-point; persentase disimpan dengan presisi yang sesuai.
- Tidak ada koma desimal dalam nominal rupiah pada UI.
- Uji input kosong, angka nol, nominal besar, tanda negatif, karakter nonangka, dan pemformatan kembali.

## 13.2 Pajak

- Nilai awal konfigurasi: pajak daerah 10%, tetapi Manager dapat mengubahnya.
- Pajak dapat ditetapkan per kategori: kamar, meeting room, fine dining, layanan tambahan, dan kategori relevan lainnya.
- Pajak menjadi komponen terpisah yang ditambahkan pada subtotal sesuai baseline produk.
- Dasar perhitungan default setelah diskon (`after_discount`), tetapi implementasi harus mendukung pengaturan sesuai kategori dan ketentuan yang berlaku.
- Konfigurasi memiliki tarif, kategori, status aktif, tanggal efektif mulai/akhir, dan catatan dasar kebijakan.
- Tarif yang berubah hanya berlaku pada transaksi/periode yang sesuai; transaksi yang sudah dibukukan mempertahankan snapshot tarif, nominal pajak, dan aturan saat transaksi dibuat.
- Sebelum produksi, Manager harus memverifikasi tarif dan kategori pajak berdasarkan ketentuan yang relevan. Jangan menyatakan 10% sebagai penetapan hukum universal.

## 13.3 Service charge

- Terpisah dari pajak.
- Dapat diatur per kategori layanan, dengan persentase/nominal, tanggal efektif, status aktif, serta aturan dasar perhitungan.
- Disimpan sebagai komponen transaksi terpisah agar laporan pendapatan dan kewajiban dapat dipisahkan sesuai konfigurasi akun.
- Perubahan service charge tidak mengubah transaksi lama.

## 13.4 Urutan perhitungan default

Urutan yang disarankan:

1. Harga dasar dan tarif periode.
2. Tambahan layanan/item.
3. Diskon yang memenuhi syarat.
4. Subtotal kena pajak menurut kategori.
5. Pajak dan service charge sesuai konfigurasi masing-masing.
6. Total tagihan.
7. Pembayaran sah dan refund/reversal.
8. Saldo akhir.

Urutan tepat antara service charge dan pajak dapat perlu penyesuaian sesuai peraturan serta kebijakan kategori; simpan aturan tersebut dalam konfigurasi dan uji setiap kombinasi. Pembulatan nominal pajak dilakukan secara konsisten pada tingkat kategori/subtotal yang ditetapkan, bukan secara acak per render UI.

---

# 14. Akuntansi dan Laporan Keuangan

## 14.1 Dasar akuntansi

- Basis akrual (`accrual`) dan pembukuan berpasangan (`double-entry`).
- Setiap jurnal harus seimbang: total debit sama dengan total kredit.
- Jurnal otomatis berasal dari peristiwa transaksi, pembayaran, refund, pembelian, penerimaan persediaan, beban, utang, piutang, dan penyusutan sesuai pemetaan akun.
- Jurnal manual tersedia untuk Manager/permission yang ditetapkan, dengan periode, akun, debit/kredit, referensi, lampiran/catatan, dan alasan.
- Jurnal yang sudah diposting tidak boleh dihapus/diedit tanpa prosedur berotorisasi; gunakan reversal atau jurnal penyesuaian dan simpan audit.
- Periode akuntansi dapat ditutup/dikunci; transaksi pada periode terkunci harus melalui prosedur khusus Manager dan meninggalkan jejak.

## 14.2 Bagan akun

Manager/administrator berwenang dapat mengonfigurasi bagan akun minimal untuk:

- Aset: kas, bank, piutang, persediaan, aset tetap, akumulasi penyusutan.
- Liabilitas: uang muka pelanggan, utang usaha, kewajiban pajak/service charge bila relevan.
- Ekuitas/modal dan saldo awal.
- Pendapatan: kamar, meeting room, fine dining, layanan tambahan, serta pendapatan lain.
- Beban: biaya operasional, komisi OTA, utilitas, maintenance, persediaan terpakai, penyusutan, dan beban lain.

Struktur akun final dan saldo awal harus dikonfigurasi oleh pihak hotel sesuai praktik akuntansinya. Sistem tidak boleh mengarang saldo awal produksi.

## 14.3 Prinsip jurnal peristiwa

Implementer harus mendefinisikan dan mendokumentasikan mapping akun. Contoh prinsip (akun aktual configurable):

- Penerimaan DP: kas/bank bertambah dan uang muka pelanggan bertambah; belum otomatis menjadi pendapatan kamar.
- Pengakuan pendapatan kamar: pada saat layanan/malam menginap diperoleh sesuai kebijakan akrual, pendapatan kamar dan pelunasan uang muka/piutang diperbarui secara tepat.
- Pembayaran saldo piutang: kas/bank bertambah dan piutang berkurang.
- Penjualan layanan meeting/fine dining: pendapatan diakui sesuai peristiwa layanan/kebijakan akrual, bukan dikarenakan tanggal pembayaran saja.
- Refund: jurnal pembalik/penyesuaian berdasarkan transaksi asal, tidak menghapus pembayaran asal.
- Komisi OTA: dicatat terpisah sebagai biaya/akun terkait serta saldo settlement, tanpa menghitung pendapatan dua kali.
- Pembelian stok: persediaan/biaya dan utang/kas diakui sesuai alur pembelian serta kebijakan akuntansi.
- Pemakaian stok: persediaan berkurang dan beban/akun terkait bertambah jika proses pemakaian dikonfigurasi demikian.
- Aset tetap: nilai perolehan dicatat sebagai aset; beban penyusutan dan akumulasi penyusutan dihitung berdasarkan jadwal.

Semua contoh harus divalidasi ketika bagan akun dan aturan pajak hotel dikonfigurasi. Jurnal otomatis harus dapat ditelusuri ke reservasi, pembayaran, atau dokumen sumber.

## 14.4 Laporan wajib

- Jurnal umum.
- Buku besar per akun dan periode.
- Neraca saldo.
- Laporan laba rugi.
- Neraca.
- Laporan arus kas.
- Piutang pelanggan dan umur piutang bila relevan.
- Utang pemasok dan umur utang bila relevan.
- Pendapatan berdasarkan layanan, periode, sumber reservasi, tipe kamar, OTA, meeting room, dan fine dining.
- Pembayaran berdasarkan metode dan akun kas/bank.
- Refund, diskon, pajak dan service charge.
- Rekonsiliasi kasir/shift dan selisih kas.
- Biaya operasional dan persetujuan pengeluaran.
- Komisi serta settlement OTA.
- Persediaan dan nilai stok.
- Aset tetap, akumulasi penyusutan, dan beban penyusutan.
- Laporan okupansi kamar, average daily rate (ADR), dan RevPAR jika data pendapatan kamar dan inventori tersedia dengan basis perhitungan yang konsisten.
- Analitik pelanggan: repeat guest, sumber pemesanan, segmentasi yang relevan, dan tren booking.

Laporan memiliki filter rentang tanggal/periode, kategori, status, sumber transaksi, dan ekspor sesuai izin. Setiap laporan harus menampilkan definisi angka, rentang periode, serta timestamp pembuatan untuk mencegah salah tafsir.

---

# 15. Pembayaran, Kasir, Pengeluaran dan Night Audit

## 15.1 Metode pembayaran

Metode pembayaran dapat diatur Manager, misalnya tunai, transfer bank, QRIS, kartu, atau metode lain. Pencatatan tetap manual dalam versi pertama. Pencatatan metode QRIS tidak berarti ada integrasi otomatis ke penyedia QRIS.

Setiap pembayaran memiliki:

- Nomor transaksi unik.
- Reservasi/tagihan yang terkait.
- Tanggal/waktu dan pengguna.
- Nominal.
- Metode pembayaran.
- Akun kas/bank penerima.
- Referensi/nomor transfer jika ada.
- Status verifikasi.
- Bukti pembayaran bila diperlukan.
- Catatan dan audit trail.

## 15.2 Shift kasir

Setiap shift menyimpan:

- Petugas dan waktu buka/tutup.
- Akun kas/bank yang berada dalam lingkup rekonsiliasi.
- Saldo kas awal.
- Total penerimaan berdasarkan metode.
- Pengeluaran kas yang sah.
- Saldo kas yang diharapkan sistem.
- Kas aktual berdasarkan hitung fisik.
- Selisih dan alasan.
- Status penutupan, pemeriksa, dan approval bila diperlukan.

Jika ada selisih, petugas mencatat alasan dan shift dapat ditandai perlu pemeriksaan. Manager meninjau selisih. Shift tidak boleh diam-diam disesuaikan agar cocok tanpa audit trail.

## 15.3 Pengeluaran dan persetujuan

- Pengeluaran di bawah atau sama dengan Rp500.000 mengikuti permission yang berlaku; batas ini dapat diubah oleh Manager.
- Pengeluaran **di atas Rp500.000** wajib mendapat persetujuan Manager berdasarkan baseline awal.
- Data: nomor, tanggal, kategori, penerima/vendor, deskripsi, nominal, metode/akun pembayaran, bukti, pemohon, status persetujuan, approver, serta catatan.
- Pemohon tidak boleh menyetujui pengeluarannya sendiri jika konflik tugas dapat dihindari.
- Pengeluaran yang ditolak tidak boleh memengaruhi kas/jurnal final.
- Persetujuan dan posting akuntansi harus idempotent agar tidak menggandakan jurnal.

## 15.4 Night audit/penutupan harian

- Hari operasional berganti pada tengah malam sesuai zona waktu hotel.
- Penutupan memakai checklist terpandu, bukan menutup seluruh transaksi secara otomatis tanpa pemeriksaan.
- Checklist minimal: status shift kasir, transaksi pembayaran, piutang, pembayaran pending, transaksi/refund, reservasi yang masih berjalan, pengakuan pendapatan sesuai kebijakan akrual, jurnal tidak seimbang, dan laporan penting.
- Sistem dapat mengizinkan penutupan dengan pengecualian hanya untuk role berwenang, disertai alasan dan audit.
- Reservasi yang masih menginap tetap berlanjut melewati pergantian tanggal.
- Penutupan hari tidak membuat status kamar checked-out dan tidak menghapus saldo terbuka.

---

# 16. Inventaris, Pembelian, Pemasok dan Aset Tetap

## 16.1 Persediaan

Persediaan meliputi barang habis pakai seperti amenities, bahan makanan, sabun, perlengkapan kebersihan, dan barang lain yang dikonfigurasi.

Master item:

- SKU/kode dan nama.
- Kategori dan satuan.
- Pemasok default opsional.
- Stok tersedia dan stok minimum.
- Lokasi/gudang bila digunakan.
- Harga/nilai rata-rata.
- Status aktif/nonaktif.
- Item terkait layanan jika relevan.

## 16.2 Pembelian/penerimaan

Versi pertama mendukung pemasok, pencatatan pembelian, penerimaan barang, barang masuk/keluar, stok minimum, dan penyesuaian. Alur pesanan pembelian yang kompleks dapat ditambahkan kemudian bila kebutuhan hotel bertambah.

Data pemasok: nama, kontak, alamat, nomor identitas usaha bila relevan, catatan, status.

Dokumen pembelian/penerimaan sekurang-kurangnya menyimpan:

- Pemasok dan referensi invoice pemasok.
- Tanggal dokumen dan tanggal penerimaan.
- Item, kuantitas, satuan, harga satuan, diskon/pajak jika relevan, dan total.
- Status penerimaan dan pembayaran.
- Akun utang/kas/bank yang terkait.
- Petugas serta dokumen bukti.

Pembelian secara kredit membentuk utang pemasok sesuai mapping akun. Pembayaran utang mengurangi utang dan memperbarui kas/bank.

## 16.3 Metode penilaian stok

- Gunakan **weighted average** (rata-rata tertimbang) untuk menghitung nilai unit persediaan.
- Pemakaian/keluarnya stok memperbarui kuantitas dan nilai berdasarkan rata-rata yang relevan.
- Setiap mutasi stok menyimpan item, jenis mutasi, kuantitas, tanggal, sumber/dokumen, petugas, biaya/nilai jika berlaku, dan saldo setelah mutasi.
- Jika item sudah memiliki histori transaksi, perubahan metode penilaian tidak boleh dilakukan sembarangan. Jika diperlukan, harus berupa migrasi akuntansi yang direncanakan dan berotorisasi.

## 16.4 Mutasi stok dan stock opname

Jenis mutasi minimal: pembelian/penerimaan, penggunaan, pemindahan lokasi, pengembalian, barang rusak, kehilangan, koreksi stock opname, dan pengeluaran lainnya.

- Stok tidak boleh turun di bawah nol kecuali Manager mengaktifkan aturan khusus dan ada alasan/audit; default menolak transaksi yang membuat stok negatif.
- Stock opname membandingkan hitungan fisik dan saldo sistem.
- Koreksi resmi membutuhkan persetujuan Manager.
- Koreksi mencatat jumlah sebelum/sesudah, selisih, alasan, petugas, approver dan dampak nilai akuntansi.
- Penggunaan barang di kamar atau layanan hanya memotong stok otomatis jika hubungan layanan-item stok serta kuantitasnya telah didefinisikan. Jika tidak, petugas mencatat pemakaian secara manual.

## 16.5 Aset tetap dan penyusutan

Aset tetap meliputi AC, TV, komputer, furnitur, mesin, peralatan hotel, dan item lain yang masuk kebijakan aset hotel.

Data aset:

- Kode aset unik dan nama.
- Kategori, serial number bila tersedia, lokasi/kamar.
- Tanggal perolehan dan mulai digunakan.
- Nilai perolehan.
- Nilai residu jika digunakan.
- Umur manfaat.
- Metode penyusutan (default garis lurus).
- Akun aset, akumulasi penyusutan dan beban penyusutan.
- Kondisi/status, foto, garansi, pemasok, serta riwayat maintenance.

- Penyusutan mengikuti jadwal akuntansi yang dikonfigurasi. Tanggal mulai, umur manfaat, dan residu harus dapat diatur sesuai kebijakan akuntansi hotel.
- Sistem mencegah penggandaan jurnal penyusutan untuk periode yang sama.
- Penghentian/penjualan aset membutuhkan tindakan berotorisasi serta perhitungan nilai buku dan jurnal pelepasan sesuai konfigurasi.
- Riwayat maintenance aset tetap dapat dihubungkan dengan work order.

---

# 17. Invoice, Kwitansi, QR Verifikasi dan Dokumen

## 17.1 Invoice dan kwitansi

- Dukung cetak thermal 80 mm dan PDF yang bisa dicetak pada kertas dokumen seperti A4.
- Invoice menunjukkan tagihan; kwitansi membuktikan pembayaran yang benar-benar diterima. Jangan menyamakan bukti reservasi dengan bukti pelunasan.
- Reservasi dapat memiliki banyak bukti pembayaran karena pembayaran DP/parsial berulang.
- Template kwitansi custom dapat mengatur nama hotel, logo, alamat/kontak, judul, catatan/footer, informasi reservasi, serta teks keterangan sesuai izin.
- Nilai yang dicetak harus berasal dari snapshot transaksi yang tersimpan dan bukan dihitung ulang dengan konfigurasi saat ini.
- Nomor reservasi, invoice, dan kwitansi unik dan format/prefix-nya dapat diatur Manager.
- Dokumen yang sudah terbit tidak boleh diubah diam-diam. Cetak ulang menandai salinan bila diperlukan; koreksi transaksi membuat dokumen koreksi/reversal sesuai proses.

## 17.2 QR verifikasi kwitansi

- QR mengarah ke halaman verifikasi publik dengan token acak/bertanda tangan yang sulit ditebak.
- QR tidak boleh berisi nomor identitas, alamat, nomor WhatsApp lengkap, atau data rahasia secara terbuka.
- Informasi publik minimal: nomor dokumen, tanggal, nominal yang relevan, dan status valid/terkoreksi/dibatalkan/pembayaran terverifikasi sesuai aturan.
- Halaman verifikasi tidak boleh menyediakan tautan ke data reservasi penuh atau file privat.
- Endpoint harus membatasi kebocoran data dan menerapkan validasi token.
- QR harus membedakan validitas dokumen dari status pelunasan: dokumen bisa asli tetapi hanya menunjukkan pembayaran sebagian.

## 17.3 Unggahan dokumen

- Bukti booking/bukti bayar boleh gambar atau dokumen dengan daftar ekstensi/MIME yang diizinkan dan batas ukuran yang dapat dikonfigurasi.
- Validasi dilakukan pada server; jangan mempercayai ekstensi filename saja.
- Gunakan nama file acak/UUID, hindari nama yang dapat menjalankan kode, dan blokir eksekusi file upload.
- Dokumen privat disimpan di lokasi yang tidak dapat dibrowse publik.
- Unduhan dilakukan lewat controller/endpoint yang memeriksa autentikasi dan permission.
- Catat akses/unduhan dokumen sensitif bila relevan.
- Dokumen identitas opsional, akses paling ketat, dan masa retensi mengikuti kebijakan hotel serta kewajiban yang berlaku.

---

# 18. Notifikasi dan Pesan WhatsApp

## 18.1 Notifikasi internal

Notifikasi dalam aplikasi dapat dikonfigurasi per peristiwa, minimal:

- Booking Website baru.
- Bukti pembayaran menunggu verifikasi.
- Kedatangan/check-in hari ini.
- Checkout hari ini.
- Booking pending hampir kedaluwarsa atau telah expired.
- Kamar kotor dengan tamu akan datang hari ini.
- PMR baru atau belum selesai.
- Laporan maintenance baru/perubahan status.
- Pengeluaran menunggu persetujuan.
- Refund atau diskon di atas batas menunggu persetujuan.
- Stok di bawah minimum.
- Penutupan shift/hari yang belum selesai atau memiliki selisih.

Setiap notifikasi memiliki penerima sesuai role/permission, tautan ke item terkait, waktu, status dibaca/belum, dan pengaturan aktif/nonaktif bila relevan. Hindari menyertakan data pribadi sensitif dalam notifikasi.

## 18.2 WhatsApp manual

- Sediakan link ke WhatsApp dari profil/reservasi dengan nomor yang telah dinormalisasi.
- Pesan yang sudah disiapkan dapat berisi ringkasan non-sensitif seperti nama sapaan, nomor reservasi, tanggal, dan permintaan konfirmasi.
- Pengguna harus menekan kirim di aplikasi WhatsApp; aplikasi tidak mengklaim pesan terkirim otomatis.
- Hindari menyertakan data identitas atau rincian pembayaran berlebihan pada pesan default.

---

# 19. Master Data dan Pengaturan

Menu konfigurasi Manager minimal mencakup:

1. Profil hotel, logo, alamat, kontak, website, dan kebijakan yang ditampilkan publik.
2. Nomor kamar dan tipe kamar.
3. Jam default check-in/check-out dan toleransi checkout.
4. Tarif weekday/weekend/high season serta periode khusus.
5. Promo, diskon, batas diskon dan kebijakan stacking.
6. Kebijakan anak/bayi, kapasitas kamar, dan extra guest.
7. Kebijakan reservasi Website, masa tahan booking, dan persyaratan konfirmasi.
8. Kebijakan pembatalan, No-Show, refund, DP, dan pelunasan.
9. Pajak per kategori dan tanggal berlaku.
10. Service charge per kategori dan tanggal berlaku.
11. Metode pembayaran dan akun kas/bank.
12. Prefix/format nomor reservasi, invoice, kwitansi, dan nomor dokumen lain.
13. Meeting room, kapasitas, durasi, fasilitas dan paket.
14. Fine dining, menu/paket dan kapasitas.
15. Katalog layanan kamar tambahan dan pemetaan item stok.
16. Bagan akun, periode akuntansi, serta konfigurasi jurnal.
17. Pemasok, item stok, lokasi stok, satuan, batas minimum, dan data aset.
18. Role/permission.
19. Aturan notifikasi.
20. Interval polling dashboard.
21. Jadwal dan retensi backup, sejauh didukung hosting.
22. Template invoice/kwitansi.
23. Status/identitas website booking.
24. Kebijakan retensi data dan akses dokumen.

Perubahan konfigurasi finansial wajib mempunyai waktu berlaku, audit trail, dan aturan agar tidak mengubah transaksi historis.

---

# 20. Usulan Entitas dan Struktur Database

Ini adalah rancangan entitas tingkat PRD; implementer boleh menormalkan nama tabel dan relasi dengan tetap mempertahankan seluruh makna, integritas, dan alur audit. Semua tabel perlu timestamps yang relevan. Gunakan foreign key, unique constraint, index, dan transaksi database untuk menjaga konsistensi.

## 20.1 Pengguna dan konfigurasi

- `users`: akun pengguna, role/status bila menggunakan satu role utama.
- `roles`, `permissions`, `role_permissions`, dan `user_permissions` bila menggunakan permission granular.
- `hotel_settings`: pengaturan properti.
- `numbering_sequences`: prefix, format, dan sequence dokumen.
- `audit_logs`: aktivitas dan perubahan penting.
- `notifications`, `notification_preferences`.
- `backup_runs` atau catatan administrasi backup jika mekanismenya dapat dicatat.

## 20.2 Kamar dan reservasi

- `room_types`.
- `rooms`.
- `room_status_histories`.
- `room_cleaning_histories` atau model histori/status yang setara.
- `rate_plans`.
- `room_rate_rules` / `seasonal_rates`.
- `promotions` dan relasi target promo.
- `customers`.
- `reservations`.
- `reservation_guests` jika perlu memisahkan pemesan/penghuni.
- `reservation_rooms` atau `room_allocations` agar satu reservasi dapat mempunyai alokasi/room move yang terlacak.
- `reservation_nightly_rates` atau snapshot rincian per malam.
- `reservation_status_histories`.
- `reservation_cancellation_records`.
- `reservation_attachments` untuk metadata dokumen privat.
- `ota_channels` atau penyimpanan nama kanal configurable.

Reservasi minimal menyimpan nomor reservasi, sumber, status reservasi, pelanggan, tanggal check-in/check-out, jumlah malam, jumlah dewasa/anak/bayi, tipe kamar, alokasi kamar bila ada, snapshot tarif/diskon/pajak/service charge, total, saldo, catatan, pengguna pembuat, timestamp, dan data konfirmasi.

## 20.3 Tagihan, pembayaran dan dokumen

- `invoices`.
- `invoice_items`.
- `payments`.
- `payment_allocations` bila satu pembayaran dapat dialokasikan ke beberapa tagihan atau sebaliknya.
- `refunds`/`payment_reversals`.
- `payment_verifications`.
- `receipt_documents` atau metadata nomor dokumen.
- `receipt_templates`.
- `public_verification_tokens` atau mekanisme token QR.
- `additional_services`.
- `reservation_service_items`.
- `tax_rules`, `service_charge_rules`, `tax_components`, `charge_components` atau model snapshot setara.
- `ota_settlements` dan `ota_deductions` jika diperlukan untuk mengelola komisi/potongan secara terpisah.

## 20.4 Meeting room dan fine dining

- `meeting_rooms`.
- `meeting_packages` dan `meeting_package_items`.
- `meeting_reservations`.
- `meeting_reservation_items`.
- `dining_areas`/`dining_tables` jika fitur lokasi/meja diaktifkan.
- `dining_menus`/`dining_packages`.
- `dining_reservations`.
- `dining_reservation_items`.

Model reservasi layanan harus menyimpan slot waktu, kapasitas/pax, item/harga snapshot, status, pelanggan/pemesan, pembayaran dan referensi invoice.

## 20.5 Housekeeping dan maintenance

- `housekeeping_tasks`.
- `pmr_requests` atau model tugas yang setara.
- `maintenance_reports`.
- `maintenance_work_orders`.
- `maintenance_work_order_items` atau biaya terkait jika dibutuhkan.
- Relasi work order ke kamar/aset dan attachment privat.

## 20.6 Akuntansi dan kasir

- `accounts`/`chart_of_accounts`.
- `accounting_periods`.
- `journal_entries`.
- `journal_lines`.
- `accounting_mappings` untuk konfigurasi peristiwa-ke-akun.
- `cashier_shifts`.
- `cashier_transactions` atau tautan ke pembayaran/pengeluaran.
- `cash_reconciliations`.
- `expenses`.
- `expense_approvals` atau status/relasi approval.
- `vendors`.
- `vendor_bills` / `purchase_documents`.
- `vendor_payments`.
- `accounts_receivable`/`accounts_payable` jika tidak dimodelkan murni melalui subledger transaksi.
- `asset_depreciation_entries`.

Jurnal harus memiliki reference type/id ke peristiwa asal agar mudah direkonsiliasi. Hindari hanya menyimpan jurnal tanpa referensi sumber.

## 20.7 Inventaris dan aset

- `inventory_categories`.
- `inventory_items`.
- `inventory_locations` bila digunakan.
- `inventory_movements`.
- `stocktakes` dan `stocktake_lines`.
- `purchase_documents` dan `purchase_items`.
- `vendors`.
- `fixed_assets`.
- `asset_categories`.
- `asset_maintenance_histories` atau relasi ke work order.

## 20.8 Ketentuan rancangan database

- Semua nominal IDR disimpan dengan tipe yang aman bagi hitungan Rupiah tanpa pecahan; persentase terpisah.
- Setiap dokumen bernomor memiliki unique constraint.
- Gunakan soft delete hanya pada master data yang sesuai; jangan otomatis menggunakan soft delete untuk semua tabel.
- Transaksi finansial, jurnal, pembayaran, mutasi stok, dan audit tidak dihapus permanen melalui antarmuka normal.
- Terapkan foreign key dan index pada nomor reservasi, nomor dokumen, tanggal, status, sumber booking, relasi kamar, relasi pelanggan, dan reference jurnal.
- Cegah duplikasi jurnal dengan unique idempotency/reference key berdasarkan peristiwa sumber.
- Snapshot harga, pajak, service charge, diskon, deskripsi item, dan nama pelanggan yang tercetak harus disimpan ketika dokumen/booking dikonfirmasi.
- Gunakan transaksi database untuk update berantai pada reservasi, alokasi, pembayaran, stok, dan jurnal.
- Retensi atau penghapusan data pribadi harus mengikuti kebijakan hotel dan kewajiban yang berlaku; jangan menghapus data finansial yang perlu dipertahankan tanpa prosedur.

---

# 21. Ketentuan Shared Hosting cPanel Tanpa Symbolic Link

**Bagian ini wajib dipatuhi. Shared hosting target tidak mendukung symbolic link. Dilarang menjadikan symbolic link sebagai prasyarat build, deployment, upload, atau operasi aplikasi.**

## 21.1 Larangan eksplisit

- Jangan mengandalkan `php artisan storage:link`.
- Jangan mengandalkan `public/storage` yang merupakan symlink.
- Jangan membuat symbolic link lain untuk menghubungkan direktori aplikasi, `vendor`, `storage`, atau `public_html`.
- Jangan membuat instruksi deployment yang mensyaratkan akses SSH/root jika hosting tidak menyediakannya.
- Jangan menyimpan dokumen privat di direktori yang bisa diakses langsung melalui web.

## 21.2 Pola penempatan file yang disarankan

Struktur aktual tergantung paket hosting, tetapi prinsipnya:

- Simpan aplikasi Laravel, `.env`, `vendor`, source, dan storage privat di direktori yang tidak dapat diakses langsung oleh publik, jika struktur hosting memungkinkan.
- Web root/domain mengarah ke isi direktori `public` Laravel dengan **menyalin/menempatkan file yang diperlukan**, bukan symbolic link.
- Jika cPanel hanya menyediakan `public_html`, gunakan pola deployment tanpa symlink: tempatkan project di luar `public_html` jika memungkinkan, lalu salin file front controller dan aset publik yang dibutuhkan ke web root dan sesuaikan jalur absolut pada front controller agar memanggil `vendor/autoload.php` serta `bootstrap/app.php` dari lokasi aplikasi yang benar.
- Jika domain/subdomain dapat diatur document root-nya ke direktori `public` melalui konfigurasi cPanel tanpa symbolic link, opsi ini boleh dipakai. Itu perubahan konfigurasi document root, bukan pembuatan symlink.
- Jangan menaruh `.env`, `vendor`, `storage/app/private`, backup, atau source code sensitif di bawah web root yang dapat diunduh.
- Aset publik yang dikelola aplikasi dan memang boleh diakses publik (logo, gambar galeri hotel) disimpan dengan metode yang tidak membutuhkan symlink, misalnya folder publik khusus yang writable secara terbatas dan URL/pemetaan path eksplisit.

## 21.3 File privat tanpa `storage:link`

- Simpan bukti transfer, dokumen identitas, bukti booking, attachment maintenance, backup sementara, dan dokumen internal pada disk/path privat di luar web root bila memungkinkan.
- Download/view harus melalui endpoint Laravel dengan autentikasi dan pemeriksaan permission, atau mekanisme server yang setara dan terlindungi.
- Controller memvalidasi pengguna yang sedang login dan izin akses terhadap reservasi/dokumen tertentu sebelum mengirim file.
- Gunakan nama file acak, validasi jenis/ukuran, dan `Content-Disposition` yang aman.
- Jangan membuat route publik berdasarkan nama file yang dapat ditebak.
- Jangan menyelesaikan keterbatasan symbolic link dengan menyalin seluruh dokumen privat ke `public_html`.
- Bila penyimpanan privat di luar web root benar-benar tidak dimungkinkan, implementer harus menjelaskan risiko dan menggunakan proteksi server yang terverifikasi; tidak boleh menyatakan aman hanya karena URL disembunyikan.

## 21.4 Aset frontend

- Build aset frontend ke direktori `public/build` atau struktur publik lain yang didukung build tool tanpa membuat symlink.
- File build publik harus benar-benar tersedia dalam deployment; jangan mengandalkan path lokal development.
- Jika menggunakan Vite, pastikan manifest build tersalin dengan benar dan URL aset bekerja pada subfolder/domain yang aktual.
- Jangan membutuhkan Node.js berjalan di server produksi jika hosting tidak mendukungnya; jalankan build di lingkungan development/CI kemudian unggah file hasil build.

## 21.5 Cron, scheduler, dan antrean

- Dokumentasikan perintah cron cPanel untuk scheduler Laravel, umumnya pemanggilan `artisan schedule:run` setiap menit dengan path PHP dan path aplikasi yang tepat untuk akun hosting. Path final harus diverifikasi di server.
- Tugas booking expiry, notifikasi terjadwal, dan backup harus dirancang agar dapat berjalan sebagai scheduled task.
- Gunakan queue driver `database` atau `sync` sesuai kebutuhan/dukungan hosting. Jangan mewajibkan Redis atau worker yang berjalan terus-menerus.
- Bila memakai database queue, dokumentasikan proses menjalankan worker yang kompatibel dengan batasan hosting. Untuk pekerjaan yang harus berjalan tanpa worker persisten, gunakan scheduler/command yang memproses pekerjaan terbatas secara idempotent.
- Buat health/status admin sederhana untuk memperlihatkan kapan scheduler terakhir berjalan jika secara teknis memungkinkan.

## 21.6 Backup shared hosting

- Backup harian database dan file penting.
- Retensi default 30 hari, tetapi dapat dikonfigurasi.
- Simpan backup terpisah dari folder publik dan, bila paket hosting mendukung, salinan kedua di luar server utama.
- Backup tidak boleh dapat diakses melalui URL publik.
- Jangan membuat symlink untuk tujuan backup.
- Dokumentasikan cara backup manual melalui cPanel/command yang tersedia dan cara restore.
- Uji restore berkala di lingkungan aman; backup yang belum pernah diuji tidak boleh dianggap tervalidasi.
- Hindari membebani request web biasa dengan proses backup besar; jalankan melalui cron atau prosedur yang didukung.

## 21.7 Checklist preflight deployment

Sebelum produksi, verifikasi:

1. Versi PHP dan ekstensi yang dibutuhkan oleh versi Laravel terpilih.
2. Versi MySQL/MariaDB dan charset/collation.
3. Composer/vendor tersedia dan dependency dikunci.
4. `APP_KEY`, `.env`, mode production/debug, URL aplikasi, timezone.
5. Folder cache/log yang dibutuhkan writable dengan permission minimum.
6. Lokasi private storage tidak dapat diakses publik.
7. Front controller menggunakan jalur absolut yang tepat tanpa symlink.
8. Aset frontend ter-build dan dapat diakses.
9. Cron/scheduler berfungsi.
10. Email/notifikasi jika ada, meskipun email bukan ketergantungan wajib baseline.
11. Backup dan prosedur restore teruji.
12. SSL/HTTPS aktif.
13. Endpoint QR verification dan file download diuji dengan akun tanpa izin.
14. Tidak ada file `.env`, dump database, log, backup, atau source sensitif di web root publik.
15. `APP_DEBUG=false` dan error production tidak menampilkan stack trace.

---

# 22. Kebutuhan UI/UX

## 22.1 Prinsip umum

- Desain modern, profesional, bersih, mudah dibaca, dan responsif.
- Navigasi berbasis modul dan role; menu yang tidak diizinkan tidak ditampilkan, tetapi kontrol backend tetap wajib.
- Formulir dipecah menjadi bagian yang logis, memberi validasi langsung, serta menunjukkan pesan kesalahan yang jelas.
- Status menggunakan badge/label konsisten dengan teks, bukan warna saja.
- Aksi berisiko (refund, pembatalan, jurnal, koreksi stok, disable kamar) harus meminta konfirmasi dan alasan bila relevan.
- Berikan pencarian dan filter pada halaman data besar.
- Gunakan pagination dan server-side query; hindari memuat seluruh transaksi sekaligus.
- Tampilkan loading, empty state, error state, dan sukses secara konsisten.
- Cegah submit berulang ketika operasi sedang berlangsung.

## 22.2 Form harga rupiah

Semua kolom harga menggunakan komponen input Rupiah yang:

- Memformat pemisah ribuan titik.
- Tidak menampilkan angka desimal.
- Menormalkan input menjadi nilai numerik valid di server.
- Menampilkan ringkasan subtotal, diskon, pajak, service charge dan total secara jelas.
- Tidak menggunakan format nilai dari browser sebagai dasar otoritatif.

## 22.3 Menu minimal

- Dashboard.
- Website/Booking Website.
- Reservasi Kamar.
- Kalender Reservasi.
- Kendali Kamar.
- Data Pelanggan.
- Meeting Room.
- Fine Dining.
- Layanan Tambahan.
- Pembayaran/Invoice/Kwitansi.
- Kasir dan Penutupan Harian.
- Akuntansi.
- Pengeluaran dan Persetujuan.
- Inventaris dan Pembelian.
- Aset Tetap dan Maintenance.
- Laporan dan Analitik.
- Housekeeping/PMR.
- Notifikasi.
- Pengaturan/Master Data.
- Pengguna, Role, dan Permission.
- Audit Trail.

Menu harus diatur berdasarkan role dan permission. Tidak semua menu tampil pada semua role.

---

# 23. Non-Functional Requirements

## 23.1 Performa

- Halaman dashboard utama ditargetkan terasa responsif pada data operasional wajar di shared hosting.
- Gunakan index untuk kolom filter/relasi penting, query agregasi efisien, cache terbatas untuk master data yang aman, dan pagination.
- Polling dashboard default 30 detik; hindari query berat di setiap polling.
- Jangan mengunci aplikasi pada layanan infrastructure yang tidak tersedia pada shared hosting.
- Operasi keuangan/inventaris yang membutuhkan konsistensi lebih penting daripada pembaruan UI instan.

## 23.2 Keamanan dan privasi

- HTTPS wajib untuk produksi.
- Validasi/otorisasi pada semua endpoint.
- CSRF, XSS output escaping, SQL injection defense, rate limiting login, secure cookies/session, password hashing.
- Upload privat tervalidasi dan tidak dapat dieksekusi.
- Data identitas dan kontak hanya dapat dilihat berdasarkan permission.
- Jangan menaruh data pribadi pada URL/token QR atau log aplikasi yang tidak perlu.
- Log aplikasi tidak menyimpan password, token rahasia, atau seluruh nomor identitas tanpa alasan.
- Lindungi IDOR dengan pemeriksaan akses atas setiap objek terkait, termasuk unduhan berkas.
- Berikan penanganan yang aman untuk request ekspor data.

## 23.3 Keandalan dan integritas

- Transaksi database untuk posting jurnal, pembayaran, pemakaian stok, booking/alokasi, dan approval.
- Idempotency untuk scheduled job dan posting jurnal.
- Constraint unik untuk nomor dokumen.
- Nomor dokumen tidak digunakan kembali.
- Tidak ada delete permanen dari antarmuka untuk transaksi finansial atau audit.
- Validasi waktu dan ketersediaan dilakukan server-side.
- Semua waktu disimpan/diproses konsisten; UI menampilkan waktu `Asia/Jakarta`.

## 23.4 Aksesibilitas dan kompatibilitas

- Mendukung desktop, tablet, dan ponsel modern.
- Teks, tombol, kontras, status, dan formulir tetap terbaca pada layar kecil.
- Form penting dapat digunakan tanpa hover.
- Tabel besar menyediakan responsivitas dan filter.

---

# 24. Acceptance Criteria (Kriteria Penerimaan)

Aplikasi baru dapat dianggap memenuhi PRD jika paling tidak lolos kriteria berikut.

## 24.1 Master data

- Manager dapat menambah/mengubah/menonaktifkan tipe kamar, kamar fisik, meeting room, layanan, menu, metode pembayaran, akun kas/bank, pajak, service charge, dan konfigurasi relevan.
- Perubahan master data tidak mengubah snapshot transaksi yang telah dikonfirmasi/dibukukan.
- Kamar yang memiliki reservasi aktif tidak bisa dihapus sehingga menghilangkan histori.

## 24.2 Booking dan ketersediaan

- Sistem menerima sumber Walk-In, OTA, Website, dan WhatsApp.
- Booking Website dapat dinonaktifkan oleh Manager; endpoint backend ikut menolak booking baru ketika nonaktif.
- Booking Website pending memegang inventori selama dua jam sesuai konfigurasi dan kedaluwarsa melalui scheduler aktif.
- Dua request bersamaan tidak dapat memesan inventori yang sama melewati kapasitas.
- Reservasi dapat memakai tipe kamar tanpa kamar fisik atau memilih kamar lebih awal.
- Extend memeriksa kembali ketersediaan dan menghitung tarif pada tanggal tambahan.
- Kamar maintenance/disable tidak dapat dialokasikan.
- Pembatalan/No-Show mempertahankan histori dan mengikuti aturan konfigurasi.

## 24.3 Harga dan uang

- Harga weekday, weekend, high season, harga khusus, promo, diskon, pajak, service charge, dan layanan custom dihitung sesuai konfigurasi.
- Input `1250000` ditampilkan sebagai `Rp 1.250.000` tanpa desimal.
- Total yang disimpan server konsisten dengan ringkasan yang ditampilkan.
- Perubahan pajak/harga setelah reservasi dikonfirmasi tidak mengubah transaksi historis.
- Override harga/diskaun yang sensitif membutuhkan permission dan audit sesuai aturan.

## 24.4 Check-in/checkout dan housekeeping

- Check-in memerlukan reservasi yang valid dan kamar yang tersedia, aktif, serta siap.
- Checkout mengubah reservasi ke checked-out dan otomatis menjadikan kamar kotor.
- Kamar tidak dapat dialokasikan untuk tamu baru sebelum proses kebersihan dan inspeksi selesai jika kebijakan mensyaratkan pemeriksaan.
- PMR membuat tugas yang dapat diterima, diproses, dan diselesaikan.
- Kamar kotor dengan reservasi hari ini diprioritaskan pada dashboard housekeeping.
- Maintenance/disable mencegah alokasi kamar.

## 24.5 Meeting room dan fine dining

- Meeting room dapat dipesan dengan paket all-in atau tarif per pax.
- Durasi fleksibel dan benturan waktu diperiksa.
- Fasilitas dapat dipilih dari template dan/atau ditambah manual.
- Fine dining menerima reservasi berdasarkan tanggal, waktu, pax, kapasitas, menu/paket, dan item custom.
- Harga custom tersimpan per transaksi dan tidak mengubah harga katalog.
- Semua transaksi terhubung ke pembayaran, dokumen, dan jurnal yang sesuai.

## 24.6 Pembayaran dan kwitansi

- Reservasi mendukung DP dan pembayaran parsial berkali-kali.
- Saldo tagihan dihitung otomatis.
- Refund memerlukan persetujuan Manager dan menghasilkan pencatatan balik tanpa menghapus pembayaran asli.
- Invoice dan kwitansi dapat dicetak dalam format thermal 80 mm dan PDF.
- QR memverifikasi dokumen dan status pembayaran tanpa menampilkan data sensitif.
- Nomor reservasi/invoice/kwitansi unik dan tidak digunakan ulang.

## 24.7 Akuntansi dan kasir

- Jurnal berpasangan selalu seimbang.
- Posting ulang event yang sama tidak menghasilkan jurnal duplikat.
- DP tidak langsung diakui sebagai pendapatan kamar tanpa peristiwa pengakuan sesuai kebijakan akrual.
- Laporan keuangan menghasilkan jurnal umum, buku besar, neraca saldo, laba rugi, neraca, arus kas, piutang, utang, serta laporan pendapatan.
- Pengeluaran di atas Rp500.000 tidak dapat diposting sebagai final sebelum persetujuan Manager.
- Kasir dapat membuka/menutup shift dan mencatat selisih kas.
- Penutupan harian menggunakan checklist; transaksi menginap yang berjalan tidak di-checkout otomatis.

## 24.8 Inventaris dan aset

- Penerimaan/pembelian menambah stok sesuai dokumen.
- Mutasi pemakaian mengurangi stok secara konsisten.
- Weighted average berjalan konsisten dan nilai stok bisa direkonsiliasi.
- Koreksi stock opname memerlukan persetujuan Manager.
- Layanan hanya memotong stok otomatis bila pemetaan item/kuantitas tersedia.
- Penyusutan garis lurus menghasilkan jurnal sesuai jadwal tanpa duplikasi.

## 24.9 Role, keamanan dan audit

- Housekeeping tidak bisa mengakses laporan keuangan atau endpoint pembayaran dengan mengubah URL secara manual.
- Resepsionis tidak bisa menyetujui refund/ubah konfigurasi sensitif tanpa permission.
- Perubahan diskon, harga, status kamar, transaksi dan ekspor sensitif dicatat dalam audit trail.
- Dokumen privat tidak bisa diakses oleh pengguna tanpa permission.
- QR verifikasi tidak membuka data reservasi lengkap.
- Semua upload divalidasi di server.

## 24.10 Shared hosting tanpa symlink

- Aplikasi dapat berjalan pada cPanel/shared hosting tanpa symbolic link.
- Tidak diperlukan `storage:link`.
- Aset frontend publik dapat diakses melalui path normal.
- Dokumen privat tetap privat melalui penyimpanan nonpublik dan endpoint terotorisasi.
- Scheduler/expiry bekerja dengan cron yang didokumentasikan.
- Backup tidak tersedia melalui URL publik.
- Tidak ada file `.env`, backup, log, atau source sensitif yang dapat diunduh dari web root.

---

# 25. Rencana Pengujian

## 25.1 Pengujian unit

- Perhitungan tarif weekday/weekend/high season.
- Urutan prioritas tarif dan validasi periode.
- Diskon dan stacking.
- Pajak/service charge per kategori.
- Format Rupiah dan validasi jumlah.
- Perhitungan malam dan extend.
- Saldo tagihan, pembayaran parsial, refund, dan status pembayaran.
- Weighted average stok dan penyusutan garis lurus.
- Posting jurnal yang seimbang dan idempotent.
- Nomor dokumen unik.

## 25.2 Pengujian integrasi

- Reservasi → invoice → DP → check-in → pengakuan pendapatan → checkout → kwitansi.
- Booking Website → upload bukti → verifikasi → konfirmasi.
- Pending booking → scheduler expiry → release inventori.
- OTA → catatan komisi/potongan → settlement → jurnal.
- Layanan tambahan → tagihan → stok (jika dipetakan) → jurnal.
- Meeting room/fine dining → pembayaran → laporan.
- Pembelian → penerimaan stok → utang → pembayaran → jurnal.
- Pemakaian stok → nilai persediaan/beban.
- Aset tetap → jadwal penyusutan → jurnal.
- Shift kasir → rekonsiliasi → penutupan hari.

## 25.3 Pengujian keamanan

- Akses lintas role dan IDOR.
- Upload file berbahaya/ekstensi palsu.
- Akses dokumen privat tanpa login/permission.
- Manipulasi nilai harga/pajak dari request browser.
- Double submit dan double booking bersamaan.
- CSRF/XSS, rate limit login, session timeout.
- QR token acak dan endpoint verifikasi tanpa kebocoran PII.
- Akses ekspor dan endpoint approval.
- Pastikan `.env`, backup, log, dan source tidak dapat diakses publik.

## 25.4 Pengujian UAT operasional

Lakukan skenario langsung bersama perwakilan Manager, Resepsionis, dan Housekeeping. UAT harus mencakup hari sibuk, booking dari berbagai kanal, kamar kotor dengan kedatangan hari ini, extend dengan konflik, refund, diskon di atas batas, pembelian stok, penutupan kas berbeda, dan pemulihan backup.

---

# 26. Tahapan Implementasi yang Disarankan

Antigravity harus menerapkan bertahap, memvalidasi setiap fase, dan menjaga build tetap dapat dijalankan. Jangan mencoba membangun semua modul sekaligus tanpa pengujian integrasi.

## Fase 0 — Pemeriksaan dan fondasi

- Periksa versi Laravel/PHP yang didukung paket shared hosting.
- Buat project dan struktur deployment tanpa symlink.
- Setup autentikasi, role/permission, konfigurasi, audit trail, format Rupiah, layout responsif, backup dasar, dan tes.
- Dokumentasikan jalur web root cPanel serta cara akses private storage.

## Fase 1 — Master data dan reservasi kamar

- Master hotel, tipe kamar, kamar, tarif, promo, konfigurasi booking, customer, dan kanal reservasi.
- Ketersediaan, kalender, booking Website/manual, hold 2 jam, pembatalan, dan alokasi kamar.
- Cegah double booking dan lakukan UAT.

## Fase 2 — Front office dan housekeeping

- Check-in, extend, room move, checkout, status kamar, cleaning workflow, PMR, maintenance, dan dashboard kendali kamar.
- Polling 30 detik, prioritas kamar kotor dan check-in hari ini.

## Fase 3 — Pembayaran dan dokumen

- Tagihan, DP/pembayaran parsial, metode pembayaran, OTA settlement, kwitansi thermal/PDF, QR verification, layanan tambahan, dan approval refund.

## Fase 4 — Meeting room dan fine dining

- Master resource, kalender, paket, harga per pax/all-in, item custom, pembayaran, dan laporan layanan.

## Fase 5 — Akuntansi dan kasir

- Bagan akun, pemetaan jurnal, uang muka, pengakuan pendapatan, kasir per shift, pengeluaran, utang/piutang, laporan, guided night audit.

## Fase 6 — Inventaris dan aset

- Pemasok, pembelian, penerimaan, stok, weighted average, stock opname, mapping layanan ke stok, aset tetap, maintenance, dan penyusutan.

## Fase 7 — Analitik, hardening, dan deployment

- Analitik pelanggan, ekspor PDF/XLSX/CSV, pemeriksaan keamanan, backup dan restore, scheduler cPanel, UAT akhir, data awal, dokumentasi pengguna, serta deployment produksi.

Urutan dapat disesuaikan oleh Antigravity jika ada dependensi teknis, tetapi setiap perubahan urutan harus menjaga integritas keuangan dan tidak menghilangkan penerimaan fase.

---

# 27. Instruksi Kerja Khusus untuk Antigravity

1. **Baca dokumen ini sepenuhnya sebelum mengubah atau membuat kode.** Buat checklist requirement yang menghubungkan setiap persyaratan dengan modul, migration, route, policy, UI, dan test.
2. Periksa lingkungan dan versi framework yang kompatibel; jangan memilih dependency yang tidak tersedia/berisiko pada shared hosting.
3. Buat rencana implementasi bertahap. Setelah tiap fase, jalankan test dan laporkan berkas utama yang dibuat/diubah, keputusan teknis, dan fitur yang belum selesai.
4. Jangan mengganti requirement dengan mockup semata. Tombol dan menu utama harus berfungsi sesuai alur yang terdokumentasi.
5. Jangan menggunakan symbolic link di tahap mana pun. Jangan menjalankan `php artisan storage:link` sebagai bagian instruksi implementasi/deployment.
6. Semua nilai sensitif seperti URL produksi, password, key, credential bank, dan data tamu riil tidak boleh ditanamkan di source code. Gunakan konfigurasi environment yang aman.
7. Semua operasi berisiko dan finance/inventory harus divalidasi di backend dan dibungkus transaksi database bila diperlukan.
8. Gunakan pola layanan/domain yang jelas untuk kalkulasi tarif, pajak/service charge, saldo tagihan, jurnal, alokasi inventori, dan stok. Hindari menyalin rumus perhitungan ke banyak controller.
9. Semua harga yang dikonfirmasi disimpan sebagai snapshot. Perubahan master data hanya berlaku untuk transaksi baru kecuali proses perubahan yang berotorisasi.
10. Jangan menghapus transaksi pembayaran, jurnal, stok, atau audit untuk memperbaiki kesalahan. Terapkan reversal/adjustment.
11. Implementasikan seluruh role dan permission server-side; uji pemanggilan langsung endpoint tanpa izin.
12. Gunakan data demo yang jelas ditandai; sediakan cara membuat data awal produksi tanpa membuat booking/angka keuangan palsu.
13. Buat README instalasi dan deployment khusus cPanel tanpa symbolic link yang memuat struktur folder, penyesuaian front controller, build aset, konfigurasi `.env`, migration, cron, private file storage, backup/restore, dan checklist preflight.
14. Jangan mengklaim backup, scheduler, atau expiry booking otomatis berfungsi sebelum dikonfigurasi dan diuji pada hosting target.
15. Jangan mengunci tarif pajak sebagai ketentuan hukum; jadikan konfigurasi dan dokumentasikan verifikasi sebelum produksi.
16. Setelah seluruh implementasi, sediakan laporan kesesuaian: requirement, status implementasi, test yang dijalankan, serta gap yang belum selesai. Jangan menyatakan selesai jika acceptance criteria belum diuji.

---

# 28. Asumsi, Risiko, dan Hal yang Harus Dikonfirmasi saat Setup Produksi

Keputusan bisnis dinilai cukup lengkap untuk pengembangan, namun nilai data operasional berikut harus diisi atau diverifikasi sebelum peluncuran produksi:

1. Nama/nomor kamar, tipe dan kapasitas aktual.
2. Harga dasar, kalender weekday/weekend/high season, promo, dan tanggal aktif.
3. Kebijakan DP, persentase/nominal, syarat pelunasan, late checkout, refund, cancellation, dan No-Show.
4. Batas usia anak/bayi, kapasitas maksimum dan biaya extra guest.
5. Tarif pajak dan service charge per kategori beserta tanggal efektif dan dasar yang telah diverifikasi.
6. Bagan akun, saldo kas/bank, modal, utang, piutang, dan saldo awal stok/aset.
7. Data vendor, daftar stok, harga, lokasi, dan daftar aset awal.
8. Kebijakan khusus hotel syariah terkait dokumen/penerimaan tamu yang diputuskan pihak hotel.
9. Kapabilitas nyata hosting: PHP/ekstensi, document root, cron, ukuran storage, backup eksternal, dan akses file.
10. Kebijakan retensi dokumen identitas/bukti pembayaran berdasarkan kebutuhan operasional serta kewajiban yang berlaku.

Risiko utama:

- Shared hosting mungkin membatasi cron, ukuran file, waktu proses, memory, dan akses private storage; hal ini harus dibuktikan saat deployment.
- Tanpa API OTA, inventori OTA tidak tersinkron otomatis. Prosedur operasional untuk memperbarui portal OTA tetap dibutuhkan.
- Tanpa payment gateway, verifikasi pembayaran website bergantung pada ketelitian petugas.
- Akuntansi akrual membutuhkan pemetaan akun dan saldo awal yang benar; laporan tidak dapat dianggap valid jika data awal belum dikonfigurasi.
- Pajak memerlukan verifikasi aturan yang berlaku.
- Penggabungan profil pelanggan harus hati-hati untuk menghindari salah identifikasi.

---

# 29. Definisi Selesai (Definition of Done)

Satu modul dianggap selesai jika:

- Fungsinya sesuai requirement dan role yang ditentukan.
- Validasi UI dan backend tersedia.
- Transaksi database, relasi, index dan constraint yang diperlukan dibuat.
- Audit trail tersedia untuk tindakan sensitif.
- Responsif pada ponsel dan desktop.
- Empty/loading/error/success state ditangani.
- Unit/integration test terkait lulus.
- Tidak ada data penting yang hanya disimpan di state frontend.
- Dokumentasi penggunaan dan konfigurasi diperbarui.
- Modul tidak bergantung pada symbolic link.
- Tidak ada error kritis atau kebocoran data yang diketahui.

Aplikasi dianggap siap UAT jika seluruh modul inti tersambung, skenario acceptance criteria telah dijalankan, role test lulus, dan instalasi staging shared hosting berjalan tanpa symbolic link. Aplikasi hanya siap produksi setelah data master dan akun, kebijakan pajak, backup/restore, scheduler, SSL, dan dokumen operasional diverifikasi oleh pihak hotel.

---

# 30. Glosarium

- **ADR (Average Daily Rate):** pendapatan kamar dibagi jumlah kamar terjual pada periode terkait.
- **Audit trail:** catatan siapa melakukan tindakan apa dan kapan.
- **DP:** uang muka dari pelanggan.
- **Housekeeping:** pengelolaan kebersihan dan kesiapan kamar.
- **High season:** periode dengan aturan tarif khusus/kenaikan.
- **No-Show:** tamu tidak hadir sesuai reservasi berdasarkan aturan yang berlaku.
- **OTA:** Online Travel Agent pihak ketiga.
- **PMR:** Please Make Up Room, permintaan pembersihan kamar saat tamu menginap.
- **RevPAR:** pendapatan kamar dibagi kamar malam yang tersedia dalam periode yang konsisten.
- **Room move:** pemindahan tamu dari satu kamar ke kamar lain.
- **Snapshot transaksi:** salinan nilai penting pada saat transaksi dikonfirmasi agar tidak berubah ketika master data diperbarui.
- **Weighted average:** penilaian persediaan menggunakan rata-rata tertimbang biaya unit.
- **Night audit:** proses pemeriksaan/penutupan operasional dan keuangan harian.
- **Symbolic link/symlink:** tautan filesystem yang menunjuk ke file/direktori lain; tidak boleh menjadi ketergantungan SikolaNata3 pada shared hosting target.

---

**Akhir dokumen PRD SikolaNata3 v1.0**
