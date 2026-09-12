<?php
$file = 'resources/views/home.blade.php';
$content = file_get_contents($file);

$oldCode = <<<JS
        if (devices && devices.length > 0) {
            devices.forEach(cam => {
                let opt = document.createElement('option');
                opt.value = cam.id;
                opt.text = cam.label || 'Kamera ' + cam.id;
                select.appendChild(opt);
            });
            let frontOpt = document.createElement('option');
            frontOpt.value = 'user';
            frontOpt.text = 'Kamera Depan (Default)';
            select.insertBefore(frontOpt, select.firstChild);
            
            let backOpt = document.createElement('option');
            backOpt.value = 'environment';
            backOpt.text = 'Kamera Belakang (Default)';
            select.insertBefore(backOpt, select.firstChild);
            
            select.value = 'environment';
        } else {
            select.innerHTML = '<option value="environment">Kamera Belakang</option><option value="user">Kamera Depan</option>';
        }

        setTimeout(() => {
            if (document.getElementById('publicScannerModal').classList.contains('hidden')) return;
            startScanner('environment');
        }, 400); 
JS;

$newCode = <<<JS
        if (devices && devices.length > 0) {
            let backCam = devices.find(c => c.label.toLowerCase().includes('back') || c.label.toLowerCase().includes('belakang'));
            let defaultId = backCam ? backCam.id : devices[0].id;
            
            devices.forEach(cam => {
                let opt = document.createElement('option');
                opt.value = cam.id;
                opt.text = cam.label || 'Kamera ' + cam.id;
                select.appendChild(opt);
            });
            
            select.value = defaultId;

            setTimeout(() => {
                if (document.getElementById('publicScannerModal').classList.contains('hidden')) return;
                startScanner(defaultId);
            }, 400); 
        } else {
            select.innerHTML = '<option value="environment">Kamera Belakang</option><option value="user">Kamera Depan</option>';
            setTimeout(() => {
                if (document.getElementById('publicScannerModal').classList.contains('hidden')) return;
                startScanner({ facingMode: "environment" });
            }, 400); 
        }
JS;

$content = str_replace($oldCode, $newCode, $content);
file_put_contents($file, $content);
echo "Patched home.blade.php";
