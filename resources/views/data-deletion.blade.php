<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penghapusan Data Akun — Study Center Nias</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
            line-height: 1.7;
            max-width: 720px;
            margin: 0 auto;
            padding: 24px 20px 60px;
            color: #1a1a1a;
            background: #fafafa;
        }
        h1 { color: #0F766E; font-size: 1.6rem; margin-bottom: 0.25rem; }
        h2 { color: #134e4a; font-size: 1.15rem; margin-top: 2rem; }
        .subtitle { color: #555; font-size: 0.95rem; margin-top: 0; }
        .card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 24px 28px;
            margin-top: 28px;
        }
        .warning-box {
            background: #fff7ed;
            border-left: 4px solid #f97316;
            padding: 14px 18px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }
        ul { padding-left: 20px; }
        li { margin-bottom: 6px; }
        label { font-weight: 600; display: block; margin-bottom: 6px; }
        input[type="text"] {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid #cbd5e1;
            border-radius: 6px;
            font-size: 1rem;
            font-family: monospace;
            letter-spacing: 0.08em;
            transition: border-color 0.2s;
        }
        input[type="text"]:focus { outline: none; border-color: #0F766E; }
        .btn-hapus {
            margin-top: 18px;
            width: 100%;
            padding: 13px;
            background: #dc2626;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: 0.03em;
            transition: background 0.2s;
        }
        .btn-hapus:hover { background: #b91c1c; }
        .btn-batal {
            display: block;
            text-align: center;
            margin-top: 12px;
            color: #0F766E;
            text-decoration: none;
            font-size: 0.92rem;
        }
        .error-msg {
            background: #fee2e2;
            color: #991b1b;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 14px;
            font-size: 0.9rem;
        }
        .success-msg {
            background: #dcfce7;
            color: #166534;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 14px;
            font-size: 0.9rem;
        }
        .login-notice {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 18px 22px;
            text-align: center;
            margin-top: 20px;
        }
        .login-notice a {
            color: #0F766E;
            font-weight: 700;
            text-decoration: none;
        }
        footer { margin-top: 40px; font-size: 0.82rem; color: #888; }
        .steps {
            counter-reset: step;
            list-style: none;
            padding-left: 0;
        }
        .steps li {
            counter-increment: step;
            padding: 8px 0 8px 44px;
            position: relative;
            border-bottom: 1px solid #f1f5f9;
        }
        .steps li:last-child { border-bottom: none; }
        .steps li::before {
            content: counter(step);
            position: absolute;
            left: 0;
            top: 8px;
            width: 28px;
            height: 28px;
            background: #0F766E;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.82rem;
            font-weight: 700;
        }
    </style>
</head>
<body>

    <h1>🗑️ Penghapusan Data Akun</h1>
    <p class="subtitle">Study Center Nias — Aplikasi Student (<code>com.studycenter.sc_student</code>)</p>

    @if(session('success'))
        <div class="success-msg">✅ {{ session('success') }}</div>
    @endif

    <div class="card">
        <h2>📋 Data apa yang disimpan?</h2>
        <ul>
            <li>Nama lengkap, username, dan alamat email</li>
            <li>Foto profil dan bio (jika diisi)</li>
            <li>Jurnal harian (catatan kegiatan belajar & kehidupan rohani)</li>
            <li>Rekaman presensi (kehadiran)</li>
            <li>Data profil siswa (cabang, kelas)</li>
            <li>Blog/tulisan yang diterbitkan</li>
            <li>Data CV dan tautan sosial (jika diisi)</li>
        </ul>

        <h2>🔴 Apa yang terjadi saat akun dihapus?</h2>
        <ul>
            <li>Semua data identitas pribadi Anda (nama, email, foto, bio) <strong>dianonimisasi</strong> secara permanen</li>
            <li>Semua sesi login dan token API dicabut segera</li>
            <li>Akun tidak bisa diakses atau dipulihkan setelah penghapusan</li>
            <li>Data jurnal dan presensi yang terkait dengan akun Anda tidak lagi terhubung ke identitas Anda</li>
            <li>Blog yang sudah diterbitkan dapat dihapus terpisah oleh admin</li>
        </ul>
    </div>

    @auth
    <div class="card">
        <h2>Hapus akun saya: <strong>{{ Auth::user()->name }}</strong></h2>
        <p style="color:#555; font-size:0.92rem; margin-top:0">
            Login sebagai: <code>{{ Auth::user()->email }}</code>
        </p>

        <div class="warning-box">
            ⚠️ <strong>Perhatian:</strong> Penghapusan akun bersifat <strong>permanen dan tidak dapat dibatalkan</strong>.
            Pastikan Anda sudah mengunduh data penting sebelum melanjutkan.
        </div>

        @if($errors->any())
            <div class="error-msg">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('data-deletion.destroy') }}"
              onsubmit="return confirm('Yakin ingin menghapus akun Anda secara permanen? Tindakan ini tidak bisa dibatalkan.')">
            @csrf
            @method('DELETE')

            <label for="konfirmasi">
                Ketik <strong>HAPUS</strong> untuk mengkonfirmasi:
            </label>
            <input type="text"
                   id="konfirmasi"
                   name="konfirmasi"
                   placeholder="HAPUS"
                   autocomplete="off"
                   required>

            <button type="submit" class="btn-hapus">
                🗑️ Hapus Akun &amp; Semua Data Saya
            </button>
        </form>

        <a href="/" class="btn-batal">
            ← Batal, kembali ke beranda
        </a>
    </div>
    @else
    <div class="card">
        <h2>Cara menghapus akun Anda</h2>
        <ol class="steps">
            <li>Login ke akun Anda melalui halaman ini atau aplikasi Study Center Nias Student</li>
            <li>Kembali ke halaman ini setelah berhasil login</li>
            <li>Ketik <strong>HAPUS</strong> di kolom konfirmasi dan klik tombol hapus</li>
        </ol>
        <div class="login-notice">
            <p>Anda perlu login terlebih dahulu untuk menghapus akun.</p>
            <a href="{{ route('login') }}">→ Masuk ke akun saya</a>
        </div>
    </div>

    <div class="card" style="margin-top: 20px; background: #f8fafc;">
        <h2 style="margin-top:0">Tidak bisa login? Hubungi kami</h2>
        <p style="margin:0; font-size: 0.95rem;">
            Jika Anda tidak dapat login namun ingin mengajukan penghapusan data secara manual,
            silakan hubungi pengelola Study Center Nias melalui:<br><br>
            📧 Email admin cabang Anda, atau<br>
            🌐 Website: <a href="https://studycenter.nanoprojectdevindonesia.com" style="color:#0F766E">studycenter.nanoprojectdevindonesia.com</a><br><br>
            Sertakan nama lengkap dan email yang terdaftar agar kami dapat memproses permintaan Anda.
        </p>
    </div>
    @endauth

    <footer>
        <p>
            Halaman ini tersedia di:
            <code>https://studycenter.nanoprojectdevindonesia.com/hapus-akun</code><br>
            Kebijakan Privasi: <a href="{{ url('/privacy-policy') }}" style="color:#0F766E">studycenter.nanoprojectdevindonesia.com/privacy-policy</a>
        </p>
    </footer>

</body>
</html>
