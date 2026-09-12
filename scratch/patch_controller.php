<?php
$file = 'app/Http/Controllers/Web/Admin/PrajuritJurnalAdminController.php';
$content = file_get_contents($file);

$insert = <<<PHP
    public function index(Request \$request)
    {
        \$roleId = Role::where('name', 'prajurit')->value('id');

        \$usersQ = User::where('is_active', true)
            ->whereHas('roles', fn(\$r) => \$r->where('roles.id', \$roleId))
            ->with('studentProfile')
            ->orderBy('name');

        if (\$request->filled('q')) {
            \$term = '%' . \$request->q . '%';
            \$usersQ->where(fn(\$w) => \$w->where('name', 'like', \$term)->orWhere('username', 'like', \$term));
        }

        \$users = \$usersQ->paginate(20)->withQueryString();

        return view('admin.prajurit-jurnal.index', compact('users'));
    }

PHP;

// Find position to insert before dashboard function
$pos = strpos($content, '    public function dashboard(Request $request)');
if ($pos !== false) {
    $newContent = substr($content, 0, $pos) . $insert . substr($content, $pos);
    file_put_contents($file, $newContent);
    echo "Patched index method successfully.";
} else {
    echo "Failed to find insertion point.";
}
