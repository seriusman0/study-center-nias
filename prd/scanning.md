Tentu, ini adalah draf **Product Requirements Document (PRD)** yang disesuaikan dengan kebutuhan pengembangan fitur absensi berbasis QR Code untuk aplikasi Anda.

---

# Product Requirements Document (PRD): Fitur Absensi Berbasis QR Code

**Status:** Draft / In Progress
**Fitur:** QR Code Attendance Scanner
**Lokasi:** `/presensi/{id}/edit`

## 1. Latar Belakang & Tujuan

Saat ini, proses absensi dilakukan secara manual dengan mengetik nama siswa. Untuk meningkatkan efisiensi dan kecepatan proses presensi di kelas, fitur ini dikembangkan untuk mendukung pemindaian QR Code yang berisi ID siswa.

**Tujuan:**

* Mempercepat proses absensi siswa.
* Mengurangi kesalahan manusia (*human error*) dalam penginputan.
* Memberikan umpan balik instan (visual dan audio) bagi siswa.

## 2. User Flow (Alur Pengguna)

1. User (Guru/Admin) membuka halaman `/presensi/{id}/edit`.
2. User menekan tombol **"Aktifkan Kamera"**.
3. Modal scanner terbuka dan kamera aktif.
4. User mengarahkan QR Code siswa ke box scanner.
5. Sistem memproses hasil scan (validasi ID & status absen).
6. Modal menampilkan info detail siswa dan status presensi.
7. User dapat terus melakukan scan tanpa menutup modal.
8. Jika terjadi kendala, User tetap bisa input manual di field yang tersedia.

## 3. Spesifikasi Teknis & Fungsionalitas

### 3.1. Antarmuka (UI) & Interaksi

* **Trigger:** Tombol "Aktifkan Kamera" di halaman edit presensi.
* **Modal Scanner:** * Menampilkan *viewfinder* (box) untuk pemindaian.
* Modal bersifat *persistent* (tidak tertutup otomatis setelah satu scan).
* Terdapat tombol untuk "Input Manual" jika QR Code tidak terbaca.


* **Feedback:**
* **Visual:** Muncul status (Success/Failed/Already Exists) beserta detail nama siswa.
* **Audio:** Suara notifikasi yang berbeda untuk:
* Berhasil (Scan sukses & data valid).
* Gagal (ID tidak ditemukan).
* Peringatan (Sudah melakukan absensi sebelumnya).





### 3.2. Logika Sistem (Backend)

* **Validasi ID:** Sistem harus memeriksa apakah ID dalam QR Code terdaftar dalam database siswa.
* **Status Presensi:** Sistem harus melakukan cek status:
1. Jika belum absen: Tandai sebagai hadir & kembalikan data user.
2. Jika sudah absen: Berikan notifikasi *"User sudah melakukan presensi"*.
3. Jika ID tidak valid: Berikan notifikasi *"ID Siswa tidak ditemukan"*.


* **Keamanan:** Memastikan hanya ID yang terotorisasi yang dapat mengubah data presensi.

## 4. Prioritas Fitur

| Prioritas | Fitur | Deskripsi |
| --- | --- | --- |
| **High** | QR Scanner Engine | Integrasi library scanner (misal: `html5-qrcode`). |
| **High** | Validasi ID | Logic pengecekan database siswa & update status presensi. |
| **Medium** | Audio Feedback | Implementasi file audio untuk status sukses/gagal. |
| **Low** | UI/UX Improvement | Penyesuaian responsivitas modal kamera. |

## 5. Rencana Pengujian (Test Plan)

* **Unit Testing:** Validasi logika cek ID di sisi server.
* **Integration Testing:** Memastikan alur QR Code tidak mengganggu fungsionalitas input manual yang sudah ada.
* **Performance Testing:** Memastikan kamera tidak membebani browser atau menyebabkan *memory leak* saat modal terbuka dalam waktu lama.

---

### Saran Pengembangan:

1. **Library Scanner:** Untuk web, Anda bisa menggunakan `html5-qrcode` yang cukup stabil dan mudah diintegrasikan dengan aplikasi berbasis Laravel/Next.js/Node.js.
2. **Audio Handling:** Pastikan browser pengguna memberikan izin akses audio agar suara *success/failed* dapat diputar secara otomatis (browser modern sering memblokir audio otomatis kecuali ada interaksi pengguna).
3. **Data Security:** Pastikan QR Code yang digunakan siswa memiliki enkripsi sederhana atau setidaknya token unik agar tidak mudah dipalsukan oleh siswa lain.

Apakah Anda ingin saya membantu membuatkan *pseudocode* atau contoh logika untuk fungsi validasi QR Code tersebut?
