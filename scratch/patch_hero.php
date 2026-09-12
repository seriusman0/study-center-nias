<?php
$file = 'resources/views/home.blade.php';
$content = file_get_contents($file);

// Replace the eyebrow
$oldEyebrow = '<p class="sc-eyebrow text-sc-yellow-300 mb-3">KOMUNITAS BELAJAR NIAS</p>';
$newEyebrow = '<p class="text-white font-extrabold tracking-[0.2em] text-sm mb-3 drop-shadow-md">KOMUNITAS BELAJAR NIAS</p>';
$content = str_replace($oldEyebrow, $newEyebrow, $content);

// Remove the old giant button
$content = preg_replace('/<div class="mt-12 flex justify-center w-full relative z-10 px-4 sm:px-0">.*?<\/button>\s*<\/div>/s', '', $content);

// Add the new button alongside the others
$oldButtonsGroup = <<<HTML
        <div class="flex gap-3 justify-center flex-wrap">
            <a href="{{ route('blog.index') }}"
               class="px-6 py-3 bg-sc-orange-500 text-white font-semibold rounded-lg hover:bg-sc-orange-600 transition shadow-sc-2">
                Baca Blog
            </a>
            @guest
            <a href="{{ route('register') }}"
               class="px-6 py-3 border border-white/40 rounded-lg hover:bg-white/10 transition font-medium">
                Bergabung
            </a>
            @endguest
        </div>
HTML;

$newButtonsGroup = <<<HTML
        <div class="flex flex-col sm:flex-row gap-3 justify-center items-center flex-wrap">
            <button type="button" onclick="openPublicScanner()" 
               class="px-6 py-3 bg-white text-sc-teal-700 font-semibold rounded-lg hover:bg-gray-100 transition shadow-lg flex items-center gap-2 w-full sm:w-auto justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Scan Jurnal
            </button>
            <a href="{{ route('blog.index') }}"
               class="px-6 py-3 bg-sc-orange-500 text-white font-semibold rounded-lg hover:bg-sc-orange-600 transition shadow-sc-2 w-full sm:w-auto text-center">
                Baca Blog
            </a>
            @guest
            <a href="{{ route('register') }}"
               class="px-6 py-3 border border-white/40 text-white rounded-lg hover:bg-white/10 transition font-medium w-full sm:w-auto text-center">
                Bergabung
            </a>
            @endguest
        </div>
HTML;

$content = str_replace($oldButtonsGroup, $newButtonsGroup, $content);
file_put_contents($file, $content);
echo "Patched Hero HTML.\n";
