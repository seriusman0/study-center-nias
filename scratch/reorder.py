import re

with open('/var/www/study-center-nias/resources/views/admin/prajurit-jurnal/dashboard.blade.php', 'r') as f:
    content = f.read()

# Splitting logic
# 1. Top Section (Top: Today's bible info to before User table)
top_section_match = re.search(r"(\{\{-- Top: Today's bible info \+ form window --\}\}.*?)\{\{-- User table --\}\}", content, re.DOTALL)
top_section = top_section_match.group(1).strip()

# 2. User table
user_table_match = re.search(r"(\{\{-- User table --\}\}.*?</div>\n)\{\{-- ═══════ MODAL: QR SCANNER ═══════ --\}\}", content, re.DOTALL)
user_table = user_table_match.group(1).strip()

# 3. Extract scanner button from user_table and remove it
scanner_btn_match = re.search(r"(\s*<button type=\"button\" class=\"btn btn-sm btn-warning ml-2\" id=\"btnOpenScanner\">\s*<i class=\"fas fa-qrcode mr-1\"></i> Scan QR Prajurit\s*</button>)", user_table)
if scanner_btn_match:
    scanner_btn = scanner_btn_match.group(1)
    user_table = user_table.replace(scanner_btn, "")

# Create new layout
new_btn = """
{{-- SCAN QR PRAJURIT BUTTON --}}
<div class="mb-4 text-center">
    <button type="button" class="btn btn-warning btn-lg px-5 py-3 font-weight-bold" id="btnOpenScanner" style="font-size: 1.5rem; border-radius: 15px; box-shadow: 0 5px 15px rgba(255,193,7,0.4);">
        <i class="fas fa-qrcode mr-2"></i> SCAN QR PRAJURIT
    </button>
</div>
"""

new_content = content[:top_section_match.start()] + new_btn + "\n" + user_table + "\n\n<hr class=\"my-5\">\n\n" + top_section + "\n\n{{-- ═══════ MODAL: QR SCANNER ═══════ --}}\n" + content[user_table_match.end()-len("{{-- ═══════ MODAL: QR SCANNER ═══════ --}}"):]

with open('/var/www/study-center-nias/resources/views/admin/prajurit-jurnal/dashboard.blade.php', 'w') as f:
    f.write(new_content)

print("Done")
