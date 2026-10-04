import re

with open('resources/views/jurnal/index.blade.php', 'r') as f:
    text = f.read()

# 1. Change "Pembacaan Alkitab" to "Allah"
text = re.sub(
    r'<h2 class="text-lg font-bold text-sc-ink-900 mb-1 flex items-center gap-2">\s*<span class="w-7 h-7 rounded-lg bg-sc-teal-700 text-white text-sm font-bold flex items-center justify-center">1</span>\s*Pembacaan Alkitab\s*</h2>',
    r'<h2 class="text-lg font-bold text-sc-ink-900 mb-4 flex items-center gap-2">\n            <span class="w-7 h-7 rounded-lg bg-sc-teal-700 text-white text-sm font-bold flex items-center justify-center">1</span>\n            Allah\n        </h2>\n        <h3 class="text-sm font-bold text-sc-teal-700 uppercase tracking-wider mb-2">Pembacaan Alkitab</h3>',
    text
)

# 2. Merge Hafal Ayat into Allah card
# The Hafal Ayat card starts with:
# </div>
#
#    {{-- Hafal Ayat --}}
#    <div class="bg-white shadow-sc-1 border border-sc-line rounded-2xl p-5 mb-4"
#         x-data="hafalAyat(jurnalPage_cfg)">
#        <h2 class="text-lg font-bold text-sc-ink-900 mb-1 flex items-center gap-2">
#            <span class="w-7 h-7 rounded-lg bg-sc-teal-700 text-white text-sm font-bold flex items-center justify-center">2</span>
#            Hafal Ayat Mingguan
#        </h2>

text = re.sub(
    r'</div>\s*\{\{-- Hafal Ayat --\}\}\s*<div class="bg-white shadow-sc-1 border border-sc-line rounded-2xl p-5 mb-4"\s*x-data="hafalAyat\(jurnalPage_cfg\)">\s*<h2 class="text-lg font-bold text-sc-ink-900 mb-1 flex items-center gap-2">\s*<span class="w-7 h-7 rounded-lg bg-sc-teal-700 text-white text-sm font-bold flex items-center justify-center">2</span>\s*Hafal Ayat Mingguan\s*</h2>',
    r'\n        <div class="my-6 border-t border-sc-line"></div>\n\n        {{-- Hafal Ayat --}}\n        <div x-data="hafalAyat(jurnalPage_cfg)">\n            <h3 class="text-sm font-bold text-sc-teal-700 uppercase tracking-wider mb-2">Hafalan Ayat</h3>',
    text
)

# 3. Jadwal Kehidupan -> We want to move `kerohanian` into the Allah card, and make Pendidikan and Karakter their own cards!
# Let's completely replace the Jadwal Kehidupan block.

jadwal_start = text.find('{{-- Jadwal Kehidupan --}}')
foto_start = text.find('{{-- Foto Saat Belajar --}}')

new_jadwal = """
        {{-- Mengawali Hari Dengan Berdoa (Kerohanian) --}}
        <div class="my-6 border-t border-sc-line"></div>
        <div class="space-y-2">
            @if(!empty($lifeItems['kerohanian']))
                @foreach($lifeItems['kerohanian'] as $item)
                    @if(in_array($item->label, ['Baca Alkitab', 'Hafal Ayat', 'Perjanjian Lama', 'Perjanjian Baru']))
                        @continue
                    @endif
                    <label class="flex items-center gap-3 p-2 rounded-lg border border-sc-line hover:bg-sc-teal-50 cursor-pointer transition">
                        <input type="checkbox" class="w-5 h-5 accent-sc-teal-600"
                            :checked="state.life.includes({{ $item->id }})"
                            @change="toggle('life', {{ $item->id }}, $event.target.checked)">
                        <span class="text-sm">{{ $item->label }}</span>
                    </label>
                @endforeach
            @endif
        </div>
    </div> <!-- Close Allah Card -->

    {{-- Pendidikan --}}
    <div class="bg-white shadow-sc-1 border border-sc-line rounded-2xl p-5 mb-4">
        <h2 class="text-lg font-bold text-sc-ink-900 mb-4 flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg bg-sc-teal-700 text-white text-sm font-bold flex items-center justify-center">2</span>
            Pendidikan
        </h2>
        <div class="space-y-2">
            @if(!empty($lifeItems['pendidikan']))
                @foreach($lifeItems['pendidikan'] as $item)
                    <label class="flex items-center gap-3 p-2 rounded-lg border border-sc-line hover:bg-sc-teal-50 cursor-pointer transition">
                        <input type="checkbox" class="w-5 h-5 accent-sc-teal-600"
                            :checked="state.life.includes({{ $item->id }})"
                            @change="toggle('life', {{ $item->id }}, $event.target.checked)">
                        <span class="text-sm">{{ $item->label }}</span>
                    </label>
                @endforeach
            @endif
        </div>
    </div>

    {{-- Karakter --}}
    <div class="bg-white shadow-sc-1 border border-sc-line rounded-2xl p-5 mb-4">
        <h2 class="text-lg font-bold text-sc-ink-900 mb-4 flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg bg-sc-teal-700 text-white text-sm font-bold flex items-center justify-center">3</span>
            Karakter
        </h2>
        <div class="space-y-2 mb-6">
            @if(!empty($lifeItems['karakter']))
                @foreach($lifeItems['karakter'] as $item)
                    <label class="flex items-center gap-3 p-2 rounded-lg border border-sc-line hover:bg-sc-teal-50 cursor-pointer transition">
                        <input type="checkbox" class="w-5 h-5 accent-sc-teal-600"
                            :checked="state.life.includes({{ $item->id }})"
                            @change="toggle('life', {{ $item->id }}, $event.target.checked)">
                        <span class="text-sm">{{ $item->label }}</span>
                    </label>
                @endforeach
            @endif
        </div>
"""

text = text[:jadwal_start] + new_jadwal + text[foto_start:]

# Fix the Foto Saat Belajar number (from 4 to 4... wait, Karakter is 3, but I want Foto Saat Belajar to be part of Karakter? No, the user wrote:
# ### Karakter
# - Menyapa Orangtua/guru/kakak
# - Merapikan tempat tidur
# 
# > Foto Saat belajar
# Let's put Foto inside the Karakter card!

foto_end = text.find('</div>', text.find('x-show="!fotoBelajar_cfg"')) # actually I'll just change the title of Foto.

with open('scratch/new_index.blade.php', 'w') as f:
    f.write(text)

