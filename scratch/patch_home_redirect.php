<?php
$file = 'resources/views/home.blade.php';
$content = file_get_contents($file);

// Replace onScanSuccess logic
$oldLogic = <<<JS
        if (data.status === 'not_found') {
            playSound('error');
            document.getElementById('scan-status').innerHTML = '<span class="text-red-500">Pengguna tidak ditemukan/tidak aktif.</span>';
            setTimeout(() => { scanning = false; }, 2500);
            return;
        }
        if (data.status === 'no_items') {
            playSound('error');
            document.getElementById('scan-status').innerHTML = '<span class="text-red-500">Tidak ada item jurnal.</span>';
            setTimeout(() => { scanning = false; }, 2500);
            return;
        }

        playSound('success');
        currentUser = data.user;
        currentItems = data.items;
        currentDate = data.today;
        
        document.getElementById('scan-status').innerHTML = '<span class="text-green-600 font-bold">Berhasil!</span>';
        openPublicJurnalModal(data);
        setTimeout(() => { scanning = false; }, 1500);
JS;

$newLogic = <<<JS
        if (data.status === 'not_found') {
            playSound('error');
            document.getElementById('scan-status').innerHTML = '<span class="text-red-500">' + (data.message || 'Pengguna tidak ditemukan/tidak aktif.') + '</span>';
            setTimeout(() => { scanning = false; }, 2500);
            return;
        }
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

$content = str_replace($oldLogic, $newLogic, $content);
file_put_contents($file, $content);
echo "Patched Javascript to handle redirect.\n";
