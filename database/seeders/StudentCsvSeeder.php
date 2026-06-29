<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentCsvSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = base_path('Book1.csv');
        if (! is_readable($csvPath)) {
            $this->command->warn("CSV not found at $csvPath. Skipped.");
            return;
        }

        $studentRoleId = DB::table('roles')->where('name', 'student')->value('id');
        if (! $studentRoleId) {
            $this->command->error('Role "student" missing. Run RoleSeeder first.');
            return;
        }

        $fh = fopen($csvPath, 'r');
        if (! $fh) {
            $this->command->error('Cannot open CSV.');
            return;
        }

        // Header
        $header = fgetcsv($fh, 0, ';');
        if (! $header) {
            fclose($fh);
            $this->command->error('CSV header missing.');
            return;
        }

        $idx = $this->columnMap($header);

        $now = now();
        $created = 0;
        $skipped = 0;

        while (($row = fgetcsv($fh, 0, ';')) !== false) {
            if (empty(array_filter($row, fn($v) => $v !== '' && $v !== null))) {
                continue;
            }

            $name = $this->clean($row[$idx['name']] ?? '');
            if ($name === '') {
                $skipped++;
                continue;
            }

            DB::transaction(function () use ($row, $idx, $name, $studentRoleId, $now, &$created) {
                $username = $this->uniqueUsername($name);

                DB::table('users')->insert([
                    'name'              => $name,
                    'username'          => $username,
                    'email'             => null,
                    'password'          => Hash::make('12345'),
                    'is_active'         => true,
                    'profile_public'    => true,
                    'cv_enabled'        => false,
                    'email_verified_at' => null,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ]);

                $userId = DB::getPdo()->lastInsertId();

                DB::table('user_roles')->insertOrIgnore([
                    'user_id'    => $userId,
                    'role_id'    => $studentRoleId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('student_profiles')->insert([
                    'user_id'        => $userId,
                    'student_number' => null,
                    'birth_date'     => $this->parseDate($row[$idx['birth']] ?? null),
                    'birth_place'    => null,
                    'gender'         => $this->parseGender($row[$idx['gender']] ?? null),
                    'address'        => $this->cleanLong($row[$idx['address']] ?? null),
                    'guardian_name'  => $this->clean($row[$idx['parent']] ?? '') ?: null,
                    'guardian_phone' => $this->cleanPhone($row[$idx['parent_phone']] ?? null),
                    'school_name'    => $this->clean($row[$idx['school']] ?? '') ?: null,
                    'grade_class'    => $this->clean($row[$idx['kelas']] ?? '') ?: null,
                    'entry_year'     => null,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]);

                $created++;
            });
        }
        fclose($fh);

        $this->command->info("Seeded $created students from CSV. Skipped: $skipped.");
    }

    private function columnMap(array $header): array
    {
        $norm = array_map(fn($h) => mb_strtolower(trim($h)), $header);
        $find = function (string ...$needles) use ($norm) {
            foreach ($norm as $i => $col) {
                foreach ($needles as $n) {
                    if (str_contains($col, $n)) {
                        return $i;
                    }
                }
            }
            return null;
        };

        return [
            'parent'       => $find('nama orangtua') ?? 1,
            'name'         => $find('nama lengkap') ?? 2,
            'gender'       => $find('jenis kelamin') ?? 3,
            'phone'        => $find('nomor hp siswa') ?? 4,
            'kelas'        => $find('kelas') ?? 5,
            'school'       => $find('nama sekolah') ?? 6,
            'birth'        => $find('tanggal lahir') ?? 7,
            'address'      => $find('alamat') ?? 8,
            'parent_phone' => $find('no hp orangtua', 'no hp orang tua', 'orangtua/wali') ?? 14,
        ];
    }

    private function clean(?string $v): string
    {
        if ($v === null) return '';
        $v = trim($v);
        $v = preg_replace('/\s+/u', ' ', $v);
        return $v;
    }

    private function cleanLong(?string $v): ?string
    {
        if ($v === null) return null;
        $v = trim($v);
        $v = preg_replace('/[\r\n]+/', ', ', $v);
        $v = preg_replace('/\s{2,}/', ' ', $v);
        return $v ?: null;
    }

    private function cleanPhone(?string $v): ?string
    {
        if ($v === null) return null;
        $v = trim($v);
        if ($v === '' || $v === '-' || $v === '0') return null;
        if (preg_match('/wa\.me\/(\d+)/i', $v, $m)) {
            return '+' . $m[1];
        }
        $v = preg_replace('/[^\d+]/', '', $v);
        return $v ?: null;
    }

    private function parseGender(?string $v): ?string
    {
        $v = mb_strtoupper(trim((string) $v));
        if (str_contains($v, 'PEREMPUAN')) return 'P';
        if (str_contains($v, 'LAKI'))       return 'L';
        return null;
    }

    private function parseDate(?string $v): ?string
    {
        $v = trim((string) $v);
        if ($v === '') return null;
        foreach (['d/m/Y', 'd-m-Y', 'Y-m-d'] as $fmt) {
            try {
                $d = Carbon::createFromFormat($fmt, $v);
                if ($d && $d->year >= 1990 && $d->year <= (int) date('Y')) {
                    return $d->toDateString();
                }
            } catch (\Throwable $e) {
                // try next
            }
        }
        return null;
    }

    private function uniqueUsername(string $name): string
    {
        $ascii = Str::ascii($name);
        $base  = preg_replace('/[^a-z0-9]/', '', strtolower($ascii));
        if ($base === '') {
            $base = 'siswa';
        }
        $base = substr($base, 0, 30);
        $u = $base;
        $i = 1;
        while (DB::table('users')->where('username', $u)->exists()) {
            $u = $base . $i++;
        }
        return $u;
    }
}
