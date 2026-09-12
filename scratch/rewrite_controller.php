<?php
$file = 'app/Http/Controllers/Web/PublicJurnalController.php';

$content = <<<'PHP'
<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\JurnalEntry;
use App\Models\JurnalLifeCheck;
use App\Models\JurnalLifeItem;
use App\Models\User;
use App\Support\JurnalWeek;
use Illuminate\Http\Request;

class PublicJurnalController extends Controller
{
    private function getItemsQuery($user, $tanggal)
    {
        $allowedCategories = [];
        $includeGlobal = false;
        $hiddenLabels = [];

        if ($user->hasRole('prajurit')) {
            $allowedCategories = array_merge($allowedCategories, ['kerohanian', 'pendidikan', 'karakter', 'pembacaan', 'sidang', 'rohani', 'prajurit']);
        }
        if ($user->hasRole('student')) {
            $allowedCategories = array_merge($allowedCategories, ['kerohanian', 'pendidikan', 'karakter']);
        }
        if ($user->hasRole(['college'])) {
            $allowedCategories = array_merge($allowedCategories, ['pembacaan', 'sidang', 'rohani']);
            $includeGlobal = true;
        }
        if ($user->hasRole(['scholarship_teenager'])) {
            $allowedCategories = array_merge($allowedCategories, ['pembacaan', 'sidang', 'rohani']);
            $includeGlobal = true;
            $hiddenLabels[] = 'Baca Buku Rohani (1 Bab / 1 Judul per Minggu)';
        }
        
        if (empty($allowedCategories)) {
             $allowedCategories = ['kerohanian', 'pendidikan', 'karakter', 'pembacaan', 'sidang', 'rohani', 'prajurit', 'lain-lain'];
        } else {
             $allowedCategories = array_unique($allowedCategories);
        }

        return JurnalLifeItem::where('is_active', true)
            ->whereIn('kategori', $allowedCategories)
            ->where(function ($q) use ($user, $includeGlobal) {
                if ($includeGlobal) {
                    $q->whereNull('student_id');
                }
                $q->orWhere('student_id', $user->id)
                  ->orWhereHas('assignedStudents', fn($a) => $a->where('users.id', $user->id));
            })
            ->when(!empty($hiddenLabels), fn($q) => $q->whereNotIn('label', $hiddenLabels))
            ->orderBy('kategori')
            ->orderBy('id');
    }

    private function buildResponse($user, $tanggal)
    {
        $items = $this->getItemsQuery($user, $tanggal)->get();

        if ($items->isEmpty()) {
            return response()->json(['status' => 'no_items']);
        }

        $checkedIds = JurnalLifeCheck::where('student_id', $user->id)->whereDate('tanggal', $tanggal)->where('checked', true)->pluck('life_item_id')->all();
        $numberValues = JurnalLifeCheck::where('student_id', $user->id)->whereDate('tanggal', $tanggal)->whereIn('life_item_id', $items->where('response_type', 'number')->pluck('id'))->get()->pluck('value', 'life_item_id');
        
        $entry = JurnalEntry::where('student_id', $user->id)->where('tanggal', $tanggal)->first();

        return response()->json([
            'status' => 'success',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'kelas' => $user->studentProfile?->grade_class ?? $user->collegeProfile?->institution_name,
                'avatar' => $user->avatar
            ],
            'today' => $tanggal,
            'today_formatted' => \Carbon\Carbon::parse($tanggal)->locale('id')->isoFormat('dddd, D MMMM YYYY'),
            'items' => $items->map(fn($i) => [
                'id' => $i->id,
                'label' => $i->label,
                'nama' => $i->label,
                'kategori' => $i->kategori,
                'tipe' => $i->response_type,
                'deskripsi' => ''
            ]),
            'checkedIds' => $checkedIds,
            'numberValues' => $numberValues,
            'pl_checked' => $entry ? $entry->pl_checked : false,
            'pb_checked' => $entry ? $entry->pb_checked : false,
        ]);
    }

    public function scan(Request $request)
    {
        $request->validate(['user_id' => 'required|integer']);

        $user = User::with(['studentProfile', 'collegeProfile'])->where('id', $request->user_id)->where('is_active', true)->first();

        if (!$user) {
            return response()->json(['status' => 'not_found']);
        }

        $tanggal = JurnalWeek::today()->toDateString();

        return $this->buildResponse($user, $tanggal);
    }

    public function save(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'item_id' => 'required|integer',
            'action'  => 'required|string',
        ]);

        $user = User::with(['studentProfile', 'collegeProfile'])->where('id', $request->user_id)->where('is_active', true)->firstOrFail();
        $tanggal = JurnalWeek::today()->toDateString();
        $itemId = $request->item_id;
        $action = $request->action;
        $value = $request->value;

        if ($action === 'number') {
            JurnalLifeCheck::updateOrCreate(
                ['student_id' => $user->id, 'life_item_id' => $itemId, 'tanggal' => $tanggal],
                ['checked' => true, 'value' => $value]
            );
        } elseif ($action === 'boolean') {
            JurnalLifeCheck::updateOrCreate(
                ['student_id' => $user->id, 'life_item_id' => $itemId, 'tanggal' => $tanggal],
                ['checked' => (bool)$value, 'value' => null]
            );
        } elseif ($action === 'mab_pl' || $action === 'mab_pb') {
            $entry = JurnalEntry::updateOrCreate(
                ['student_id' => $user->id, 'tanggal' => $tanggal],
                ['cabang_id'  => $user->cabang_id]
            );
            if ($action === 'mab_pl') {
                $entry->update(['pl_checked' => (bool)$value]);
            } else {
                $entry->update(['pb_checked' => (bool)$value]);
            }
        }

        JurnalEntry::updateOrCreate(
            ['student_id' => $user->id, 'tanggal' => $tanggal],
            ['cabang_id'  => $user->cabang_id]
        );

        return $this->buildResponse($user, $tanggal);
    }
}
PHP;

file_put_contents($file, $content);
echo "Rewrote PublicJurnalController successfully.\n";
