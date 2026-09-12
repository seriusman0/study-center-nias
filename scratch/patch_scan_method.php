<?php
$file = 'app/Http/Controllers/Web/PublicJurnalController.php';
$content = file_get_contents($file);

$oldScanEnd = <<<PHP
        return response()->json([
            'status'   => 'found',
            'user' => [
                'id'     => \$user->id,
                'name'   => \$user->name,
                'kelas'  => \$user->studentProfile?->grade_class ?? \$user->collegeProfile?->institution_name,
                'avatar' => \$user->avatar,
            ],
            'today'           => \$today,
            'today_formatted' => \Carbon\Carbon::parse(\$today)->locale('id')->isoFormat('dddd, D MMMM YYYY'),
            'items'           => \$items->map(fn(\$i) => [
                'id'            => \$i->id,
                'label'         => \$i->label,
                'kategori'      => \$i->kategori,
                'response_type' => \$i->response_type,
            ]),
            'checkedIds'   => \$checkedIds,
            'numberValues' => \$numberValues,
        ]);
PHP;

$newScanEnd = <<<PHP
        \$entry = JurnalEntry::where('student_id', \$user->id)->where('tanggal', \$today)->first();

        return response()->json([
            'status'   => 'found',
            'user' => [
                'id'     => \$user->id,
                'name'   => \$user->name,
                'kelas'  => \$user->studentProfile?->grade_class ?? \$user->collegeProfile?->institution_name,
                'avatar' => \$user->avatar,
            ],
            'today'           => \$today,
            'today_formatted' => \Carbon\Carbon::parse(\$today)->locale('id')->isoFormat('dddd, D MMMM YYYY'),
            'items'           => \$items->map(fn(\$i) => [
                'id'            => \$i->id,
                'label'         => \$i->label,
                'nama'          => \$i->label, // Fallback if nama doesn't exist, though usually label == nama
                'kategori'      => \$i->kategori,
                'tipe'          => \$i->response_type,
                'deskripsi'     => \$i->description ?? '', // Using description since it might be description
            ]),
            'checkedIds'   => \$checkedIds,
            'numberValues' => \$numberValues,
            'pl_checked'   => \$entry ? \$entry->pl_checked : false,
            'pb_checked'   => \$entry ? \$entry->pb_checked : false,
        ]);
PHP;

$content = str_replace($oldScanEnd, $newScanEnd, $content);
file_put_contents($file, $content);
echo "Patched scan method.\n";
