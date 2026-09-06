<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\CollegeBibleItem;
use App\Models\CollegeConfig;
use App\Models\JurnalEntry;
use App\Models\JurnalLifeCheck;
use App\Models\JurnalLifeItem;
use App\Models\MentorPresensi;
use App\Models\Presensi;
use App\Models\Role;
use App\Models\User;
use App\Support\JurnalWeek;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BerandaController extends Controller
{
    public function index()
    {
        $user     = auth()->user();
        $today    = now()->toDateString();
        $cabangId = $user->cabang_id;

        // QR code SVG (student ID)
        $qrRaw  = (string) QrCode::size(180)->format('svg')->generate((string) $user->id);
        $qrHtml = preg_replace('/(<svg[^>]+)\s+width="\d+"/', '$1 width="100%"', $qrRaw);
        $qrHtml = preg_replace('/(<svg[^>]+)\s+height="\d+"/', '$1 height="100%"', $qrHtml);

        // Journal progress today
        $todayEntry = JurnalEntry::where('student_id', $user->id)
            ->where('tanggal', $today)->first();

        $studentItemsQuery = JurnalLifeItem::where('is_active', true)
            ->where(function ($query) use ($user) {
                $query->where('student_id', $user->id)
                      ->orWhereHas('assignedStudents', fn($a) => $a->where('users.id', $user->id));
                
                $templateCats = [];
                if ($user->hasRole(['college', 'scholarship_teenager'])) {
                    $templateCats = array_merge($templateCats, ['pembacaan', 'sidang', 'rohani']);
                }
                
                if ($user->hasRole('prajurit')) {
                    $templateCats[] = 'prajurit';
                }
                
                if (!empty($templateCats)) {
                    $query->orWhere(function ($q2) use ($templateCats) {
                        $q2->whereNull('student_id')
                           ->whereIn('kategori', $templateCats);
                    });
                }
            });

        $studentItems = $studentItemsQuery->get(['id', 'label']);
        $totalLifeItems = $studentItems->count();
        $itemIds = $studentItems->pluck('id')->all();

        $lifeChecksToday = JurnalLifeCheck::where('student_id', $user->id)
            ->where('tanggal', $today)
            ->where('checked', true)
            ->whereIn('life_item_id', $itemIds)
            ->count();

        // "Baca Alkitab" and "Hafal Ayat" are special — they write to jurnal_entries,
        // not jurnal_life_checks, so count them separately.
        if ($studentItems->contains('label', 'Baca Alkitab')) {
            if ($todayEntry?->pl_checked || $todayEntry?->pb_checked) {
                $lifeChecksToday++;
            }
        }
        if ($studentItems->contains('label', 'Hafal Ayat')) {
            $weekKey  = JurnalWeek::weekKeyFor(JurnalWeek::today());
            $hasVerse = JurnalEntry::where('student_id', $user->id)
                ->where('verse_week_key', $weekKey)
                ->whereNotNull('verse_ref')
                ->exists();
            if ($hasVerse) {
                $lifeChecksToday++;
            }
        }

        // Bible reading for today
        $config     = CollegeConfig::current();
        $todayDate  = JurnalWeek::today();
        $dayNo      = $config->dayNoFor($todayDate);
        $scheduleId = $user->cabang?->bible_schedule_id ?? $config->active_schedule_id;
        $bibleItem  = CollegeBibleItem::forDayNo($dayNo, $scheduleId);

        // Blogs from same cabang (latest 6)
        $blogs = Blog::with(['user', 'cabang', 'tags'])
            ->whereNotNull('published_at')
            ->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
            ->latest('published_at')
            ->take(6)
            ->get();

        // Activity photos from presensis (max 20, most recent)
        $photos = Presensi::whereNotNull('foto')
            ->where('foto', '!=', '')
            ->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
            ->latest()
            ->take(20)
            ->pluck('foto');

        // Mentor-specific data
        $mentorPresensiToday = null;
        $mentorPresensiCount = 0;
        $presensiSiswaToday  = null;
        $presensiSiswaCount  = 0;
        if ($user->hasRole('mentor')) {
            $mentorPresensiToday = MentorPresensi::where('mentor_id', $user->id)
                ->whereDate('tanggal', $today)
                ->latest()
                ->first();
            $mentorPresensiCount = MentorPresensi::where('mentor_id', $user->id)->count();
            $presensiSiswaToday  = Presensi::where('mentor_id', $user->id)
                ->whereDate('tanggal', $today)
                ->with(['kelasMaster:id,nama', 'students:id,name'])
                ->latest()
                ->get();
            $presensiSiswaCount  = Presensi::where('mentor_id', $user->id)->count();
        }

        $collegeUsers = collect();
        if ($user->hasRole('college')) {
            $collegeRoleId = Role::where('name', 'college')->value('id');
            $collegeUsers = User::where('is_active', true)
                ->where('id', '!=', $user->id)
                ->whereHas('roles', fn($r) => $r->where('roles.id', $collegeRoleId))
                ->orderBy('name')
                ->get(['id', 'name', 'avatar', 'username']);
        } elseif ($user->hasRole('scholarship_teenager')) {
            $stRoleId = Role::where('name', 'scholarship_teenager')->value('id');
            $collegeUsers = User::where('is_active', true)
                ->where('id', '!=', $user->id)
                ->whereHas('roles', fn($r) => $r->where('roles.id', $stRoleId))
                ->orderBy('name')
                ->get(['id', 'name', 'avatar', 'username']);
        }

        return view('beranda', compact(
            'user', 'qrHtml', 'todayEntry', 'lifeChecksToday', 'totalLifeItems',
            'blogs', 'photos', 'today', 'bibleItem', 'dayNo', 'collegeUsers',
            'mentorPresensiToday', 'mentorPresensiCount', 'presensiSiswaToday', 'presensiSiswaCount'
        ));
    }
}
