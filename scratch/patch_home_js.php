<?php
$file = 'resources/views/home.blade.php';
$content = file_get_contents($file);

$oldJs = <<<JS
        if (data.status === 'redirect') {
            playSound('success');
            document.getElementById('scan-status').innerHTML = '<span class="text-green-600 font-bold">Berhasil! Mengalihkan...</span>';
            window.location.href = data.url;
            return;
        }

        playSound('error');
        document.getElementById('scan-status').innerHTML = '<span class="text-red-500">Gagal mengalihkan.</span>';
        setTimeout(() => { scanning = false; }, 2000);
JS;

$newJs = <<<JS
        if (data.status === 'redirect') {
            playSound('success');
            document.getElementById('scan-status').innerHTML = '<span class="text-green-600 font-bold">Berhasil! Mengalihkan...</span>';
            window.location.href = data.url;
            return;
        }
        
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

        playSound('error');
        document.getElementById('scan-status').innerHTML = '<span class="text-red-500">Gagal mengalihkan.</span>';
        setTimeout(() => { scanning = false; }, 2000);
JS;

$content = str_replace($oldJs, $newJs, $content);

// Ensure that closePublicScanner also hides roleSelectionSection and restores camera
$oldClose = <<<JS
function closePublicScanner() {
    document.getElementById('publicScannerModal').classList.add('hidden');
    try {
        if (html5Qrcode) {
            html5Qrcode.stop().then(() => {
                html5Qrcode.clear();
            }).catch(err => {
                html5Qrcode.clear();
            });
        }
    } catch (e) {}
    scanning = false;
}
JS;

$newClose = <<<JS
function closePublicScanner() {
    document.getElementById('publicScannerModal').classList.add('hidden');
    
    // Reset view visibility
    document.getElementById('scannerCameraSection').classList.remove('hidden');
    document.getElementById('roleSelectionSection').classList.add('hidden');
    document.getElementById('scan-status').innerHTML = 'Arahkan QR Code ke kamera.';
    
    try {
        if (html5Qrcode) {
            html5Qrcode.stop().then(() => {
                html5Qrcode.clear();
            }).catch(err => {
                html5Qrcode.clear();
            });
        }
    } catch (e) {}
    scanning = false;
}
JS;

$content = str_replace($oldClose, $newClose, $content);

file_put_contents($file, $content);
echo "Patched Javascript for multiple roles.\n";
