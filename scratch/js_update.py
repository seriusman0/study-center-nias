import re

file_path = '/var/www/study-center-nias/resources/views/admin/prajurit-jurnal/dashboard.blade.php'

with open(file_path, 'r') as f:
    content = f.read()

# Replace the hide event listener for scannerModal to reload on close if needed
# and replace onScanSuccess and openJurnalModal.
js_block_regex = re.compile(r"(\$\('#scannerModal'\)\.on\('hide\.bs\.modal'.*?)</script>", re.DOTALL)

new_js = """$('#scannerModal').on('hide.bs.modal', function () {
    if (html5Qrcode) {
        try {
            html5Qrcode.stop().then(() => {
                html5Qrcode.clear();
                html5Qrcode = null;
            }).catch(e => {
                html5Qrcode.clear();
                html5Qrcode = null;
            });
        } catch (e) {
            html5Qrcode = null;
        }
    }
    if (shouldReloadOnClose) {
        location.reload();
    }
    
    // Reset view
    document.getElementById('scannerResultContent').style.display = 'none';
    document.getElementById('scannerResultPlaceholder').style.display = 'flex';
});

let shouldReloadOnClose = false;

function onScanSuccess(decodedText) {
    if (scanning) return;
    scanning = true;

    const userId = parseInt(decodedText.trim(), 10);
    if (isNaN(userId)) {
        playSound('error');
        document.getElementById('scan-status').innerHTML =
            '<span class="text-danger">QR tidak valid</span>';
        setTimeout(() => { scanning = false; }, 2000);
        return;
    }

    document.getElementById('scan-status').innerHTML =
        '<span class="text-info"><i class="fas fa-spinner fa-spin mr-1"></i>Memuat data...</span>';

    fetch(SCAN_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ user_id: userId }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'not_found') {
            playSound('error');
            document.getElementById('scan-status').innerHTML =
                '<span class="text-danger">Prajurit tidak ditemukan atau tidak aktif.</span>';
            setTimeout(() => { scanning = false; }, 2500);
            return;
        }

        playSound('success');
        currentUser  = data.prajurit;
        currentItems = data.items;
        currentDate  = data.today;
        
        document.getElementById('scan-status').innerHTML =
            '<span class="text-success"><i class="fas fa-check mr-1"></i>Berhasil discan! Lanjut scan QR lain jika perlu.</span>';

        // Render data di kolom kanan tanpa menutup scanner
        openJurnalModal(data);
        
        // Timeout sebelum mengizinkan scan baru (mencegah double scan cepat)
        setTimeout(() => { scanning = false; }, 1500);
    })
    .catch(() => {
        playSound('error');
        document.getElementById('scan-status').innerHTML =
            '<span class="text-danger">Koneksi gagal.</span>';
        setTimeout(() => { scanning = false; }, 2000);
    });
}

function openJurnalModal(data) {
    document.getElementById('scannerResultPlaceholder').style.display = 'none';
    
    const contentDiv = document.getElementById('scannerResultContent');
    contentDiv.style.display = 'block';
    contentDiv.classList.remove('kid-bounce');
    void contentDiv.offsetWidth; // trigger reflow
    contentDiv.classList.add('kid-bounce');

    document.getElementById('jurnalModalTitleName').innerHTML = escHtml(data.prajurit.name);
        
    let avatarUrl = data.prajurit.avatar || 'https://ui-avatars.com/api/?name='+encodeURIComponent(data.prajurit.name)+'&size=150&background=FF9A9E&color=fff';
    
    document.getElementById('jurnalAvatarCol').innerHTML = `
        <img src="${avatarUrl}" class="rounded-circle shadow-sm" style="width: 80px; height: 80px; object-fit: cover; border: 3px solid #FF9A9E;" alt="Foto Profil">
    `;

    let kelasHtml = data.prajurit.kelas ? `Kelas: <strong>${escHtml(data.prajurit.kelas)}</strong> <span class="mx-2">|</span>` : `<span class="text-black-50">Kelas: Belum diatur</span> <span class="mx-2">|</span>`;
    document.getElementById('jurnalPrajuritInfo').innerHTML =
        `${kelasHtml} Tanggal: <strong>${data.today_formatted || data.today}</strong>`;

    let scoresHtml = '';
    if (data.prajurit.scores) {
        scoresHtml = `
            <div class="col-4">
                <div class="p-2 border rounded shadow-sm bg-white">
                    <div class="small text-muted font-weight-bold">MAB</div>
                    <div class="h4 mb-0 text-primary">${data.prajurit.scores.mab || 0}</div>
                </div>
            </div>
            <div class="col-4">
                <div class="p-2 border rounded shadow-sm bg-white">
                    <div class="small text-muted font-weight-bold">MAS</div>
                    <div class="h4 mb-0 text-success">${data.prajurit.scores.mas || 0}</div>
                </div>
            </div>
            <div class="col-4">
                <div class="p-2 border rounded shadow-sm bg-white">
                    <div class="small text-muted font-weight-bold">Hafalan</div>
                    <div class="h4 mb-0 text-warning">${data.prajurit.scores.hafalan || 0}</div>
                </div>
            </div>
        `;
    }
    document.getElementById('jurnalScores').innerHTML = scoresHtml;

    let html = '';
    data.items.forEach(item => {
        const checked = data.checkedIds.includes(item.id);
        if (item.response_type === 'boolean') {
            html += `
            <label class="kid-check-item d-flex align-items-center mb-3" for="item_${item.id}">
                <input type="checkbox" class="jurnal-check"
                    id="item_${item.id}" data-item-id="${item.id}" data-type="boolean"
                    ${checked ? 'checked' : ''}>
                <span class="kid-check-label">${escHtml(item.label)}</span>
            </label>`;
        } else {
            const val = data.numberValues[item.id] ?? '';
            const displayVal = (val === 0 || val === '0') ? '' : val;
            html += `
            <div class="kid-check-item d-flex align-items-center justify-content-between mb-3">
                <label for="item_num_${item.id}" class="kid-check-label m-0">${escHtml(item.label)}</label>
                <input type="number" min="0" class="kid-number-input jurnal-number m-0 text-center"
                    id="item_num_${item.id}" data-item-id="${item.id}" data-type="number"
                    value="${displayVal}" placeholder="" style="width: 80px; padding: 5px 10px;">
            </div>`;
        }
    });

    document.getElementById('jurnalItemsList').innerHTML = html;
    document.getElementById('jurnalSaveStatus').innerHTML = '';
    
    // Attach autosave listeners
    document.querySelectorAll('.jurnal-check').forEach(el => {
        el.addEventListener('change', () => autoSaveJurnal());
    });
    
    let debounceTimer;
    document.querySelectorAll('.jurnal-number').forEach(el => {
        el.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => autoSaveJurnal(), 500);
        });
    });
}

function autoSaveJurnal() {
    const checks = [];

    document.querySelectorAll('.jurnal-check').forEach(el => {
        checks.push({
            item_id: parseInt(el.dataset.itemId),
            checked: el.checked,
            value: null,
        });
    });

    document.querySelectorAll('.jurnal-number').forEach(el => {
        checks.push({
            item_id: parseInt(el.dataset.itemId),
            checked: (el.value > 0),
            value: parseInt(el.value) || 0,
        });
    });

    document.getElementById('jurnalSaveStatus').innerHTML =
        '<span class="text-info"><i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...</span>';

    fetch(SAVE_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            user_id: currentUser.id,
            tanggal: currentDate,
            checks: checks,
        }),
    })
    .then(r => {
        if (!r.ok) throw new Error('Network error');
        return r.json();
    })
    .then(data => {
        if (data.status === 'saved') {
            document.getElementById('jurnalSaveStatus').innerHTML =
                '<span class="text-success"><i class="fas fa-check mr-1"></i>Tersimpan otomatis</span>';
            shouldReloadOnClose = true;
        } else {
            document.getElementById('jurnalSaveStatus').innerHTML =
                '<span class="text-danger">Respon tidak valid</span>';
        }
    })
    .catch(() => {
        document.getElementById('jurnalSaveStatus').innerHTML =
            '<span class="text-danger">Gagal menyimpan otomatis.</span>';
    });
}

function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
</script>"""

content = js_block_regex.sub(new_js, content)

with open(file_path, 'w') as f:
    f.write(content)

print("JS replaced")
