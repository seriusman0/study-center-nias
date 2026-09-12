<?php
$file = 'app/Http/Controllers/Web/PublicJurnalController.php';
$content = file_get_contents($file);

$queryLogic = <<<PHP
        \$allowedCategories = [];
        \$includeGlobal = false;
        \$hiddenLabels = [];

        if (\$user->hasRole('prajurit')) {
            \$allowedCategories = array_merge(\$allowedCategories, ['kerohanian', 'pendidikan', 'karakter', 'pembacaan', 'sidang', 'rohani', 'prajurit']);
        }
        if (\$user->hasRole('student')) {
            \$allowedCategories = array_merge(\$allowedCategories, ['kerohanian', 'pendidikan', 'karakter']);
        }
        if (\$user->hasRole('college')) {
            \$allowedCategories = array_merge(\$allowedCategories, ['pembacaan', 'sidang', 'rohani']);
            \$includeGlobal = true;
        }
        if (\$user->hasRole('scholarship_teenager')) {
            \$allowedCategories = array_merge(\$allowedCategories, ['pembacaan', 'sidang', 'rohani']);
            \$includeGlobal = true;
            \$hiddenLabels[] = 'Baca Buku Rohani (1 Bab / 1 Judul per Minggu)';
        }
        
        // If they have no known roles, fallback to just explicit assignments
        if (empty(\$allowedCategories)) {
             \$allowedCategories = ['kerohanian', 'pendidikan', 'karakter', 'pembacaan', 'sidang', 'rohani', 'prajurit', 'lain-lain'];
        } else {
             \$allowedCategories = array_unique(\$allowedCategories);
        }

        \$itemsQuery = JurnalLifeItem::where('is_active', true)
            ->whereIn('kategori', \$allowedCategories)
            ->where(function (\$q) use (\$user, \$includeGlobal) {
                if (\$includeGlobal) {
                    \$q->whereNull('student_id');
                }
                \$q->orWhere('student_id', \$user->id)
                  ->orWhereHas('assignedStudents', fn(\$a) => \$a->where('users.id', \$user->id));
            })
            ->when(!empty(\$hiddenLabels), fn(\$q) => \$q->whereNotIn('label', \$hiddenLabels))
            ->orderBy('kategori')
            ->orderBy('id');
PHP;

// Replace in scan()
$content = preg_replace('/\$itemsQuery = JurnalLifeItem::where.*?->orderBy\(\'kategori\'\)->orderBy\(\'id\'\);/s', $queryLogic, $content, 1);

// Replace in save()
$content = preg_replace('/\$itemsQuery = JurnalLifeItem::where.*?->orderBy\(\'kategori\'\)->orderBy\(\'id\'\);/s', $queryLogic, $content, 1);

file_put_contents($file, $content);
echo "Patched unified query logic.\n";
