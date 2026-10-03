<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\JurnalEntry;
use App\Models\JurnalLifeCheck;
use App\Models\JurnalLifeItem;
use App\Models\CollegeConfig;
use App\Models\Role;
use App\Models\User;
use App\Support\JurnalWeek;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * API Controller untuk Aplikasi Presensi Offline Prajurit (Android)
 *
 * Semua endpoint di sini dirancang untuk mendukung skenario offline-first:
 *
 * 1. PULL (saat online): Android mengambil daftar prajurit + item jurnal lewat endpoint ini,
 *    menyimpan ke database lokal (SQLite/Room).
 *
 * 2. OFFLINE: Android melakukan presensi (scan QR, centang item) hanya ke database lokal.
 *    Operasi disimpan sebagai "pending sync records".
 *
 * 3. PUSH SYNC (saat kembali online): Android mengirim batch sync ke endpoint ini
 *    untuk mengunggah semua centangan yang dibuat offline.
 *
 * Authentication: Sanctum Token (admin/mentor).
 * Base URL: https://studycenter.nanoprojectdevindonesia.com/api/admin/prajurit-offline
 */
class PrajuritOfflineApiController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // 1. BOOTSTRAP — Data awal untuk inisialisasi database lokal
    // ─────────────────────────────────────────────────────────────

    /**
     * GET /bootstrap
     *
     * Mengambil semua data yang dibutuhkan APK untuk beroperasi offline:
     * - Daftar seluruh prajurit aktif (id, nama, username/QR payload)
     * - Semua life items kategori prajurit
     * - Konfigurasi form (jam buka/tutup)
     * - Server timestamp (referensi waktu)
     *
     * APK harus menyimpan respons ini ke local storage dan memperbaruinya
     * setiap kali online. Jika data tidak berubah (dibandingkan ETag/checksum),
     * APK bisa skip refresh.
     */
    public function bootstrap(Request $request): JsonResponse
    {
        $roleId = Role::where('name', 'prajurit')->value('id');
        $config = CollegeConfig::current();

        $prajurits = User::where('is_active', true)
            ->whereHas('roles', fn($r) => $r->where('roles.id', $roleId))
            ->with('studentProfile')
            ->orderBy('name')
            ->get()
            ->map(fn($u) => [
                'id'       => $u->id,
                'name'     => $u->name,
                'username' => $u->username,
                'kelas'    => $u->studentProfile?->grade_class,
                'avatar'   => $u->avatar,
                // QR code berisi string: user_id (integer sebagai string)
                // Contoh: QR = "42"  → user_id = 42
                'qr_payload' => (string) $u->id,
            ]);

        $items = JurnalLifeItem::where('is_active', true)
            ->where('kategori', 'prajurit')
            ->orderBy('id')
            ->get()
            ->map(fn($i) => [
                'id'            => $i->id,
                'label'         => $i->label,
                'response_type' => $i->response_type ?? 'boolean', // 'boolean' | 'number'
            ]);

        return response()->json([
            'server_time'     => now()->toIso8601String(),
            'server_date'     => JurnalWeek::today()->toDateString(),
            'config'          => [
                'form_open_time'  => $config->form_open_time,  // "06:00:00"
                'form_close_time' => $config->form_close_time, // "22:00:00"
            ],
            'prajurits'       => $prajurits,
            'life_items'      => $items,
            'total_prajurits' => $prajurits->count(),
            'total_items'     => $items->count(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // 2. SCAN — Cari data prajurit berdasarkan QR code
    // ─────────────────────────────────────────────────────────────

    /**
     * POST /scan
     *
     * Menerima user_id dari QR scan, mengembalikan info prajurit +
     * centangan hari ini (untuk pre-fill modal).
     *
     * ⚠ Endpoint ini opsional untuk operasi offline — APK bisa langsung
     * mencari dari data bootstrap lokal menggunakan qr_payload.
     * Gunakan endpoint ini hanya jika ingin data centangan real-time dari server.
     *
     * Body: { "user_id": 42, "date": "2026-09-27" }
     * Response: { "status": "found"|"not_found", "prajurit": {...}, "today_checks": [...] }
     */
    public function scan(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer',
            'date'    => 'nullable|date',
        ]);

        $roleId  = Role::where('name', 'prajurit')->value('id');
        $prajurit = User::with('studentProfile')
            ->where('id', $request->user_id)
            ->where('is_active', true)
            ->whereHas('roles', fn($r) => $r->where('roles.id', $roleId))
            ->first();

        if (!$prajurit) {
            return response()->json(['status' => 'not_found'], 404);
        }

        $date  = $request->filled('date')
            ? Carbon::parse($request->date, JurnalWeek::TZ)->startOfDay()
            : JurnalWeek::today();
        $dateStr = $date->toDateString();

        $items = JurnalLifeItem::where('is_active', true)
            ->where('kategori', 'prajurit')
            ->orderBy('id')
            ->get();

        $checks = JurnalLifeCheck::where('student_id', $prajurit->id)
            ->whereDate('tanggal', $dateStr)
            ->get()
            ->keyBy('life_item_id');

        $entry = JurnalEntry::where('student_id', $prajurit->id)
            ->whereDate('tanggal', $dateStr)
            ->first();

        return response()->json([
            'status'    => 'found',
            'prajurit'  => [
                'id'       => $prajurit->id,
                'name'     => $prajurit->name,
                'username' => $prajurit->username,
                'kelas'    => $prajurit->studentProfile?->grade_class,
                'avatar'   => $prajurit->avatar,
            ],
            'date'           => $dateStr,
            'today_checks'   => $items->map(fn($i) => [
                'item_id'  => $i->id,
                'checked'  => $checks->has($i->id) ? (bool) $checks[$i->id]->checked : false,
                'value'    => $checks->has($i->id) ? $checks[$i->id]->value : null,
            ]),
            'has_entry'  => !is_null($entry),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // 3. SYNC — Kirim batch centangan dari operasi offline
    // ─────────────────────────────────────────────────────────────

    /**
     * POST /sync
     *
     * Endpoint utama untuk push data dari APK offline ke server.
     * Menerima array "records" — setiap record adalah 1 centangan untuk
     * 1 prajurit pada 1 tanggal.
     *
     * Strategi conflict: Server selalu menerima data dari APK (last-write-wins
     * berdasarkan `offline_created_at`). Jika server sudah punya data yang lebih
     * baru, data server dipertahankan dan record APK diabaikan (field `skipped`).
     *
     * Body:
     * {
     *   "device_id": "android-device-uuid",  // optional, untuk logging
     *   "synced_by": "admin-user-id",         // user admin yang melakukan sync
     *   "records": [
     *     {
     *       "local_id":            "uuid-local-123",  // ID lokal APK (dikembalikan di response)
     *       "user_id":             42,
     *       "date":                "2026-09-27",
     *       "item_id":             25,
     *       "checked":             true,
     *       "value":               null,
     *       "offline_created_at":  "2026-09-27T14:30:00+07:00"
     *     },
     *     ...
     *   ]
     * }
     *
     * Response:
     * {
     *   "synced":  15,   // jumlah record berhasil disimpan
     *   "skipped": 2,    // record diabaikan (data lebih baru di server)
     *   "failed":  0,    // record gagal (validasi error)
     *   "results": [
     *     { "local_id": "uuid-local-123", "status": "ok" },
     *     { "local_id": "uuid-local-456", "status": "skipped", "reason": "newer_data_on_server" },
     *     ...
     *   ]
     * }
     */
    public function sync(Request $request): JsonResponse
    {
        $request->validate([
            'device_id'              => 'nullable|string|max:100',
            'records'                => 'required|array|min:1|max:500',
            'records.*.local_id'     => 'required|string|max:100',
            'records.*.user_id'      => 'required|integer|exists:users,id',
            'records.*.date'         => 'required|date',
            'records.*.item_id'      => 'required|integer|exists:jurnal_life_items,id',
            'records.*.checked'      => 'nullable|boolean',
            'records.*.value'        => 'nullable|numeric|min:0',
            'records.*.offline_created_at' => 'nullable|date',
        ]);

        $roleId = Role::where('name', 'prajurit')->value('id');
        $today  = JurnalWeek::today();

        $syncedCount  = 0;
        $skippedCount = 0;
        $failedCount  = 0;
        $results      = [];

        // Validasi semua user_id adalah prajurit (cache sekali)
        $validUserIds = User::where('is_active', true)
            ->whereHas('roles', fn($r) => $r->where('roles.id', $roleId))
            ->pluck('id')
            ->flip();

        foreach ($request->records as $rec) {
            $localId = $rec['local_id'];

            // Validasi user adalah prajurit aktif
            if (!$validUserIds->has($rec['user_id'])) {
                $failedCount++;
                $results[] = ['local_id' => $localId, 'status' => 'failed', 'reason' => 'user_not_prajurit'];
                continue;
            }

            $date = Carbon::parse($rec['date'], JurnalWeek::TZ)->startOfDay();

            // Tolak tanggal masa depan
            if ($date->greaterThan($today)) {
                $failedCount++;
                $results[] = ['local_id' => $localId, 'status' => 'failed', 'reason' => 'future_date'];
                continue;
            }

            $offlineTs = isset($rec['offline_created_at'])
                ? Carbon::parse($rec['offline_created_at'])
                : null;

            try {
                DB::transaction(function () use ($rec, $date, $offlineTs, &$syncedCount, &$skippedCount, &$results, $localId) {
                    $prajurit = User::find($rec['user_id']);
                    $checked  = (bool) ($rec['checked'] ?? false);
                    $value    = $rec['value'] ?? null;
                    $dateStr  = $date->toDateString();
                    $itemId   = $rec['item_id'];

                    // Conflict resolution: cek apakah server punya data lebih baru
                    if ($offlineTs) {
                        $serverRecord = JurnalLifeCheck::where('student_id', $rec['user_id'])
                            ->where('life_item_id', $itemId)
                            ->whereDate('tanggal', $dateStr)
                            ->first();

                        if ($serverRecord && $serverRecord->updated_at > $offlineTs) {
                            $skippedCount++;
                            $results[] = ['local_id' => $localId, 'status' => 'skipped', 'reason' => 'newer_data_on_server'];
                            return;
                        }
                    }

                    // Simpan centangan
                    $match = [
                        'student_id'   => $rec['user_id'],
                        'life_item_id' => $itemId,
                        'tanggal'      => $dateStr,
                    ];
                    $updateData = [
                        'checked' => $checked,
                        'value'   => $value,
                    ];

                    if (JurnalLifeCheck::where($match)->exists()) {
                        JurnalLifeCheck::where($match)->update($updateData);
                    } else {
                        JurnalLifeCheck::create(array_merge($match, $updateData));
                    }

                    // Pastikan JurnalEntry ada (tanda sudah ada aktivitas pada tanggal ini)
                    $entry = JurnalEntry::firstOrCreate(
                        ['student_id' => $rec['user_id'], 'tanggal' => $dateStr],
                        ['cabang_id'  => $prajurit->cabang_id]
                    );

                    // Sinkronisasi MAB logic (Membaca Alkitab Bersama-sama → pl_checked + pb_checked)
                    $mabId = JurnalLifeItem::where('label', 'like', '%Membaca Alkitab Bersama-sama%')->value('id');
                    if ($mabId && $itemId == $mabId) {
                        $entry->update([
                            'pl_checked' => $checked,
                            'pb_checked' => $checked,
                        ]);
                    }

                    $syncedCount++;
                    $results[] = ['local_id' => $localId, 'status' => 'ok'];
                });
            } catch (\Throwable $e) {
                $failedCount++;
                $results[] = ['local_id' => $localId, 'status' => 'failed', 'reason' => 'server_error'];
            }
        }

        return response()->json([
            'synced'  => $syncedCount,
            'skipped' => $skippedCount,
            'failed'  => $failedCount,
            'results' => $results,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // 4. PRESENSI TODAY — Snapshot centangan seluruh prajurit hari ini
    // ─────────────────────────────────────────────────────────────

    /**
     * GET /today-snapshot
     *
     * Mengambil status centangan SEMUA prajurit untuk tanggal tertentu.
     * Digunakan setelah sync untuk verifikasi atau untuk memperbarui tampilan
     * daftar di APK.
     *
     * Query params: ?date=2026-09-27 (opsional, default hari ini)
     */
    public function todaySnapshot(Request $request): JsonResponse
    {
        $request->validate(['date' => 'nullable|date']);

        $date = $request->filled('date')
            ? Carbon::parse($request->date, JurnalWeek::TZ)->startOfDay()
            : JurnalWeek::today();
        $dateStr = $date->toDateString();

        $roleId = Role::where('name', 'prajurit')->value('id');

        $prajurits = User::where('is_active', true)
            ->whereHas('roles', fn($r) => $r->where('roles.id', $roleId))
            ->orderBy('name')
            ->get();

        $userIds = $prajurits->pluck('id');

        // Ambil semua checks hari ini sekaligus
        $allChecks = JurnalLifeCheck::whereIn('student_id', $userIds)
            ->whereDate('tanggal', $dateStr)
            ->get()
            ->groupBy('student_id');

        $items = JurnalLifeItem::where('is_active', true)
            ->where('kategori', 'prajurit')
            ->orderBy('id')
            ->pluck('id');

        $data = $prajurits->map(function ($u) use ($allChecks, $items, $dateStr) {
            $checks     = $allChecks->get($u->id, collect())->keyBy('life_item_id');
            $checkedIds = $checks->filter(fn($c) => (bool) $c->checked)->pluck('life_item_id')->values();

            return [
                'user_id'     => $u->id,
                'name'        => $u->name,
                'has_activity'=> $checkedIds->count() > 0,
                'checked_ids' => $checkedIds,
                'number_values' => $checks
                    ->filter(fn($c) => !is_null($c->value))
                    ->map(fn($c) => ['item_id' => $c->life_item_id, 'value' => $c->value])
                    ->values(),
            ];
        });

        return response()->json([
            'date'       => $dateStr,
            'total'      => $prajurits->count(),
            'active'     => $data->where('has_activity', true)->count(),
            'prajurits'  => $data,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // 5. SINGLE SAVE — Simpan centangan 1 prajurit (saat online)
    // ─────────────────────────────────────────────────────────────

    /**
     * POST /save
     *
     * Menyimpan semua centangan untuk 1 prajurit pada 1 tanggal.
     * Sama seperti fungsi saveJurnal di web admin, tapi via JSON API.
     * Gunakan ini saat device ONLINE untuk instant save (tanpa antri sync).
     *
     * Body:
     * {
     *   "user_id": 42,
     *   "date":    "2026-09-27",
     *   "checks": [
     *     { "item_id": 25, "checked": true,  "value": null },
     *     { "item_id": 27, "checked": true,  "value": 3    },
     *     { "item_id": 28, "checked": false, "value": null }
     *   ]
     * }
     */
    public function save(Request $request): JsonResponse
    {
        $request->validate([
            'user_id'           => 'required|integer|exists:users,id',
            'date'              => 'required|date',
            'checks'            => 'required|array|min:1',
            'checks.*.item_id'  => 'required|integer|exists:jurnal_life_items,id',
            'checks.*.checked'  => 'nullable|boolean',
            'checks.*.value'    => 'nullable|numeric|min:0',
        ]);

        $roleId   = Role::where('name', 'prajurit')->value('id');
        $prajurit = User::where('id', $request->user_id)
            ->where('is_active', true)
            ->whereHas('roles', fn($r) => $r->where('roles.id', $roleId))
            ->firstOrFail();

        $dateStr = Carbon::parse($request->date, JurnalWeek::TZ)->toDateString();
        $today   = JurnalWeek::today()->toDateString();

        if ($dateStr > $today) {
            return response()->json(['ok' => false, 'message' => 'Tanggal masa depan tidak diizinkan.'], 422);
        }

        $mabId      = JurnalLifeItem::where('label', 'like', '%Membaca Alkitab Bersama-sama%')->value('id');
        $mabChecked = false;
        $mabPresent = false;

        DB::transaction(function () use ($request, $prajurit, $dateStr, $mabId, &$mabChecked, &$mabPresent) {
            foreach ($request->checks as $check) {
                $itemId  = $check['item_id'];
                $checked = (bool) ($check['checked'] ?? false);
                $value   = $check['value'] ?? null;

                if ($mabId && $itemId == $mabId) {
                    $mabPresent = true;
                    $mabChecked = $checked;
                }

                JurnalLifeCheck::updateOrCreate(
                    ['student_id' => $prajurit->id, 'life_item_id' => $itemId, 'tanggal' => $dateStr],
                    ['checked' => $checked, 'value' => $value]
                );
            }

            $entry = JurnalEntry::updateOrCreate(
                ['student_id' => $prajurit->id, 'tanggal' => $dateStr],
                ['cabang_id'  => $prajurit->cabang_id]
            );

            if ($mabPresent) {
                $entry->update(['pl_checked' => $mabChecked, 'pb_checked' => $mabChecked]);
            }
        });

        return response()->json([
            'ok'   => true,
            'date' => $dateStr,
            'user_id' => $prajurit->id,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // 6. HISTORY — Riwayat presensi range tanggal untuk 1 prajurit
    // ─────────────────────────────────────────────────────────────

    /**
     * GET /history/{userId}
     *
     * Mengambil riwayat centangan prajurit tertentu dalam range tanggal.
     * Digunakan untuk menampilkan laporan di APK.
     *
     * Query params: ?from=2026-09-01&to=2026-09-27
     */
    public function history(Request $request, int $userId): JsonResponse
    {
        $request->validate([
            'from' => 'required|date',
            'to'   => 'required|date|after_or_equal:from',
        ]);

        $roleId   = Role::where('name', 'prajurit')->value('id');
        $prajurit = User::where('id', $userId)
            ->where('is_active', true)
            ->whereHas('roles', fn($r) => $r->where('roles.id', $roleId))
            ->firstOrFail();

        $from    = Carbon::parse($request->from, JurnalWeek::TZ)->startOfDay();
        $to      = Carbon::parse($request->to, JurnalWeek::TZ)->startOfDay();
        $fromStr = $from->toDateString();
        $toStr   = $to->toDateString();

        $checks = JurnalLifeCheck::where('student_id', $prajurit->id)
            ->whereBetween(DB::raw('DATE(tanggal)'), [$fromStr, $toStr])
            ->get()
            ->groupBy(fn($c) => $c->tanggal->toDateString());

        $days = [];
        for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
            $key       = $d->toDateString();
            $dayChecks = $checks->get($key, collect());
            $days[]    = [
                'date'        => $key,
                'checked_ids' => $dayChecks->where('checked', true)->pluck('life_item_id')->values(),
                'number_values' => $dayChecks
                    ->filter(fn($c) => !is_null($c->value))
                    ->map(fn($c) => ['item_id' => $c->life_item_id, 'value' => $c->value])
                    ->values(),
                'has_activity' => $dayChecks->where('checked', true)->count() > 0,
            ];
        }

        return response()->json([
            'user_id' => $prajurit->id,
            'name'    => $prajurit->name,
            'from'    => $fromStr,
            'to'      => $toStr,
            'days'    => $days,
        ]);
    }
}
