Implementasi yang paling rapi adalah pisahkan data login, role, dan data profil khusus.

Jangan simpan semua data admin, student, mentor dalam satu tabel users yang terlalu banyak kolom. Nanti tabel menjadi kotor, banyak kolom kosong, dan sulit dikembangkan.

1. Struktur dasar yang disarankan

Gunakan tabel utama:

users

Untuk data umum semua pengguna.

Contoh isi:

Kolom	Keterangan
id	ID user
name	nama pengguna
email	email login
password	password
phone	nomor HP jika umum
status	active, inactive, suspended
created_at	tanggal dibuat
updated_at	tanggal diperbarui

Tabel ini hanya menyimpan data yang dimiliki semua role.

2. Pisahkan role ke tabel khusus
roles
Kolom	Contoh
id	1
name	admin, student, mentor
user_roles
Kolom	Keterangan
user_id	relasi ke users
role_id	relasi ke roles

Kenapa pakai user_roles?

Karena satu user bisa saja punya lebih dari satu role.

Contoh:

Budi adalah mentor
Budi juga bisa menjadi admin
Siti adalah student
Rina adalah student dan mentor

Ini lebih fleksibel daripada menaruh kolom role langsung di tabel users.

3. Buat tabel profil sesuai role

Karena data setiap role berbeda, buat tabel profil terpisah.

student_profiles
Kolom	Keterangan
id	ID profil
user_id	relasi ke users
student_number	nomor siswa
birth_date	tanggal lahir
gender	jenis kelamin
address	alamat
guardian_name	nama wali
guardian_phone	nomor wali
school_name	asal sekolah
mentor_profiles
Kolom	Keterangan
id	ID profil
user_id	relasi ke users
expertise	bidang keahlian
bio	deskripsi mentor
education	pendidikan
experience_years	lama pengalaman
hourly_rate	tarif
is_available	status ketersediaan
admin_profiles
Kolom	Keterangan
id	ID profil
user_id	relasi ke users
employee_number	nomor pegawai
department	bagian
position	jabatan

Dengan pola ini, data tidak tercampur.

4. Contoh relasi database

Secara sederhana:

users
  ├── user_roles
  │       └── roles
  │
  ├── student_profiles
  ├── mentor_profiles
  └── admin_profiles

Artinya:

Semua akun login masuk ke users
Hak akses masuk ke roles
Data khusus siswa masuk ke student_profiles
Data khusus mentor masuk ke mentor_profiles
Data khusus admin masuk ke admin_profiles
5. Contoh skema sederhana
CREATE TABLE users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
CREATE TABLE roles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) UNIQUE NOT NULL
);
CREATE TABLE user_roles (
    user_id BIGINT NOT NULL,
    role_id BIGINT NOT NULL,
    PRIMARY KEY (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (role_id) REFERENCES roles(id)
);
CREATE TABLE student_profiles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNIQUE NOT NULL,
    student_number VARCHAR(50),
    birth_date DATE,
    gender VARCHAR(20),
    address TEXT,
    guardian_name VARCHAR(100),
    guardian_phone VARCHAR(20),
    school_name VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES users(id)
);
CREATE TABLE mentor_profiles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNIQUE NOT NULL,
    expertise VARCHAR(150),
    bio TEXT,
    education VARCHAR(150),
    experience_years INT,
    hourly_rate DECIMAL(12,2),
    is_available BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
CREATE TABLE admin_profiles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNIQUE NOT NULL,
    employee_number VARCHAR(50),
    department VARCHAR(100),
    position VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES users(id)
);
6. Jangan buat seperti ini

Kurang disarankan:

users
- id
- name
- email
- password
- role
- student_number
- guardian_name
- school_name
- mentor_expertise
- mentor_bio
- admin_department
- admin_position

Masalahnya:

Banyak kolom kosong
Sulit validasi
Sulit dikembangkan
Struktur tidak bersih
Role baru membuat tabel makin berantakan
7. Cara validasi saat register

Misalnya user daftar sebagai student.

Maka prosesnya:

Simpan data umum ke tabel users
Ambil user_id
Masukkan role student ke user_roles
Simpan data khusus ke student_profiles

Contoh alur:

Register Student
→ insert users
→ insert user_roles sebagai student
→ insert student_profiles

Kalau mentor:

Register Mentor
→ insert users
→ insert user_roles sebagai mentor
→ insert mentor_profiles
8. Untuk hak akses, gunakan permission

Role hanya menjawab:

User ini siapa?

Permission menjawab:

User ini boleh melakukan apa?

Contoh tabel tambahan:

permissions
name
manage_users
manage_courses
view_courses
create_schedule
approve_payment
role_permissions
role	permission
admin	manage_users
admin	manage_courses
mentor	create_schedule
student	view_courses

Jadi akses tidak hanya bergantung pada nama role.

9. Best practice singkat

Gunakan pola ini:

users = data login umum
roles = jenis pengguna
user_roles = relasi user dan role
permissions = daftar hak akses
role_permissions = hak akses tiap role
student_profiles = data khusus siswa
mentor_profiles = data khusus mentor
admin_profiles = data khusus admin

Ini struktur yang paling aman, rapi, dan mudah dikembangkan untuk sistem informasi manajemen.

Kesimpulan

Simpan semua pengguna di tabel users. Jangan pisahkan login admin, student, dan mentor ke tabel berbeda. Yang perlu dipisahkan adalah profil khusus berdasarkan role.

Jadi konsep terbaiknya:

Satu tabel user untuk login.
Banyak role untuk hak akses.
Tabel profil terpisah untuk data khusus.

Struktur ini cocok untuk aplikasi sekolah, kursus, bimbel, LMS, atau sistem informasi manajemen.