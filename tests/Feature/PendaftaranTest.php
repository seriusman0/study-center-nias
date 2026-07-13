<?php

namespace Tests\Feature;

use App\Models\Cabang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PendaftaranTest extends TestCase
{
    use RefreshDatabase;

    protected Cabang $cabang;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->cabang = Cabang::create([
            'nama'             => 'Gunungsitoli',
            'slug'             => 'gunungsitoli',
            'pendaftaran_buka' => true,
            'foto_wajib'       => false,
        ]);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name'            => 'Budi Santoso',
            'gender'          => 'laki-laki',
            'school_name'     => 'SMA Negeri 1',
            'grade_class'     => 10,
            'birth_date'      => '2008-01-01',
            'address'         => 'Jl. Merdeka No. 1',
            'guardian_phone'  => '081234567890',
            'mata_pelajaran'  => ['Matematika'],
        ], $overrides);
    }

    public function test_foto_dibawah_2mb_lolos_validasi(): void
    {
        $foto = UploadedFile::fake()->create('foto.jpg', 900, 'image/jpeg');

        $response = $this->post(route('pendaftaran.store', $this->cabang), array_merge(
            $this->validPayload(),
            ['photo' => $foto]
        ));

        $response->assertRedirect(route('pendaftaran.preview', $this->cabang));
    }

    public function test_foto_tepat_2mb_lolos_validasi(): void
    {
        $foto = UploadedFile::fake()->create('foto.jpg', 2048, 'image/jpeg');

        $response = $this->post(route('pendaftaran.store', $this->cabang), array_merge(
            $this->validPayload(),
            ['photo' => $foto]
        ));

        $response->assertRedirect(route('pendaftaran.preview', $this->cabang));
    }

    public function test_foto_lebih_dari_2mb_ditolak(): void
    {
        $foto = UploadedFile::fake()->create('foto.jpg', 2049, 'image/jpeg');

        $response = $this->post(route('pendaftaran.store', $this->cabang), array_merge(
            $this->validPayload(),
            ['photo' => $foto]
        ));

        $response->assertStatus(302);
        $this->assertNotEquals(route('pendaftaran.preview', $this->cabang), $response->headers->get('Location'));
    }

    public function test_nama_duplikat_tidak_diblokir_di_store(): void
    {
        User::factory()->create(['name' => 'Budi Santoso']);

        $foto = UploadedFile::fake()->create('foto.jpg', 500, 'image/jpeg');

        $response = $this->post(route('pendaftaran.store', $this->cabang), array_merge(
            $this->validPayload(['name' => 'Budi Santoso']),
            ['photo' => $foto]
        ));

        $response->assertRedirect(route('pendaftaran.preview', $this->cabang));
    }

    public function test_konfirmasi_dengan_nama_duplikat_berhasil_simpan(): void
    {
        User::factory()->create(['name' => 'Ani Rahayu']);
        $countBefore = User::count();

        $foto = UploadedFile::fake()->create('foto.jpg', 500, 'image/jpeg');
        $tempPath = 'pendaftaran/temp/foto_test.jpg';
        Storage::disk('public')->put($tempPath, $foto->getContent());

        session([
            'pendaftaran_data' => [
                'name'           => 'Ani Rahayu',
                'gender'         => 'perempuan',
                'student_phone'  => null,
                'school_name'    => 'SMP NEGERI 2',
                'grade_class'    => 8,
                'birth_date'     => '2010-05-15',
                'address'        => 'Jl. Bunga No. 5',
                'guardian_phone' => '+6281234567891',
                'note'           => null,
            ],
            'pendaftaran_foto_temp' => $tempPath,
            'pendaftaran_cabang_id' => $this->cabang->id,
        ]);

        $response = $this->post(route('pendaftaran.konfirmasi', $this->cabang));

        $response->assertRedirect(route('pendaftaran.sukses', $this->cabang));
        $this->assertDatabaseCount('users', $countBefore + 1);
        $this->assertEquals(2, User::where('name', 'Ani Rahayu')->count());
    }

    // --- Kelas restriction tests ---

    public function test_kelas_dalam_range_cabang_diterima(): void
    {
        $this->cabang->update(['kelas_min' => 6, 'kelas_max' => 9]);

        $response = $this->post(
            route('pendaftaran.store', $this->cabang),
            $this->validPayload(['grade_class' => 7])
        );

        $response->assertRedirect(route('pendaftaran.preview', $this->cabang));
    }

    public function test_kelas_di_bawah_range_cabang_ditolak(): void
    {
        $this->cabang->update(['kelas_min' => 6, 'kelas_max' => 9]);

        $response = $this->post(
            route('pendaftaran.store', $this->cabang),
            $this->validPayload(['grade_class' => 5])
        );

        $response->assertSessionHasErrors('grade_class');
    }

    public function test_kelas_di_atas_range_cabang_ditolak(): void
    {
        $this->cabang->update(['kelas_min' => 6, 'kelas_max' => 9]);

        $response = $this->post(
            route('pendaftaran.store', $this->cabang),
            $this->validPayload(['grade_class' => 10])
        );

        $response->assertSessionHasErrors('grade_class');
    }

    public function test_kelas_boundary_min_diterima(): void
    {
        $this->cabang->update(['kelas_min' => 6, 'kelas_max' => 9]);

        $response = $this->post(
            route('pendaftaran.store', $this->cabang),
            $this->validPayload(['grade_class' => 6])
        );

        $response->assertRedirect(route('pendaftaran.preview', $this->cabang));
    }

    public function test_kelas_boundary_max_diterima(): void
    {
        $this->cabang->update(['kelas_min' => 6, 'kelas_max' => 9]);

        $response = $this->post(
            route('pendaftaran.store', $this->cabang),
            $this->validPayload(['grade_class' => 9])
        );

        $response->assertRedirect(route('pendaftaran.preview', $this->cabang));
    }

    public function test_tanpa_kelas_range_semua_kelas_1_12_diterima(): void
    {
        // cabang without kelas restriction
        $response = $this->post(
            route('pendaftaran.store', $this->cabang),
            $this->validPayload(['grade_class' => 12])
        );

        $response->assertRedirect(route('pendaftaran.preview', $this->cabang));
    }

    public function test_pesan_error_menyebut_range_kelas_cabang(): void
    {
        $this->cabang->update(['kelas_min' => 6, 'kelas_max' => 9]);

        $response = $this->post(
            route('pendaftaran.store', $this->cabang),
            $this->validPayload(['grade_class' => 10])
        );

        $response->assertSessionHasErrors(['grade_class']);
        $errors = session('errors');
        $this->assertStringContainsString('9', $errors->first('grade_class'));
    }
}
