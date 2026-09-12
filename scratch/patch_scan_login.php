<?php
$file = 'app/Http/Controllers/Web/PublicJurnalController.php';
$content = file_get_contents($file);

$newScan = <<<PHP
    public function scan(Request \$request)
    {
        \$request->validate(['user_id' => 'required|integer']);

        \$user = User::where('id', \$request->user_id)->where('is_active', true)->first();

        if (!\$user) {
            return response()->json(['status' => 'not_found']);
        }
        
        // Security check: do not allow passwordless login for admin via QR
        if (\$user->isAdmin() || \$user->hasRole('mentor')) {
            return response()->json(['status' => 'not_found', 'message' => 'Admin/Mentor tidak bisa login via QR.']);
        }

        \Illuminate\Support\Facades\Auth::login(\$user);

        \$url = route('beranda');
        if (\$user->hasRole('prajurit')) {
            \$url = route('prajurit-jurnal.index');
        } elseif (\$user->hasRole('student')) {
            \$url = route('jurnal.index');
        } elseif (\$user->hasRole('college')) {
            \$url = route('college-jurnal.index');
        } elseif (\$user->hasRole('scholarship_teenager')) {
            \$url = route('scholarship-teenager-jurnal.index');
        }

        return response()->json([
            'status' => 'redirect',
            'url' => \$url
        ]);
    }
PHP;

$content = preg_replace('/public function scan\(Request \$request\).*?return \$this->buildResponse\(\$user, \$tanggal\);\n    \}/s', $newScan, $content);
file_put_contents($file, $content);
echo "Patched scan to login.\n";
