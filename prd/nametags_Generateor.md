Tentu, ini adalah draf **Product Requirements Document (PRD)** untuk pengembangan fitur *Dynamic Name Tag Template* pada halaman `/admin/nametags`.

---

# Product Requirements Document (PRD): Dynamic Name Tag Template Generator

**Status:** Draft / In Progress
**Fitur:** Template-Based Name Tag Generator
**Lokasi:** `/admin/nametags`

## 1. Latar Belakang & Tujuan

Saat ini, fitur generator *name tag* mungkin masih menggunakan format statis atau terbatas. Untuk meningkatkan fleksibilitas desain tanpa harus mengubah kode sumber (hardcoding) setiap kali ada perubahan orientasi atau ukuran, fitur ini dikembangkan agar admin dapat memilih template yang telah ditentukan sebelumnya.

**Tujuan:**

* Memberikan kebebasan bagi admin untuk memilih desain/template *name tag*.
* Memisahkan logika desain (HTML/CSS) dari logika sistem, sehingga orientasi dan ukuran sudah terdefinisi di dalam template.
* Mempercepat proses produksi *name tag* yang variatif.

## 2. Definisi Fitur

Sistem akan memungkinkan admin untuk memilih dari daftar template yang tersedia. Setiap template merupakan satu kesatuan skrip (HTML + CSS) yang sudah mencakup:

* **Dimensi:** Ukuran lebar dan tinggi *name tag*.
* **Orientasi:** Portrait atau Landscape.
* **Layout:** Posisi elemen (foto, nama, jabatan, QR code).

## 3. Spesifikasi Teknis & Fungsionalitas

### 3.1. Antarmuka (UI)

* **Template Selector:** Dropdown atau *grid view* (dengan *preview* gambar) di halaman `/admin/nametags`.
* **Action:** Setelah template dipilih, sistem secara otomatis memuat *preview* hasil *render* berdasarkan data siswa yang dipilih.
* **Customization:** Fitur untuk memilih atau mengganti template secara instan sebelum melakukan *generate* atau cetak.

### 3.2. Logika Sistem (Backend/Scripting)

* **Template Registry:** Membuat satu direktori atau *database table* khusus untuk menyimpan file template.
* **Template Structure:** Setiap template harus dalam format satu file (misal: `template_a.blade.php` atau `.html`) yang berisi:
* Tag `<style>` untuk CSS (layout, font, warna).
* Tag `<div>` untuk struktur HTML.
* Variabel *placeholder* untuk data dinamis (Nama, ID, Foto, QR Code).


* **Rendering Engine:** Sistem mengambil *input* data siswa dan memasukkannya ke dalam *placeholder* template yang dipilih, lalu merender hasilnya menjadi PDF atau tampilan cetak.

## 4. User Flow

1. Admin mengakses halaman `/admin/nametags`.
2. Admin memilih siswa yang akan dibuatkan *name tag*-nya.
3. Admin memilih **"Pilih Template"** dari daftar yang tersedia.
4. Sistem memuat template (HTML/CSS) yang berisi pengaturan orientasi dan ukuran yang sudah *fixed*.
5. Admin menekan tombol **"Generate"** atau **"Cetak"**.
6. Sistem memproses *render* sesuai template dan menampilkan hasil akhir untuk diunduh/dicetak.

## 5. Rencana Pengembangan (Roadmap)

| Fase | Deskripsi |
| --- | --- |
| **Tahap 1** | Standarisasi format file template (HTML/CSS modular). |
| **Tahap 2** | Pembuatan fungsi *selector* template di UI Admin. |
| **Tahap 3** | Integrasi *rendering engine* untuk membaca template yang dipilih. |
| **Tahap 4** | Pengujian *preview* dan hasil cetak (PDF). |

## 6. Pertimbangan Teknis (Best Practices)

* **Keamanan:** Pastikan input dari template tidak mengandung *malicious script* jika admin memiliki akses untuk mengunggah template sendiri.
* **Responsivitas:** Karena orientasi dan ukuran sudah *fixed* di dalam template, pastikan sistem *print* browser (atau library PDF generator seperti `dompdf` atau `snappy`) dapat merender sesuai dengan ukuran kertas yang diinginkan.
* **Maintenance:** Gunakan sistem *Template Engine* (seperti Blade di Laravel) agar pemisahan antara data (backend) dan tampilan (template) tetap bersih.

---

