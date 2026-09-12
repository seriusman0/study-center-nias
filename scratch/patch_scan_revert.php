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

$content = preg_replace('/public function scan\(Request \$request\).*?\n    \}/s', $newScan, $content);
file_put_contents($file, $content);

$file2 = 'resources/views/home.blade.php';
$content2 = file_get_contents($file2);

// Remove roleSelectionSection HTML
$content2 = preg_replace('/<div id="roleSelectionSection".*?<\/div>\s*<\/div>\s*<\/div>/s', "</div>\n            </div>", $content2);

// Revert JS
$oldJs = <<<JS
        if (data.status === 'multiple_roles') {
            playSound('success');
            
            // Stop scanning and hide camera
            try { if (html5Qrcode) { html5Qrcode.clear(); } } catch (e) {}
            document.getElementById('scannerCameraSection').classList.add('hidden');
            document.getElementById('roleSelectionSection').classList.remove('hidden');
            
            // Populate user info
            document.getElementById('roleSelectionName').innerText = data.user.name;
            let avatarUrl = data.user.avatar || 'https://ui-avatars.com/api/?name='+encodeURIComponent(data.user.name)+'&size=150&background=0F766E&color=fff';
            document.getElementById('roleSelectionAvatar').innerHTML = `<img src="\${avatarUrl}" class="rounded-full shadow-sm object-cover border-4 border-sc-teal-200 mx-auto" style="width: 80px; height: 80px;" alt="Foto Profil">`;
            
            // Generate buttons
            let btnsHtml = '';
            data.routes.forEach(route => {
                btnsHtml += `<button onclick="window.location.href='\${route.url}'" class="w-full flex items-center justify-between px-5 py-3 bg-white border border-sc-line rounded-xl hover:bg-sc-teal-50 hover:border-sc-teal-200 transition group text-left shadow-sm">
                    <span class="font-bold text-sc-teal-800 group-hover:text-sc-teal-700">\${route.name}</span>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-sc-teal-500"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </button>`;
            });
            document.getElementById('roleSelectionButtons').innerHTML = btnsHtml;
            return;
        }
JS;

$content2 = str_replace($oldJs, "", $content2);

// Revert closePublicScanner JS
$content2 = preg_replace("/document\.getElementById\('scannerCameraSection'\)\.classList\.remove\('hidden'\);\s*document\.getElementById\('roleSelectionSection'\)\.classList\.add\('hidden'\);/s", "", $content2);

file_put_contents($file2, $content2);
echo "Reverted multi-role selection modal logic.\n";
