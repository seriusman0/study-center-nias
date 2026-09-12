<?php
$file = 'app/Http/Controllers/Web/PublicJurnalController.php';
$content = file_get_contents($file);

$mabLogic = <<<PHP
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
PHP;

$content = preg_replace('/foreach \(\$checks as \$check\) \{.*?JurnalEntry::updateOrCreate\(.*?cabang_id.*?\]\s*\);/s', $mabLogic, $content);
file_put_contents($file, $content);
echo "Patched PublicJurnalController save method.";
