<?php
$file = 'app/Http/Controllers/Web/PublicJurnalController.php';
$content = file_get_contents($file);

$itemsLogic = <<<PHP
        \$itemsQuery = JurnalLifeItem::where('is_active', true)
            ->where(function (\$q) use (\$user) {
                // If user is prajurit, they get prajurit category items automatically
                if (\$user->hasRole('prajurit')) {
                    \$q->where('kategori', 'prajurit');
                }
                
                // For all users, get explicitly assigned items
                \$q->orWhere('student_id', \$user->id)
                  ->orWhereHas('assignedStudents', fn(\$a) => \$a->where('users.id', \$user->id));
            })
            ->orderBy('kategori')
            ->orderBy('id');
            
        \$items = \$itemsQuery->get();
PHP;

$content = preg_replace('/\$items = JurnalLifeItem::forStudent\(\$user->id\).*?->get\(\);/s', $itemsLogic, $content);
file_put_contents($file, $content);
echo "Patched PublicJurnalController scan logic.";
