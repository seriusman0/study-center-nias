<?php
$file = 'resources/views/home.blade.php';
$content = file_get_contents($file);

// Replace the entire modal body and config
$start = strpos($content, '<div class="p-3 sm:p-5">');
$end = strpos($content, '<div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-end');

if ($start !== false && $end !== false) {
    $newModalBody = <<<HTML
            <div class="p-4 sm:p-6">
                <div id="scannerCameraSection" class="flex flex-col items-center max-w-md mx-auto w-full">
                    <label class="block text-sm font-medium text-gray-700 mb-2 w-full text-center">Pilih Kamera</label>
                    <select id="cameraSelect" class="w-full max-w-xs border-gray-300 rounded-lg shadow-sm focus:border-sc-teal-500 py-2 px-3 text-sm mb-4 bg-white text-center"></select>
                    
                    <div id="qr-reader" class="w-full rounded-xl overflow-hidden shadow-sm border-2 border-gray-200 bg-black"></div>
                    
                    <div id="scan-status" class="mt-4 text-center font-mono text-sm px-4 py-2 bg-gray-100 rounded-lg text-gray-600 w-full max-w-xs">
                        Arahkan QR Code ke kamera.
                    </div>
                </div>
                
                <div id="scannerResultContainer" class="hidden max-w-2xl mx-auto w-full">
                    <div class="flex justify-between items-center mb-4 pb-3 border-b border-gray-200">
                        <h4 class="font-bold text-xl text-gray-800" id="jurnalModalTitleName"></h4>
                        <button type="button" onclick="resetScannerUI()" class="px-4 py-2 bg-sc-teal-100 text-sc-teal-700 text-sm font-bold rounded-lg hover:bg-sc-teal-200 transition flex items-center gap-2">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                            Scan QR Lain
                        </button>
                    </div>
                    <div class="flex items-center gap-4 bg-gray-50 p-3 rounded-xl border border-gray-100 mb-4">
                        <div id="jurnalAvatarCol" class="flex-shrink-0"></div>
                        <div>
                            <div id="jurnalPrajuritInfo" class="text-sm text-gray-600"></div>
                        </div>
                    </div>
                    <form id="publicJurnalForm" onsubmit="return false;">
                        <div id="jurnalChecklistContainer" class="space-y-3 max-h-[50vh] overflow-y-auto pr-2 custom-scrollbar"></div>
                    </form>
                </div>
            </div>
            
HTML;
    $content = substr_replace($content, $newModalBody, $start, $end - $start);
}

// Remove aspectRatio: 1.0 to fix "terlalu zoom"
$content = preg_replace('/\,\s*aspectRatio:\s*1\.0/', '', $content);

// In openPublicScanner(), ensure UI is reset
$resetJS = <<<JS
function resetScannerUI() {
    document.getElementById('scannerResultContainer').classList.add('hidden');
    document.getElementById('scannerCameraSection').classList.remove('hidden');
    document.getElementById('scan-status').innerHTML = '<span class="text-gray-600">Arahkan QR Code ke kamera.</span>';
    if(html5Qrcode && html5Qrcode.getState() !== 2) { // 2 = SCANNING
        let select = document.getElementById('cameraSelect');
        if(select && select.value) {
            startScanner(select.value);
        }
    }
}
JS;

if (strpos($content, 'function resetScannerUI') === false) {
    $content = str_replace('function closePublicScanner() {', $resetJS . "\n\nfunction closePublicScanner() {", $content);
}

// In openPublicJurnalModal, hide camera section
$openModalFix = <<<JS
    document.getElementById('scannerCameraSection').classList.add('hidden');
    document.getElementById('scannerResultContainer').classList.remove('hidden');
JS;
$content = preg_replace('/document\.getElementById\(\'scannerResultPlaceholder\'\).*?classList\.remove\(\'hidden\'\);/s', $openModalFix, $content);

// In closePublicScanner, hide result and show camera so next time it opens it's correct
$closeFix = <<<JS
    document.getElementById('scannerResultContainer').classList.add('hidden');
    document.getElementById('scannerCameraSection').classList.remove('hidden');
JS;
$content = preg_replace('/document\.getElementById\(\'scannerResultContent\'\).*?classList\.add\(\'flex\'\);/s', $closeFix, $content);

file_put_contents($file, $content);
echo "Patched home.blade.php for final UI overhaul.\n";
