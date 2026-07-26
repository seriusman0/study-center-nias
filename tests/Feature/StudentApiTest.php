<?php

namespace Tests\Feature;

use App\Models\Cabang;
use App\Models\JurnalBibleSchedule;
use App\Models\JurnalEntry;
use App\Models\JurnalLifeItem;
use App\Models\Presensi;
use App\Models\Role;
use App\Models\User;
use App\Support\JurnalWeek;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StudentApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;
    protected Cabang $cabang;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->cabang = Cabang::create([
            'nama'             => 'Test',
            'slug'             => 'test',
            'pendaftaran_buka' => true,
        ]);

        $studentRole = Role::where('name', 'student')->first();

        $this->student = User::factory()->create([
            'cabang_id' => $this->cabang->id,
            'is_active' => true,
        ]);
        $this->student->roles()->attach($studentRole);

        $this->token = $this->student->createToken('t')->plainTextToken;
    }

    // -----------------------------------------------------------------------
    // Helper: create today's bible schedule
    // -----------------------------------------------------------------------

    private function createTodaySchedule(): JurnalBibleSchedule
    {
        return JurnalBibleSchedule::create([
            'tanggal'    => today()->toDateString(),
            'pl_porsi'   => 'Kejadian 1-2',
            'pb_porsi'   => 'Matius 1',
            'created_by' => $this->student->id,
        ]);
    }

    // Helper: create a life item and assign it to the student
    private function createLifeItem(array $overrides = []): JurnalLifeItem
    {
        $item = JurnalLifeItem::create(array_merge([
            'kategori'      => 'kerohanian',
            'label'         => 'Berdoa pagi',
            'response_type' => 'check',
            'is_default'    => true,
            'is_active'     => true,
            'student_id'    => null,
        ], $overrides));

        DB::table('jurnal_student_life_items')->insert([
            'student_id'   => $this->student->id,
            'life_item_id' => $item->id,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return $item;
    }

    // -----------------------------------------------------------------------
    // 1. GET /api/jurnal/today
    // -----------------------------------------------------------------------

    public function test_today_returns_bible_schedule_week_meta_life_items_and_verse_ref(): void
    {
        $schedule = $this->createTodaySchedule();
        $lifeItem = $this->createLifeItem();

        $response = $this->withToken($this->token)->getJson('/api/jurnal/today');

        $response->assertOk();

        $response->assertJsonStructure([
            'date',
            'week',
            'bible' => ['pl_porsi', 'pb_porsi', 'pl_checked', 'pb_checked'],
            'verse_ref',
            'life_items' => [
                '*' => ['id', 'kategori', 'label', 'response_type', 'checked'],
            ],
        ]);

        $response->assertJsonPath('date', today()->toDateString());
        $response->assertJsonPath('bible.pl_porsi', 'Kejadian 1-2');
        $response->assertJsonPath('bible.pb_porsi', 'Matius 1');
        $response->assertJsonPath('bible.pl_checked', false);
        $response->assertJsonPath('bible.pb_checked', false);
        $response->assertJsonPath('verse_ref', null);

        // life_items should contain the assigned item
        $data = $response->json('life_items');
        $this->assertCount(1, $data);
        $this->assertEquals($lifeItem->id, $data[0]['id']);
        $this->assertEquals('kerohanian', $data[0]['kategori']);
        $this->assertEquals('Berdoa pagi', $data[0]['label']);
        $this->assertFalse($data[0]['checked']);
    }

    public function test_today_returns_null_bible_porsi_when_no_schedule_exists(): void
    {
        $response = $this->withToken($this->token)->getJson('/api/jurnal/today');

        $response->assertOk();
        $response->assertJsonPath('bible.pl_porsi', null);
        $response->assertJsonPath('bible.pb_porsi', null);
    }

    public function test_today_reflects_existing_entry_checked_state(): void
    {
        $this->createTodaySchedule();

        JurnalEntry::create([
            'student_id' => $this->student->id,
            'cabang_id'  => $this->cabang->id,
            'tanggal'    => today()->toDateString(),
            'pl_checked' => true,
            'pb_checked' => false,
            'verse_ref'  => 'Roma 8:28',
        ]);

        $response = $this->withToken($this->token)->getJson('/api/jurnal/today');

        $response->assertOk();
        $response->assertJsonPath('bible.pl_checked', true);
        $response->assertJsonPath('bible.pb_checked', false);
        $response->assertJsonPath('verse_ref', 'Roma 8:28');
    }

    // -----------------------------------------------------------------------
    // 2. POST /api/jurnal/check – item_type = pl
    // -----------------------------------------------------------------------

    public function test_check_pl_marks_pl_checked_in_entry(): void
    {
        $today = JurnalWeek::today()->toDateString();

        $response = $this->withToken($this->token)->postJson('/api/jurnal/check', [
            'item_type' => 'pl',
            'date'      => $today,
            'checked'   => true,
        ]);

        $response->assertOk();
        $response->assertJsonPath('ok', true);
        $response->assertJsonStructure(['ok', 'state']);

        $this->assertDatabaseHas('jurnal_entries', [
            'student_id' => $this->student->id,
            'pl_checked' => 1,
        ]);
    }

    public function test_check_pl_can_uncheck(): void
    {
        $today = JurnalWeek::today()->toDateString();

        // First check it on
        $this->withToken($this->token)->postJson('/api/jurnal/check', [
            'item_type' => 'pl',
            'date'      => $today,
            'checked'   => true,
        ]);

        // Then uncheck
        $response = $this->withToken($this->token)->postJson('/api/jurnal/check', [
            'item_type' => 'pl',
            'date'      => $today,
            'checked'   => false,
        ]);

        $response->assertOk()->assertJsonPath('ok', true);

        $this->assertDatabaseHas('jurnal_entries', [
            'student_id' => $this->student->id,
            'pl_checked' => 0,
        ]);
    }

    // -----------------------------------------------------------------------
    // 3. POST /api/jurnal/check – item_type = pb
    // -----------------------------------------------------------------------

    public function test_check_pb_marks_pb_checked_in_entry(): void
    {
        $today = JurnalWeek::today()->toDateString();

        $response = $this->withToken($this->token)->postJson('/api/jurnal/check', [
            'item_type' => 'pb',
            'date'      => $today,
            'checked'   => true,
        ]);

        $response->assertOk();
        $response->assertJsonPath('ok', true);

        $this->assertDatabaseHas('jurnal_entries', [
            'student_id' => $this->student->id,
            'pb_checked' => 1,
        ]);
    }

    public function test_check_pb_can_uncheck(): void
    {
        $today = JurnalWeek::today()->toDateString();

        $this->withToken($this->token)->postJson('/api/jurnal/check', [
            'item_type' => 'pb',
            'date'      => $today,
            'checked'   => true,
        ]);

        $response = $this->withToken($this->token)->postJson('/api/jurnal/check', [
            'item_type' => 'pb',
            'date'      => $today,
            'checked'   => false,
        ]);

        $response->assertOk()->assertJsonPath('ok', true);

        $this->assertDatabaseHas('jurnal_entries', [
            'student_id' => $this->student->id,
            'pb_checked' => 0,
        ]);
    }

    // -----------------------------------------------------------------------
    // 4. POST /api/jurnal/check – item_type = verse
    // -----------------------------------------------------------------------

    public function test_check_verse_saves_verse_ref(): void
    {
        $today = JurnalWeek::today()->toDateString();

        $response = $this->withToken($this->token)->postJson('/api/jurnal/check', [
            'item_type' => 'verse',
            'date'      => $today,
            'verse_ref' => 'Yohanes 3:16',
        ]);

        $response->assertOk();
        $response->assertJsonPath('ok', true);

        $this->assertDatabaseHas('jurnal_entries', [
            'student_id' => $this->student->id,
            'verse_ref'  => 'Yohanes 3:16',
        ]);

        // verse_week_key should also be populated
        $entry = JurnalEntry::where('student_id', $this->student->id)
            ->whereDate('tanggal', $today)
            ->first();

        $this->assertNotNull($entry->verse_week_key);
        $this->assertSame(
            JurnalWeek::weekKeyFor(JurnalWeek::today()),
            $entry->verse_week_key
        );
    }

    public function test_check_verse_clears_verse_ref_when_null(): void
    {
        $today = JurnalWeek::today()->toDateString();

        // Save a verse first
        $this->withToken($this->token)->postJson('/api/jurnal/check', [
            'item_type' => 'verse',
            'date'      => $today,
            'verse_ref' => 'Yohanes 3:16',
        ]);

        // Now clear it
        $response = $this->withToken($this->token)->postJson('/api/jurnal/check', [
            'item_type' => 'verse',
            'date'      => $today,
            'verse_ref' => null,
        ]);

        $response->assertOk()->assertJsonPath('ok', true);

        $this->assertDatabaseHas('jurnal_entries', [
            'student_id'     => $this->student->id,
            'verse_ref'      => null,
            'verse_week_key' => null,
        ]);
    }

    public function test_check_verse_returned_in_state_snapshot(): void
    {
        $today = JurnalWeek::today()->toDateString();

        $response = $this->withToken($this->token)->postJson('/api/jurnal/check', [
            'item_type' => 'verse',
            'date'      => $today,
            'verse_ref' => 'Roma 8:28',
        ]);

        $response->assertOk();
        $response->assertJsonPath('state.verse_ref', 'Roma 8:28');
    }

    // -----------------------------------------------------------------------
    // 5. POST /api/jurnal/check – item_type = life
    // -----------------------------------------------------------------------

    public function test_check_life_marks_life_item_checked(): void
    {
        $today    = JurnalWeek::today()->toDateString();
        $lifeItem = $this->createLifeItem();

        $response = $this->withToken($this->token)->postJson('/api/jurnal/check', [
            'item_type' => 'life',
            'item_id'   => $lifeItem->id,
            'date'      => $today,
            'checked'   => true,
        ]);

        $response->assertOk();
        $response->assertJsonPath('ok', true);

        $this->assertDatabaseHas('jurnal_life_checks', [
            'student_id'   => $this->student->id,
            'life_item_id' => $lifeItem->id,
            'checked'      => 1,
        ]);
    }

    public function test_check_life_uncheck_removes_record(): void
    {
        $today    = JurnalWeek::today()->toDateString();
        $lifeItem = $this->createLifeItem();

        // Check on first
        $this->withToken($this->token)->postJson('/api/jurnal/check', [
            'item_type' => 'life',
            'item_id'   => $lifeItem->id,
            'date'      => $today,
            'checked'   => true,
        ]);

        // Then uncheck
        $response = $this->withToken($this->token)->postJson('/api/jurnal/check', [
            'item_type' => 'life',
            'item_id'   => $lifeItem->id,
            'date'      => $today,
            'checked'   => false,
        ]);

        $response->assertOk()->assertJsonPath('ok', true);

        $this->assertDatabaseMissing('jurnal_life_checks', [
            'student_id'   => $this->student->id,
            'life_item_id' => $lifeItem->id,
            'tanggal'      => $today,
        ]);
    }

    public function test_check_life_without_item_id_returns_422(): void
    {
        $today = JurnalWeek::today()->toDateString();

        $response = $this->withToken($this->token)->postJson('/api/jurnal/check', [
            'item_type' => 'life',
            'date'      => $today,
            'checked'   => true,
            // item_id intentionally omitted
        ]);

        $response->assertStatus(422);
    }

    public function test_check_life_item_reflected_in_state_snapshot(): void
    {
        $today    = JurnalWeek::today()->toDateString();
        $lifeItem = $this->createLifeItem();

        $response = $this->withToken($this->token)->postJson('/api/jurnal/check', [
            'item_type' => 'life',
            'item_id'   => $lifeItem->id,
            'date'      => $today,
            'checked'   => true,
        ]);

        $response->assertOk();

        $stateItems = $response->json('state.life_items');
        $this->assertNotEmpty($stateItems);

        $match = collect($stateItems)->firstWhere('id', $lifeItem->id);
        $this->assertNotNull($match);
        $this->assertTrue($match['checked']);
    }

    // -----------------------------------------------------------------------
    // 6. POST /api/jurnal/check – future date returns 422
    // -----------------------------------------------------------------------

    public function test_check_with_future_date_returns_422(): void
    {
        $future = JurnalWeek::today()->addDay()->toDateString();

        $response = $this->withToken($this->token)->postJson('/api/jurnal/check', [
            'item_type' => 'pl',
            'date'      => $future,
            'checked'   => true,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('ok', false);
        $response->assertJsonPath('message', 'Tanggal masa depan tidak diizinkan.');
    }

    public function test_check_with_past_date_is_allowed(): void
    {
        $yesterday = JurnalWeek::today()->subDay()->toDateString();

        $response = $this->withToken($this->token)->postJson('/api/jurnal/check', [
            'item_type' => 'pl',
            'date'      => $yesterday,
            'checked'   => true,
        ]);

        $response->assertOk()->assertJsonPath('ok', true);

        $this->assertDatabaseHas('jurnal_entries', [
            'student_id' => $this->student->id,
            'pl_checked' => 1,
        ]);
    }

    // -----------------------------------------------------------------------
    // 7. GET /api/jurnal/history?from=X&to=Y
    // -----------------------------------------------------------------------

    public function test_history_returns_daily_summaries_for_date_range(): void
    {
        $today     = JurnalWeek::today();
        $yesterday = $today->copy()->subDay();
        $from      = $yesterday->toDateString();
        $to        = $today->toDateString();

        // Create entries for both days
        JurnalEntry::create([
            'student_id' => $this->student->id,
            'cabang_id'  => $this->cabang->id,
            'tanggal'    => $yesterday->toDateString(),
            'pl_checked' => true,
            'pb_checked' => true,
        ]);

        JurnalEntry::create([
            'student_id' => $this->student->id,
            'cabang_id'  => $this->cabang->id,
            'tanggal'    => $today->toDateString(),
            'pl_checked' => false,
            'pb_checked' => true,
        ]);

        $response = $this->withToken($this->token)->getJson(
            "/api/jurnal/history?from={$from}&to={$to}"
        );

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => ['date', 'pl_checked', 'pb_checked', 'verse_checked', 'life_checked_ids'],
            ],
        ]);

        $data = collect($response->json('data'));
        $this->assertCount(2, $data);

        $yesterdayRow = $data->firstWhere('date', $yesterday->toDateString());
        $this->assertNotNull($yesterdayRow);
        $this->assertTrue($yesterdayRow['pl_checked']);
        $this->assertTrue($yesterdayRow['pb_checked']);
        $this->assertIsArray($yesterdayRow['life_checked_ids']);

        $todayRow = $data->firstWhere('date', $today->toDateString());
        $this->assertNotNull($todayRow);
        $this->assertFalse($todayRow['pl_checked']);
        $this->assertTrue($todayRow['pb_checked']);
    }

    public function test_history_returns_false_for_days_with_no_entry(): void
    {
        $today = JurnalWeek::today()->toDateString();
        $from  = $today;
        $to    = $today;

        $response = $this->withToken($this->token)->getJson(
            "/api/jurnal/history?from={$from}&to={$to}"
        );

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertFalse($data[0]['pl_checked']);
        $this->assertFalse($data[0]['pb_checked']);
        $this->assertFalse($data[0]['verse_checked']);
        $this->assertSame([], $data[0]['life_checked_ids']);
    }

    public function test_history_includes_life_checked_ids(): void
    {
        $today    = JurnalWeek::today()->toDateString();
        $lifeItem = $this->createLifeItem();

        $this->withToken($this->token)->postJson('/api/jurnal/check', [
            'item_type' => 'life',
            'item_id'   => $lifeItem->id,
            'date'      => $today,
            'checked'   => true,
        ]);

        $response = $this->withToken($this->token)->getJson(
            "/api/jurnal/history?from={$today}&to={$today}"
        );

        $response->assertOk();
        $data = $response->json('data');
        $this->assertContains($lifeItem->id, $data[0]['life_checked_ids']);
    }

    public function test_history_requires_from_and_to(): void
    {
        $response = $this->withToken($this->token)->getJson('/api/jurnal/history');

        $response->assertStatus(422);
    }

    public function test_history_rejects_to_before_from(): void
    {
        $response = $this->withToken($this->token)->getJson(
            '/api/jurnal/history?from=2026-01-10&to=2026-01-05'
        );

        $response->assertStatus(422);
    }

    // -----------------------------------------------------------------------
    // 8. GET /api/laporan/my
    // -----------------------------------------------------------------------

    public function test_laporan_my_returns_30_day_summary_with_pct_and_streak(): void
    {
        $response = $this->withToken($this->token)->getJson('/api/laporan/my');

        $response->assertOk();
        $response->assertJsonStructure([
            'from',
            'to',
            'pct',
            'checked',
            'total',
            'streak',
        ]);

        $data = $response->json();
        $this->assertTrue(is_float($data['pct']) || is_int($data['pct']));
        $this->assertIsInt($data['checked']);
        $this->assertIsInt($data['total']);
        $this->assertIsInt($data['streak']);

        // Date range should be 30 days (today minus 29 days)
        $today  = JurnalWeek::today()->toDateString();
        $from   = JurnalWeek::today()->subDays(29)->toDateString();
        $this->assertSame($from, $data['from']);
        $this->assertSame($today, $data['to']);
    }

    public function test_laporan_my_streak_counts_consecutive_days(): void
    {
        // Seed two consecutive days with at least pl_checked
        $today     = JurnalWeek::today();
        $yesterday = $today->copy()->subDay();

        JurnalEntry::create([
            'student_id' => $this->student->id,
            'cabang_id'  => $this->cabang->id,
            'tanggal'    => $today->toDateString(),
            'pl_checked' => true,
        ]);

        JurnalEntry::create([
            'student_id' => $this->student->id,
            'cabang_id'  => $this->cabang->id,
            'tanggal'    => $yesterday->toDateString(),
            'pl_checked' => true,
        ]);

        $response = $this->withToken($this->token)->getJson('/api/laporan/my');

        $response->assertOk();
        $this->assertGreaterThanOrEqual(2, $response->json('streak'));
    }

    public function test_laporan_my_pct_increases_with_checks(): void
    {
        // With no entries pct should be 0
        $emptyResponse = $this->withToken($this->token)->getJson('/api/laporan/my');
        $emptyResponse->assertOk();
        $this->assertEquals(0.0, $emptyResponse->json('pct'));

        // Create an entry with pl and pb checked
        JurnalEntry::create([
            'student_id' => $this->student->id,
            'cabang_id'  => $this->cabang->id,
            'tanggal'    => today()->toDateString(),
            'pl_checked' => true,
            'pb_checked' => true,
        ]);

        $filledResponse = $this->withToken($this->token)->getJson('/api/laporan/my');
        $filledResponse->assertOk();
        $this->assertGreaterThan(0, $filledResponse->json('pct'));
        $this->assertGreaterThan(0, $filledResponse->json('checked'));
    }

    // -----------------------------------------------------------------------
    // 9. GET /api/laporan/my/matrix
    // -----------------------------------------------------------------------

    public function test_laporan_my_matrix_returns_headers_and_rows(): void
    {
        $response = $this->withToken($this->token)->getJson('/api/laporan/my/matrix');

        $response->assertOk();
        $response->assertJsonStructure([
            'from',
            'to',
            'headers',
            'rows',
            'pct',
            'checked',
            'total',
        ]);

        $headers = $response->json('headers');
        $this->assertIsArray($headers);
        $this->assertContains('Tanggal', $headers);
        $this->assertContains('PL', $headers);
        $this->assertContains('PB', $headers);
        $this->assertContains('Hafal Ayat', $headers);

        $rows = $response->json('rows');
        $this->assertIsArray($rows);
        // Each row should have the same column count as headers
        foreach ($rows as $row) {
            $this->assertCount(count($headers), $row);
        }
    }

    public function test_laporan_my_matrix_row_contains_date_and_y_dash_values(): void
    {
        $today = JurnalWeek::today()->toDateString();

        JurnalEntry::create([
            'student_id' => $this->student->id,
            'cabang_id'  => $this->cabang->id,
            'tanggal'    => $today,
            'pl_checked' => true,
            'pb_checked' => false,
        ]);

        $response = $this->withToken($this->token)->getJson(
            "/api/laporan/my/matrix?from={$today}&to={$today}"
        );

        $response->assertOk();

        $rows = $response->json('rows');
        $this->assertCount(1, $rows);

        $row = $rows[0];
        $this->assertSame($today, $row[0]); // date column
        $this->assertSame('Y', $row[1]);    // PL = checked
        $this->assertSame('-', $row[2]);    // PB = not checked
        $this->assertSame('-', $row[3]);    // Hafal Ayat = not checked
    }

    public function test_laporan_my_matrix_includes_life_item_column(): void
    {
        $lifeItem = $this->createLifeItem(['label' => 'Saat Teduh', 'kategori' => 'kerohanian']);
        $today    = JurnalWeek::today()->toDateString();

        $this->withToken($this->token)->postJson('/api/jurnal/check', [
            'item_type' => 'life',
            'item_id'   => $lifeItem->id,
            'date'      => $today,
            'checked'   => true,
        ]);

        $response = $this->withToken($this->token)->getJson(
            "/api/laporan/my/matrix?from={$today}&to={$today}"
        );

        $response->assertOk();

        $headers = $response->json('headers');
        $this->assertContains('Kerohanian: Saat Teduh', $headers);

        $rows = $response->json('rows');
        $lifeColIndex = array_search('Kerohanian: Saat Teduh', $headers);
        $this->assertSame('Y', $rows[0][$lifeColIndex]);
    }

    public function test_laporan_my_matrix_to_date_capped_at_today(): void
    {
        $future = JurnalWeek::today()->addDays(5)->toDateString();
        $today  = JurnalWeek::today()->toDateString();

        $response = $this->withToken($this->token)->getJson(
            "/api/laporan/my/matrix?from={$today}&to={$future}"
        );

        $response->assertOk();
        $this->assertSame($today, $response->json('to'));
    }

    // -----------------------------------------------------------------------
    // 10. GET /api/galeri
    // -----------------------------------------------------------------------

    public function test_galeri_returns_photos_from_student_cabang(): void
    {
        Presensi::create([
            'cabang_id'   => $this->cabang->id,
            'tanggal'     => today()->toDateString(),
            'foto'        => 'presensi/foto1.jpg',
            'mentor_id'   => $this->student->id,
            'created_by'  => $this->student->id,
            'kelas'       => 'Kelas A',
            'materi'      => 'Materi Test',
            'jam_mulai'   => '08:00:00',
            'jam_selesai' => '10:00:00',
        ]);

        $response = $this->withToken($this->token)->getJson('/api/galeri');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'foto_url', 'tanggal'],
            ],
        ]);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertSame(today()->toDateString(), $data[0]['tanggal']);
        $this->assertStringContainsString('presensi/foto1.jpg', $data[0]['foto_url']);
    }

    public function test_galeri_excludes_presensi_without_foto(): void
    {
        // Presensi with no photo
        Presensi::create([
            'cabang_id'   => $this->cabang->id,
            'tanggal'     => today()->toDateString(),
            'foto'        => null,
            'mentor_id'   => $this->student->id,
            'created_by'  => $this->student->id,
            'kelas'       => 'Kelas A',
            'materi'      => 'Materi Test',
            'jam_mulai'   => '08:00:00',
            'jam_selesai' => '10:00:00',
        ]);

        // Presensi with photo
        Presensi::create([
            'cabang_id'   => $this->cabang->id,
            'tanggal'     => today()->toDateString(),
            'foto'        => 'presensi/foto2.jpg',
            'mentor_id'   => $this->student->id,
            'created_by'  => $this->student->id,
            'kelas'       => 'Kelas A',
            'materi'      => 'Materi Test',
            'jam_mulai'   => '08:00:00',
            'jam_selesai' => '10:00:00',
        ]);

        $response = $this->withToken($this->token)->getJson('/api/galeri');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertNotNull($data[0]['foto_url']);
    }

    public function test_galeri_excludes_photos_from_other_cabang(): void
    {
        $otherCabang = Cabang::create([
            'nama'             => 'Other',
            'slug'             => 'other',
            'pendaftaran_buka' => true,
        ]);

        Presensi::create([
            'cabang_id'   => $otherCabang->id,
            'tanggal'     => today()->toDateString(),
            'foto'        => 'presensi/other.jpg',
            'mentor_id'   => $this->student->id,
            'created_by'  => $this->student->id,
            'kelas'       => 'Kelas B',
            'materi'      => 'Materi Test',
            'jam_mulai'   => '08:00:00',
            'jam_selesai' => '10:00:00',
        ]);

        $response = $this->withToken($this->token)->getJson('/api/galeri');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(0, $data);
    }

    public function test_galeri_is_ordered_newest_first(): void
    {
        $olderDate = today()->subDays(5)->toDateString();
        $newerDate = today()->toDateString();

        Presensi::create([
            'cabang_id'   => $this->cabang->id,
            'tanggal'     => $olderDate,
            'foto'        => 'presensi/older.jpg',
            'mentor_id'   => $this->student->id,
            'created_by'  => $this->student->id,
            'kelas'       => 'Kelas A',
            'materi'      => 'Materi Test',
            'jam_mulai'   => '08:00:00',
            'jam_selesai' => '10:00:00',
        ]);

        Presensi::create([
            'cabang_id'   => $this->cabang->id,
            'tanggal'     => $newerDate,
            'foto'        => 'presensi/newer.jpg',
            'mentor_id'   => $this->student->id,
            'created_by'  => $this->student->id,
            'kelas'       => 'Kelas A',
            'materi'      => 'Materi Test',
            'jam_mulai'   => '08:00:00',
            'jam_selesai' => '10:00:00',
        ]);

        $response = $this->withToken($this->token)->getJson('/api/galeri');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(2, $data);
        $this->assertSame($newerDate, $data[0]['tanggal']);
        $this->assertSame($olderDate, $data[1]['tanggal']);
    }

    // -----------------------------------------------------------------------
    // 11. Non-student cannot access /api/jurnal/today (403)
    // -----------------------------------------------------------------------

    public function test_non_student_cannot_access_jurnal_today(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $mentorRole = Role::where('name', 'mentor')->first();

        $mentor = User::factory()->create([
            'cabang_id' => $this->cabang->id,
            'is_active' => true,
        ]);
        $mentor->roles()->attach($mentorRole);

        $mentorToken = $mentor->createToken('t')->plainTextToken;

        $response = $this->withToken($mentorToken)->getJson('/api/jurnal/today');

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_jurnal_today(): void
    {
        $response = $this->getJson('/api/jurnal/today');

        $response->assertStatus(401);
    }

    public function test_non_student_cannot_access_jurnal_check(): void
    {
        $adminRole = Role::where('name', 'admin')->first();

        $admin = User::factory()->create([
            'cabang_id' => $this->cabang->id,
            'is_active' => true,
        ]);
        $admin->roles()->attach($adminRole);

        $adminToken = $admin->createToken('t')->plainTextToken;

        $response = $this->withToken($adminToken)->postJson('/api/jurnal/check', [
            'item_type' => 'pl',
            'checked'   => true,
        ]);

        $response->assertStatus(403);
    }

    public function test_non_student_cannot_access_galeri(): void
    {
        $guestRole = Role::where('name', 'guest')->first();

        $guest = User::factory()->create([
            'cabang_id' => $this->cabang->id,
            'is_active' => true,
        ]);
        $guest->roles()->attach($guestRole);

        $guestToken = $guest->createToken('t')->plainTextToken;

        $response = $this->withToken($guestToken)->getJson('/api/galeri');

        $response->assertStatus(403);
    }

    public function test_non_student_cannot_access_laporan_my(): void
    {
        $mentorRole = Role::where('name', 'mentor')->first();

        $mentor = User::factory()->create([
            'cabang_id' => $this->cabang->id,
            'is_active' => true,
        ]);
        $mentor->roles()->attach($mentorRole);

        $mentorToken = $mentor->createToken('t')->plainTextToken;

        $response = $this->withToken($mentorToken)->getJson('/api/laporan/my');

        $response->assertStatus(403);
    }
}
