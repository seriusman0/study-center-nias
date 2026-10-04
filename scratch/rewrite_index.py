import re

with open('scratch/index.blade.php', 'r') as f:
    text = f.read()

# I want to replace the sections:
# 1. Pembacaan Alkitab
# 2. Hafal Ayat
# 3. Jadwal Kehidupan
# 4. Foto Saat Belajar

# With:
# 1. Allah (Contains Pembacaan Alkitab, Hafal Ayat, Kerohanian)
# 2. Pendidikan
# 3. Karakter (Contains Karakter, Foto Saat Belajar)

# Let's extract the internals of each block.
# Pembacaan Alkitab internal:
plpb_match = re.search(r'(?s)\{\{-- Pembacaan Alkitab --\}\}.*?</h2>(.*?)</div>\n\n    \{\{-- Hafal Ayat --\}\}', text)
if not plpb_match:
    print("Cannot find plpb")

# Hafal Ayat internal:
hafal_match = re.search(r'(?s)\{\{-- Hafal Ayat --\}\}\n    <div.*?x-data="hafalAyat.*?</h2>(.*?)</div>\n\n    \{\{-- Jadwal Kehidupan --\}\}', text)
if not hafal_match:
    print("Cannot find hafal")

# We will just write a new template section and inject the dynamic logic.
