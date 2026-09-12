<?php
$file = 'resources/views/home.blade.php';
$content = file_get_contents($file);

$newHtml = <<<HTML
                <div id="scannerCameraSection" class="flex flex-col items-center max-w-md mx-auto w-full">
                    <label class="block text-sm font-medium text-sc-ink-900 mb-2 w-full text-center">Pilih Kamera</label>
                    <select id="cameraSelect" class="w-full max-w-xs border-sc-line rounded-lg shadow-sm focus:border-sc-teal-500 py-2 px-3 text-sm mb-4 bg-white text-center"></select>
                    
                    <div id="qr-reader" class="w-full rounded-xl overflow-hidden shadow-sm border-2 border-sc-line bg-black"></div>
                    
                    <div id="scan-status" class="mt-4 text-center font-mono text-sm px-4 py-2 bg-gray-100 rounded-lg text-gray-600 w-full max-w-xs">
                        Arahkan QR Code ke kamera.
                    </div>
                </div>

                <div id="roleSelectionSection" class="hidden flex flex-col items-center max-w-md mx-auto w-full py-4 text-center">
                    <div id="roleSelectionAvatar" class="mb-4"></div>
                    <h4 class="text-lg font-bold text-sc-ink-900 mb-1" id="roleSelectionName"></h4>
                    <p class="text-sm text-sc-ink-500 mb-6">Akun Anda memiliki lebih dari 1 peran.<br>Pilih jurnal yang ingin diisi:</p>
                    <div id="roleSelectionButtons" class="w-full space-y-3"></div>
                </div>
HTML;

$content = preg_replace('/<div id="scannerCameraSection".*?Arahkan QR Code ke kamera\.\s*<\/div>\s*<\/div>/s', $newHtml, $content);
file_put_contents($file, $content);
echo "Patched HTML for role selection.\n";
