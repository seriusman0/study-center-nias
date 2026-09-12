<?php
$file = 'app/Http/Controllers/Web/PublicJurnalController.php';
$content = file_get_contents($file);

$oldSave = <<<PHP
    public function save(Request \$request)
    {
        \$request->validate([
            'user_id'           => 'required|integer|exists:users,id',
            'tanggal'           => 'required|date',
            'checks'            => 'nullable|array',
            'checks.*.item_id'  => 'required|integer',
            'checks.*.checked'  => 'nullable|boolean',
            'checks.*.value'    => 'nullable|numeric|min:0',
        ]);

        \$user = User::where('id', \$request->user_id)->where('is_active', true)->firstOrFail();
        \$tanggal = \$request->tanggal;
        \$checks  = \$request->checks ?? [];

                \$mabId = \App\Models\JurnalLifeItem::where('label', 'like', '%Membaca Alkitab Bersama-sama%')->value('id');
        \$mabChecked = false;
        \$mabPresent = false;

        foreach (\$checks as \$check) {
            \$itemId  = \$check['item_id'];
            \$checked = (bool) (\$check['checked'] ?? false);
            \$value   = \$check['value'] ?? null;

            if (\$mabId && \$itemId == \$mabId) {
                \$mabPresent = true;
                \$mabChecked = \$checked;
            }

            JurnalLifeCheck::updateOrCreate(
                [
                    'student_id'   => \$user->id,
                    'life_item_id' => \$itemId,
                    'tanggal'      => \$tanggal,
                ],
                [
                    'checked' => \$checked,
                    'value'   => \$value,
                ]
            );
        }

        \$entry = JurnalEntry::updateOrCreate(
            ['student_id' => \$user->id, 'tanggal' => \$tanggal],
            ['cabang_id'  => \$user->cabang_id]
        );

        if (\$mabPresent) {
            \$entry->update([
                'pl_checked' => \$mabChecked,
                'pb_checked' => \$mabChecked
            ]);
        }

        return response()->json(['status' => 'saved']);
    }
PHP;

$newSave = <<<PHP
    public function save(Request \$request)
    {
        \$request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'item_id' => 'required|integer',
            'action'  => 'required|string',
        ]);

        \$user = User::where('id', \$request->user_id)->where('is_active', true)->firstOrFail();
        \$tanggal = JurnalWeek::today()->toDateString();
        \$itemId = \$request->item_id;
        \$action = \$request->action;
        \$value = \$request->value;

        if (\$action === 'number') {
            JurnalLifeCheck::updateOrCreate(
                ['student_id' => \$user->id, 'life_item_id' => \$itemId, 'tanggal' => \$tanggal],
                ['checked' => true, 'value' => \$value]
            );
        } elseif (\$action === 'boolean') {
            JurnalLifeCheck::updateOrCreate(
                ['student_id' => \$user->id, 'life_item_id' => \$itemId, 'tanggal' => \$tanggal],
                ['checked' => (bool)\$value, 'value' => null]
            );
        } elseif (\$action === 'mab_pl' || \$action === 'mab_pb') {
            \$entry = JurnalEntry::updateOrCreate(
                ['student_id' => \$user->id, 'tanggal' => \$tanggal],
                ['cabang_id'  => \$user->cabang_id]
            );
            if (\$action === 'mab_pl') {
                \$entry->update(['pl_checked' => (bool)\$value]);
            } else {
                \$entry->update(['pb_checked' => (bool)\$value]);
            }
        }

        JurnalEntry::updateOrCreate(
            ['student_id' => \$user->id, 'tanggal' => \$tanggal],
            ['cabang_id'  => \$user->cabang_id]
        );

        // Fetch fresh data to return just like scan()
        \$itemsQuery = JurnalLifeItem::where('is_active', true)
            ->where(function (\$q) use (\$user) {
                if (\$user->hasRole('prajurit')) {
                    \$q->where('kategori', 'prajurit');
                }
                \$q->orWhere('student_id', \$user->id)
                  ->orWhereHas('assignedStudents', fn(\$a) => \$a->where('users.id', \$user->id));
            })->orderBy('kategori')->orderBy('id');
            
        \$items = \$itemsQuery->get();
        \$checkedIds = JurnalLifeCheck::where('student_id', \$user->id)->whereDate('tanggal', \$tanggal)->where('checked', true)->pluck('life_item_id')->all();
        \$numberValues = JurnalLifeCheck::where('student_id', \$user->id)->whereDate('tanggal', \$tanggal)->whereIn('life_item_id', \$items->where('response_type', 'number')->pluck('id'))->get()->pluck('value', 'life_item_id');
        
        \$entry = JurnalEntry::where('student_id', \$user->id)->where('tanggal', \$tanggal)->first();

        return response()->json([
            'status' => 'success',
            'user' => ['id' => \$user->id, 'name' => \$user->name, 'kelas' => \$user->studentProfile?->grade_class ?? \$user->collegeProfile?->institution_name, 'avatar' => \$user->avatar],
            'today' => \$tanggal,
            'today_formatted' => \Carbon\Carbon::parse(\$tanggal)->locale('id')->isoFormat('dddd, D MMMM YYYY'),
            'items' => \$items->map(fn(\$i) => ['id' => \$i->id, 'label' => \$i->label, 'nama' => \$i->nama, 'kategori' => \$i->kategori, 'tipe' => \$i->response_type, 'deskripsi' => \$i->deskripsi]),
            'checkedIds' => \$checkedIds,
            'numberValues' => \$numberValues,
            'pl_checked' => \$entry ? \$entry->pl_checked : false,
            'pb_checked' => \$entry ? \$entry->pb_checked : false,
        ]);
    }
PHP;

if (strpos($content, 'public function save') !== false) {
    $content = preg_replace('/public function save\(Request \$request\).*?\}\n\}/s', $newSave . "\n}", $content);
    file_put_contents($file, $content);
    echo "Patched save method.\n";
} else {
    echo "Save method not found.\n";
}
