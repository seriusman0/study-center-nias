<?php
$file = 'resources/views/home.blade.php';
$content = file_get_contents($file);

$newUI = <<<JS
function openPublicJurnalModal(data) {
    document.getElementById('scannerCameraSection').classList.add('hidden');
    document.getElementById('scannerResultContainer').classList.remove('hidden');

    document.getElementById('jurnalModalTitleName').innerHTML = escHtml(data.user.name);
    let avatarUrl = data.user.avatar || 'https://ui-avatars.com/api/?name='+encodeURIComponent(data.user.name)+'&size=150&background=0F766E&color=fff';
    
    document.getElementById('jurnalAvatarCol').innerHTML = `<img src="\${avatarUrl}" class="rounded-full shadow-sm object-cover border-4 border-sc-teal-200" style="width: 70px; height: 70px;" alt="Foto Profil">`;

    let kelasHtml = data.user.kelas ? `Kelas/Info: <strong>\${escHtml(data.user.kelas)}</strong> <span class="mx-2 text-gray-300">|</span>` : ``;
    document.getElementById('jurnalPrajuritInfo').innerHTML = `\${kelasHtml} Tanggal: <strong>\${data.today_formatted || data.today}</strong>`;

    const container = document.getElementById('jurnalChecklistContainer');
    container.innerHTML = '';

    let checkedIds = data.checkedIds || [];
    let numberValues = data.numberValues || {};

    // Group items by kategori
    let grouped = {};
    data.items.forEach(item => {
        let cat = item.kategori || 'lain-lain';
        if (!grouped[cat]) grouped[cat] = [];
        grouped[cat].push(item);
    });

    const categoryLabels = {
        'pembacaan': 'Pembacaan Firman',
        'sidang': 'Sidang Gereja',
        'rohani': 'Kegiatan Rohani',
        'prajurit': 'Prajurit',
        'lain-lain': 'Lain-lain'
    };

    Object.keys(grouped).forEach(kKey => {
        let catLabel = categoryLabels[kKey] || kKey;
        
        let section = document.createElement('div');
        section.className = 'mb-4';
        
        let header = document.createElement('h3');
        header.className = 'text-xs font-bold text-sc-teal-700 uppercase tracking-wider mb-2';
        header.innerText = catLabel;
        section.appendChild(header);

        let spaceY = document.createElement('div');
        spaceY.className = 'space-y-2';

        grouped[kKey].forEach(item => {
            let isChecked = checkedIds.includes(item.id);
            let itemDiv = document.createElement('div');
            
            if (item.tipe === 'number') {
                let curVal = numberValues[item.id] || '';
                itemDiv.className = 'flex items-center justify-between p-3 rounded-lg border border-gray-200 bg-white hover:bg-sc-teal-50 transition';
                itemDiv.innerHTML = `
                    <span class="text-sm font-semibold text-gray-800">\${escHtml(item.nama)}</span>
                    <div class="flex items-center gap-2">
                        <input type="number" id="val_\${item.id}" value="\${curVal}" class="form-input w-20 text-sm border-gray-300 rounded-lg shadow-sm focus:ring-sc-teal-500 focus:border-sc-teal-500 py-1" placeholder="Nilai">
                        <button type="button" onclick="saveJurnalPublic(\${item.id}, 'number')" class="px-3 py-1 bg-sc-teal-600 text-white text-xs font-bold rounded-lg hover:bg-sc-teal-700 shadow-sm">Simpan</button>
                    </div>
                `;
            } else {
                if (item.nama === 'Membaca Alkitab Bersama-sama' || item.nama === 'Baca Alkitab') {
                    let plChecked = data.pl_checked ? 'checked' : '';
                    let pbChecked = data.pb_checked ? 'checked' : '';
                    
                    itemDiv.className = 'p-3 rounded-lg border border-gray-200 bg-white';
                    itemDiv.innerHTML = `
                        <div class="text-sm font-semibold text-gray-800 mb-2">\${escHtml(item.nama)}</div>
                        <div class="grid sm:grid-cols-2 gap-2">
                            <label class="flex items-center gap-2 p-2 rounded-lg border border-gray-100 hover:bg-sc-teal-50 cursor-pointer transition">
                                <input type="checkbox" id="pl_checkbox_\${item.id}" \${plChecked} onchange="saveJurnalPublic(\${item.id}, 'mab_pl', this.checked)" class="w-5 h-5 accent-sc-teal-600 rounded border-gray-300">
                                <span class="text-sm font-semibold text-gray-700">Perjanjian Lama</span>
                            </label>
                            <label class="flex items-center gap-2 p-2 rounded-lg border border-gray-100 hover:bg-sc-teal-50 cursor-pointer transition">
                                <input type="checkbox" id="pb_checkbox_\${item.id}" \${pbChecked} onchange="saveJurnalPublic(\${item.id}, 'mab_pb', this.checked)" class="w-5 h-5 accent-sc-teal-600 rounded border-gray-300">
                                <span class="text-sm font-semibold text-gray-700">Perjanjian Baru</span>
                            </label>
                        </div>
                    `;
                } else {
                    itemDiv = document.createElement('label');
                    itemDiv.className = 'flex items-center gap-3 p-3 rounded-lg border border-gray-200 bg-white hover:bg-sc-teal-50 cursor-pointer transition';
                    let checkedAttr = isChecked ? 'checked' : '';
                    itemDiv.innerHTML = `
                        <input type="checkbox" class="w-5 h-5 accent-sc-teal-600 rounded border-gray-300 focus:ring-sc-teal-500" \${checkedAttr} onchange="saveJurnalPublic(\${item.id}, 'boolean', this.checked)">
                        <span class="text-sm font-semibold text-gray-800">\${escHtml(item.nama)}</span>
                    `;
                }
            }
            
            spaceY.appendChild(itemDiv);
        });

        section.appendChild(spaceY);
        container.appendChild(section);
    });
}
JS;

$content = preg_replace('/function openPublicJurnalModal\(data\) \{.*?\}\n\nfunction saveJurnalPublic/s', $newUI . "\n\nfunction saveJurnalPublic", $content);
file_put_contents($file, $content);
echo "Patched openPublicJurnalModal UI.\n";
