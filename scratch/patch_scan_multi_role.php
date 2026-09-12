<?php
$file = 'app/Http/Controllers/Web/PublicJurnalController.php';
$content = file_get_contents($file);

$newScan = <<<PHP
    public function scan(Request \$request)
    {
        \$request->validate(['user_id' => 'required|integer']);

        \$user = User::where('id', \$request->user_id)->where('is_active', true)->first();

        if (!\$user) {
            return response()->json(['status' => 'not_found', 'message' => 'Pengguna tidak ditemukan atau tidak aktif.']);
        }
        
        if (\$user->isAdmin() || \$user->hasRole('mentor')) {
            return response()->json(['status' => 'not_found', 'message' => 'Admin/Mentor tidak bisa login via QR.']);
        }

        \Illuminate\Support\Facades\Auth::login(\$user);

        \$availableRoutes = [];
        
        if (\$user->hasRole('prajurit')) {
            \$availableRoutes[] = [
                'name' => 'Jurnal Prajurit',
                'url' => route('prajurit-jurnal.index')
            ];
        }
        if (\$user->hasRole('student')) {
            \$availableRoutes[] = [
                'name' => 'Jurnal Student',
                'url' => route('jurnal.index')
            ];
        }
        if (\$user->hasRole('college')) {
            \$availableRoutes[] = [
                'name' => 'Jurnal College',
                'url' => route('college-jurnal.index')
            ];
        }
        if (\$user->hasRole('scholarship_teenager')) {
            \$availableRoutes[] = [
                'name' => 'Jurnal Teenager Scholarship',
                'url' => route('scholarship-teenager-jurnal.index')
            ];
        }

        if (count(\$availableRoutes) === 0) {
            // Fallback if they have none of the target roles
            return response()->json([
                'status' => 'redirect',
                'url' => route('beranda')
            ]);
        }
        
        if (count(\$availableRoutes) === 1) {
            return response()->json([
                'status' => 'redirect',
                'url' => \$availableRoutes[0]['url']
            ]);
        }

        return response()->json([
            'status' => 'multiple_roles',
            'user' => [
                'name' => \$user->name,
                'avatar' => \$user->avatar
            ],
            'routes' => \$availableRoutes
        ]);
    }
PHP;

$content = preg_replace('/public function scan\(Request \$request\).*?\n    \}/s', $newScan, $content);
file_put_contents($file, $content);
echo "Patched PublicJurnalController scan logic.\n";
