<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PendaftaranController extends Controller
{
    public function showForm()
    {
        $prevData = session('pendaftaran_data');
        $prevFoto = session('pendaftaran_foto_temp');
        return view('pendaftaran.form', compact('prevData', 'prevFoto'));
    }

    public function sukses()
    {
        $username = session('pendaftaran_username');
        return view('pendaftaran.sukses', compact('username'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:100',
            'gender'         => 'required|in:laki-laki,perempuan',
            'student_phone'  => ['nullable', 'string', 'max:13', 'regex:/^[0-9]{7,13}$/'],
            'school_name'    => 'required|string|max:150',
            'grade_class'    => 'required|integer|min:1|max:12',
            'birth_date'     => 'required|date|before:today',
            'address'        => 'required|string|max:500',
            'guardian_phone' => ['required', 'string', 'max:13', 'regex:/^[0-9]{7,13}$/'],
            'note'           => 'nullable|string|max:1000',
            'photo'          => $request->hasFile('photo') ? 'image|mimes:jpg,jpeg,png,webp|max:2048' : 'nullable',
        ], [
            'name.required'           => 'Nama lengkap wajib diisi.',
            'name.max'                => 'Nama lengkap maksimal 100 karakter.',
            'gender.required'         => 'Jenis kelamin wajib dipilih.',
            'gender.in'               => 'Pilih jenis kelamin yang valid.',
            'student_phone.regex'     => 'Nomor HP siswa hanya boleh berisi angka (7-13 digit, tanpa 0 di depan).',
            'student_phone.max'       => 'Nomor HP siswa maksimal 13 digit.',
            'school_name.required'    => 'Nama sekolah wajib diisi.',
            'grade_class.required'    => 'Kelas wajib diisi.',
            'grade_class.integer'     => 'Kelas harus berupa angka.',
            'grade_class.min'         => 'Kelas minimal 1.',
            'grade_class.max'         => 'Kelas maksimal 12.',
            'birth_date.required'     => 'Tanggal lahir wajib diisi.',
            'birth_date.before'       => 'Tanggal lahir harus sebelum hari ini.',
            'address.required'        => 'Alamat rumah wajib diisi.',
            'guardian_phone.required' => 'Nomor HP orangtua/wali wajib diisi.',
            'guardian_phone.regex'    => 'Nomor HP orangtua hanya boleh berisi angka (7-13 digit, tanpa 0 di depan).',
            'guardian_phone.max'      => 'Nomor HP orangtua maksimal 13 digit.',
            'photo.required'          => 'Foto wajib diunggah.',
            'photo.image'             => 'File harus berupa gambar.',
            'photo.mimes'             => 'Format foto harus JPG, JPEG, PNG, atau WEBP.',
            'photo.max'               => 'Ukuran foto maksimal 2 MB.',
        ]);

        $name          = ucwords(strtolower(trim($request->input('name'))));
        $schoolName    = strtoupper(trim($request->input('school_name')));
        $gradeClass    = (int) $request->input('grade_class');
        $address       = ucwords(strtolower(trim($request->input('address'))));
        $gender        = strtolower(trim($request->input('gender')));
        $guardianPhone = '+62' . ltrim(preg_replace('/\D/', '', $request->input('guardian_phone')), '0');
        $studentPhone  = $request->filled('student_phone')
            ? '+62' . ltrim(preg_replace('/\D/', '', $request->input('student_phone')), '0')
            : null;

        if ($request->hasFile('photo')) {
            $ext      = $request->file('photo')->getClientOriginalExtension();
            $tempPath = 'pendaftaran/temp/' . uniqid('foto_', true) . '.' . $ext;
            Storage::disk('public')->put($tempPath, file_get_contents($request->file('photo')->getRealPath()));
        } else {
            $tempPath = session('pendaftaran_foto_temp');
            if (! $tempPath || ! Storage::disk('public')->exists($tempPath)) {
                return back()->withInput()->withErrors(['photo' => 'Foto wajib diunggah.']);
            }
        }

        session([
            'pendaftaran_data' => [
                'name'           => $name,
                'gender'         => $gender,
                'student_phone'  => $studentPhone,
                'school_name'    => $schoolName,
                'grade_class'    => $gradeClass,
                'birth_date'     => $request->input('birth_date'),
                'address'        => $address,
                'guardian_phone' => $guardianPhone,
                'note'           => $request->filled('note') ? trim($request->input('note')) : null,
            ],
            'pendaftaran_foto_temp' => $tempPath,
        ]);

        return redirect()->route('pendaftaran.preview');
    }

    public function preview()
    {
        $data     = session('pendaftaran_data');
        $fotoTemp = session('pendaftaran_foto_temp');

        if (! $data || ! $fotoTemp) {
            return redirect()->route('pendaftaran.form')
                ->withErrors(['name' => 'Sesi habis atau tidak valid. Silakan isi ulang form.']);
        }

        return view('pendaftaran.preview', compact('data', 'fotoTemp'));
    }

    public function konfirmasi()
    {
        $data     = session('pendaftaran_data');
        $fotoTemp = session('pendaftaran_foto_temp');

        if (! $data || ! $fotoTemp) {
            return redirect()->route('pendaftaran.form')
                ->withErrors(['name' => 'Sesi habis. Silakan isi ulang form.']);
        }

        $username  = null;
        $finalPath = null;

        try {
            DB::transaction(function () use ($data, $fotoTemp, &$username, &$finalPath) {
                $baseUsername = Str::slug($data['name']);
                $username     = $baseUsername;
                $counter      = 2;
                while (User::where('username', $username)->exists()) {
                    $username = $baseUsername . '-' . $counter++;
                }

                $ext       = pathinfo($fotoTemp, PATHINFO_EXTENSION);
                $finalPath = 'pendaftaran/foto/' . $username . '.' . $ext;

                $user = User::create([
                    'name'      => $data['name'],
                    'username'  => $username,
                    'password'  => Hash::make('12345'),
                    'is_active' => false,
                ]);

                $studentRole = Role::where('name', 'student')->first();
                if ($studentRole) {
                    $user->roles()->attach($studentRole->id);
                }

                StudentProfile::create([
                    'user_id'        => $user->id,
                    'gender'         => $data['gender'],
                    'student_phone'  => $data['student_phone'],
                    'school_name'    => $data['school_name'],
                    'grade_class'    => $data['grade_class'],
                    'birth_date'     => $data['birth_date'],
                    'address'        => $data['address'],
                    'guardian_phone' => $data['guardian_phone'],
                    'note'           => $data['note'],
                    'photo'          => $finalPath,
                    'is_pending'     => true,
                    'status'         => 'pending',
                    'entry_year'     => now()->year,
                ]);
            });
        } catch (UniqueConstraintViolationException $e) {
            session()->forget(['pendaftaran_data', 'pendaftaran_foto_temp']);
            return redirect()->route('pendaftaran.form')
                ->withErrors(['name' => 'Pendaftaran atas nama "' . $data['name'] . '" baru saja diproses. Jika ini adalah Anda, silakan cek status pendaftaran. Jika bukan, hubungi pengurus.']);
        }

        Storage::disk('public')->move($fotoTemp, $finalPath);

        session()->forget(['pendaftaran_data', 'pendaftaran_foto_temp']);
        session(['pendaftaran_username' => $username]);

        return redirect()->route('pendaftaran.sukses');
    }

    public function kartu()
    {
        $username = session('pendaftaran_username');
        if (! $username) {
            abort(403, 'Akses tidak valid. Kartu hanya tersedia segera setelah pendaftaran.');
        }

        $user = User::with('studentProfile')->where('username', $username)->firstOrFail();

        $pdf = Pdf::loadView('pendaftaran.kartu-pdf', compact('user'))
            ->setPaper([0, 0, 595, 420])
            ->setOptions([
                'isRemoteEnabled'         => false,
                'isHtml5ParserEnabled'    => true,
                'dpi'                     => 120,
                'isFontSubsettingEnabled' => true,
            ]);

        return $pdf->download('kartu-pendaftaran-' . $user->username . '.pdf');
    }

    public function cekStatus(string $username)
    {
        $user = User::with('studentProfile')
            ->where('username', $username)
            ->whereHas('studentProfile')
            ->firstOrFail();

        return view('pendaftaran.cek-status', compact('user'));
    }
}
