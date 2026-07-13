<?php

namespace Tests\Feature;

use App\Models\Cabang;
use App\Models\CollegeBibleItem;
use App\Models\CollegeConfig;
use App\Models\JurnalEntry;
use App\Models\JurnalLifeItem;
use App\Models\Role;
use App\Models\User;
use App\Support\JurnalWeek;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JurnalStudentTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;
    protected Cabang $cabang;
    protected CollegeBibleItem $bibleItem;
    protected CollegeConfig $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->cabang = Cabang::create([
            'nama'             => 'Gunungsitoli',
            'slug'             => 'gunungsitoli',
            'pendaftaran_buka' => true,
        ]);

        $studentRole = Role::where('name', 'student')->first();

        $this->student = User::factory()->create([
            'cabang_id' => $this->cabang->id,
            'is_active' => true,
        ]);
        $this->student->roles()->attach($studentRole);

        // CollegeConfig anchor at day 1 for today
        $this->config = CollegeConfig::create([
            'anchor_day_no'   => 1,
            'anchor_date'     => JurnalWeek::today()->toDateString(),
            'form_open_time'  => '00:00:00',
            'form_close_time' => '23:59:00',
        ]);

        $this->bibleItem = CollegeBibleItem::create([
            'day_no'  => 1,
            'pl_text' => 'Kejadian 1-2',
            'pb_text' => 'Matius 1',
        ]);
    }

    public function test_student_can_view_jurnal(): void
    {
        $response = $this->actingAs($this->student)->get(route('jurnal.index'));
        $response->assertStatus(200);
        $response->assertViewHas('bibleItem');
        $response->assertViewHas('dayNo');
    }

    public function test_jurnal_bible_uses_college_config(): void
    {
        $response = $this->actingAs($this->student)->get(route('jurnal.index'));
        $response->assertStatus(200);

        $bibleItem = $response->viewData('bibleItem');
        $dayNo     = $response->viewData('dayNo');

        $this->assertEquals(1, $dayNo);
        $this->assertNotNull($bibleItem);
        $this->assertEquals(1, $bibleItem->day_no);
    }

    public function test_student_toggle_pl_checkbox(): void
    {
        $response = $this->actingAs($this->student)->postJson(route('jurnal.toggle'), [
            'type'    => 'pl',
            'date'    => JurnalWeek::today()->toDateString(),
            'checked' => true,
        ]);

        $response->assertOk()->assertJson(['ok' => true]);

        $this->assertDatabaseHas('jurnal_entries', [
            'student_id' => $this->student->id,
            'pl_checked' => true,
        ]);
    }

    public function test_student_toggle_pb_checkbox(): void
    {
        $response = $this->actingAs($this->student)->postJson(route('jurnal.toggle'), [
            'type'    => 'pb',
            'date'    => JurnalWeek::today()->toDateString(),
            'checked' => true,
        ]);

        $response->assertOk()->assertJson(['ok' => true]);

        $this->assertDatabaseHas('jurnal_entries', [
            'student_id' => $this->student->id,
            'pb_checked' => true,
        ]);
    }

    public function test_student_saves_verse_ref(): void
    {
        $response = $this->actingAs($this->student)->postJson(route('jurnal.toggle'), [
            'type'      => 'verse',
            'date'      => JurnalWeek::today()->toDateString(),
            'verse_ref' => 'Kejadian 1:3',
        ]);

        $response->assertOk()->assertJson(['ok' => true]);

        $this->assertDatabaseHas('jurnal_entries', [
            'student_id' => $this->student->id,
            'verse_ref'  => 'Kejadian 1:3',
        ]);
    }

    public function test_student_clears_verse_ref(): void
    {
        $today = JurnalWeek::today()->toDateString();

        // Save first
        $this->actingAs($this->student)->postJson(route('jurnal.toggle'), [
            'type'      => 'verse',
            'date'      => $today,
            'verse_ref' => 'Kejadian 1:3',
        ]);

        // Then clear
        $response = $this->actingAs($this->student)->postJson(route('jurnal.toggle'), [
            'type'      => 'verse',
            'date'      => $today,
            'verse_ref' => null,
        ]);

        $response->assertOk()->assertJson(['ok' => true]);

        $this->assertDatabaseHas('jurnal_entries', [
            'student_id'     => $this->student->id,
            'verse_ref'      => null,
            'verse_week_key' => null,
        ]);
    }

    public function test_student_toggle_life_item(): void
    {
        $item = JurnalLifeItem::create([
            'kategori'   => 'kerohanian',
            'label'      => 'Mengawali hari dengan berdoa',
            'is_default' => true,
            'is_active'  => true,
            'student_id' => null,
        ]);
        \DB::table('jurnal_student_life_items')->insert([
            'student_id'   => $this->student->id,
            'life_item_id' => $item->id,
        ]);

        $response = $this->actingAs($this->student)->postJson(route('jurnal.toggle'), [
            'type'    => 'life',
            'date'    => JurnalWeek::today()->toDateString(),
            'item_id' => $item->id,
            'checked' => true,
        ]);

        $response->assertOk()->assertJson(['ok' => true]);

        $this->assertDatabaseHas('jurnal_life_checks', [
            'student_id'   => $this->student->id,
            'life_item_id' => $item->id,
            'checked'      => true,
        ]);
    }

    public function test_verse_ref_visible_from_different_day_in_same_week(): void
    {
        $today     = JurnalWeek::today();
        $yesterday = $today->copy()->subDay()->toDateString();
        $todayStr  = $today->toDateString();

        // Save verse on yesterday
        $this->actingAs($this->student)->postJson(route('jurnal.toggle'), [
            'type'      => 'verse',
            'date'      => $yesterday,
            'verse_ref' => 'Matius 1:5',
        ]);

        // View jurnal for today — should show verse from yesterday
        $response = $this->actingAs($this->student)->get(route('jurnal.index', ['date' => $todayStr]));
        $response->assertStatus(200);
        $this->assertEquals('Matius 1:5', $response->viewData('verseRef'));
    }

    public function test_verse_ref_update_from_different_day_modifies_original_entry(): void
    {
        $today     = JurnalWeek::today();
        $yesterday = $today->copy()->subDay()->toDateString();
        $todayStr  = $today->toDateString();

        // Save on yesterday
        $this->actingAs($this->student)->postJson(route('jurnal.toggle'), [
            'type'      => 'verse',
            'date'      => $yesterday,
            'verse_ref' => 'Matius 1:5',
        ]);

        // Edit from today — should update yesterday's entry, not create new
        $this->actingAs($this->student)->postJson(route('jurnal.toggle'), [
            'type'      => 'verse',
            'date'      => $todayStr,
            'verse_ref' => 'Matius 1:23',
        ]);

        // Only 1 entry should have verse_week_key set
        $weekKey = JurnalWeek::weekKeyFor($today);
        $count   = \App\Models\JurnalEntry::where('student_id', $this->student->id)
            ->where('verse_week_key', $weekKey)
            ->count();
        $this->assertEquals(1, $count);

        // And it should hold the updated ref
        $this->assertDatabaseHas('jurnal_entries', [
            'student_id' => $this->student->id,
            'verse_ref'  => 'Matius 1:23',
        ]);
    }

    public function test_future_date_returns_422(): void
    {
        $future = JurnalWeek::today()->addDay()->toDateString();

        $response = $this->actingAs($this->student)->postJson(route('jurnal.toggle'), [
            'type'    => 'pl',
            'date'    => $future,
            'checked' => true,
        ]);

        $response->assertStatus(422);
    }
}
