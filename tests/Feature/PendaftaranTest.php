<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PendaftaranTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name'           => 'Budi Santoso',
            'gender'         => 'laki-laki',
            'school_name'    => 'SMA Negeri 1',
            'grade_class'    => 10,
            'birth_date'     => '2008-01-01',
            'address'        => 'Jl. Merdeka No. 1',
            'guardian_phone' => '081234567890',
        ], $overrides);
    }

    public function test_foto_dibawah_2mb_lolos_validasi(): void
    {
        // ~900KB — harus lolos (dulu max 512KB, sekarang 2048KB)
        $foto = UploadedFile::fake()->create('foto.jpg', 900, 'image/jpeg');

        $response = $this->post(route('pendaftaran.store'), array_merge(
            $this->validPayload(),
            ['photo' => $foto]
        ));

        $response->assertRedirect(route('pendaftaran.preview'));
    }

    public function test_foto_tepat_2mb_lolos_validasi(): void
    {
        $foto = UploadedFile::fake()->create('foto.jpg', 2048, 'image/jpeg');

        $response = $this->post(route('pendaftaran.store'), array_merge(
            $this->validPayload(),
            ['photo' => $foto]
        ));

        $response->assertRedirect(route('pendaftaran.preview'));
    }

    public function test_foto_lebih_dari_2mb_ditolak(): void
    {
        $foto = UploadedFile::fake()->create('foto.jpg', 2049, 'image/jpeg');

        $response = $this->post(route('pendaftaran.store'), array_merge(
            $this->validPayload(),
            ['photo' => $foto]
        ));

        // Validasi gagal → tidak redirect ke preview
        $response->assertStatus(302);
        $this->assertNotEquals(route('pendaftaran.preview'), $response->headers->get('Location'));
    }

    public function test_nama_duplikat_tidak_diblokir_di_store(): void
    {
        User::factory()->create(['name' => 'Budi Santoso']);

        $foto = UploadedFile::fake()->create('foto.jpg', 500, 'image/jpeg');

        $response = $this->post(route('pendaftaran.store'), array_merge(
            $this->validPayload(['name' => 'Budi Santoso']),
            ['photo' => $foto]
        ));

        $response->assertRedirect(route('pendaftaran.preview'));
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
        ]);

        $response = $this->post(route('pendaftaran.konfirmasi'));

        $response->assertRedirect(route('pendaftaran.sukses'));
        $this->assertDatabaseCount('users', $countBefore + 1);
        $this->assertEquals(2, User::where('name', 'Ani Rahayu')->count());
    }
}
