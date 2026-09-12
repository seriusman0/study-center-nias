<?php
$file = 'resources/views/home.blade.php';
$content = file_get_contents($file);

$oldBtn = <<<HTML
        <div class="mt-8 flex justify-center w-full relative z-10">
            <button type="button" onclick="openPublicScanner()" class="px-8 py-4 bg-white text-sc-teal-700 font-bold rounded-xl shadow-lg hover:bg-gray-50 transition transform hover:-translate-y-1 inline-flex items-center gap-2 text-lg border-b-4 border-sc-teal-200">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><rect x="7" y="7" width="3" height="3"/><rect x="14" y="7" width="3" height="3"/><rect x="7" y="14" width="3" height="3"/><rect x="14" y="14" width="3" height="3"/></svg>
                Isi Jurnal via Scan QR
            </button>
        </div>
HTML;

$newBtn = <<<HTML
        <div class="mt-12 flex justify-center w-full relative z-10 px-4 sm:px-0">
            <button type="button" onclick="openPublicScanner()" 
                class="group relative flex w-full sm:w-auto items-center justify-between sm:justify-center gap-4 px-6 sm:px-8 py-4 sm:py-5 bg-white rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.15)] hover:shadow-[0_8px_30px_rgba(255,255,255,0.25)] transition-all duration-300 transform hover:-translate-y-1 focus:outline-none focus:ring-4 focus:ring-white/50 overflow-hidden">
                
                <!-- Animasi kilap (shine effect) -->
                <div class="absolute inset-0 flex h-full w-full justify-center [transform:skew(-12deg)_translateX(-150%)] group-hover:duration-1000 group-hover:[transform:skew(-12deg)_translateX(150%)] pointer-events-none">
                    <div class="relative h-full w-12 bg-sc-teal-50/60"></div>
                </div>

                <div class="flex items-center gap-4 sm:gap-5 relative z-10">
                    <!-- Ikon QR Code -->
                    <div class="flex items-center justify-center bg-sc-teal-50 text-sc-teal-600 p-3 sm:p-4 rounded-xl shadow-inner group-hover:bg-sc-teal-100 group-hover:text-sc-teal-700 transition-colors">
                        <svg class="w-8 h-8 sm:w-9 sm:h-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                            <rect x="7" y="7" width="3" height="3"/>
                            <rect x="14" y="7" width="3" height="3"/>
                            <rect x="7" y="14" width="3" height="3"/>
                            <rect x="14" y="14" width="3" height="3"/>
                        </svg>
                    </div>
                    
                    <!-- Teks -->
                    <div class="flex flex-col text-left">
                        <span class="text-xs sm:text-sm font-bold text-sc-teal-600/80 uppercase tracking-wider mb-0.5 group-hover:text-sc-teal-700 transition-colors">Akses Cepat</span>
                        <span class="text-xl sm:text-2xl font-black text-sc-ink-900 tracking-tight leading-none">Isi Jurnal via QR</span>
                    </div>
                </div>
                
                <!-- Ikon Panah -->
                <div class="bg-gray-100 p-2.5 rounded-full group-hover:bg-sc-teal-600 group-hover:text-white text-gray-400 transition-all duration-300 relative z-10 shadow-sm">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </button>
        </div>
HTML;

$content = str_replace($oldBtn, $newBtn, $content);
file_put_contents($file, $content);
echo "Patched button HTML.\n";
