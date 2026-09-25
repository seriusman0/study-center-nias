<?php
$file = 'resources/views/admin/prajurit-jurnal/dashboard.blade.php';
$content = file_get_contents($file);

$target = <<<HTML
            for(let j = 1; j < headers.length; j++) {
                const label = headers[j];
                const cellStr = String(row[j]);
                const checked = cellStr.startsWith('Y');
                if(checked) checkedCount++;
                
                if (checked) {
                    let valStr = '';
                    if (cellStr.includes(':')) {
                        valStr = ' <span class="badge badge-light ml-1 text-dark">' + cellStr.split(':')[1] + '</span>';
                    }
                    badgesHtml += `<span class="kid-badge kid-badge-yes"><i class="fas fa-check mr-1"></i> \${escHtml(label)}\${valStr}</span>`;
                } else {
                    badgesHtml += `<span class="kid-badge kid-badge-no"><i class="fas fa-times mr-1"></i> \${escHtml(label)}</span>`;
                }
            }
HTML;

$replacement = <<<HTML
            const items = matrix.items || [];
            for(let j = 1; j < headers.length; j++) {
                const label = headers[j];
                const cellStr = String(row[j]);
                const checked = cellStr.startsWith('Y');
                if(checked) checkedCount++;
                
                const item = items[j-1];
                if (!item) continue;
                
                if (item.response_type === 'number') {
                    let valStr = '';
                    if (cellStr.includes(':')) {
                        valStr = cellStr.split(':')[1];
                    }
                    badgesHtml += `
                        <div class="d-inline-flex align-items-center mb-1 mr-2 p-1" style="background: #f8f9fa; border-radius: 8px; border: 1px solid #dee2e6;">
                            <span class="mr-2" style="font-size:0.85rem; font-weight:600; color:#333;">\${escHtml(label)}</span>
                            <input type="number" min="0" class="form-control form-control-sm text-center" 
                                style="width: 60px; height: 28px; border-radius: 6px; font-size: 0.9rem; font-weight:bold; color:#0056b3;" 
                                value="\${valStr}" 
                                onchange="saveJurnalInline(\${userId}, '\${dateStr}', \${item.id}, this.value > 0, this.value)">
                        </div>
                    `;
                } else {
                    const checkState = checked ? 'checked' : '';
                    badgesHtml += `
                        <div class="custom-control custom-switch custom-control-inline mr-2 mb-1" style="background: #f8f9fa; border-radius: 8px; border: 1px solid #dee2e6; padding: 4px 10px 4px 40px;">
                            <input type="checkbox" class="custom-control-input" id="switch_\${dateStr}_\${item.id}" \${checkState}
                                onchange="saveJurnalInline(\${userId}, '\${dateStr}', \${item.id}, this.checked, null)">
                            <label class="custom-control-label" for="switch_\${dateStr}_\${item.id}" style="font-size:0.85rem; font-weight:600; color:#333; padding-top:2px; cursor:pointer;">\${escHtml(label)}</label>
                        </div>
                    `;
                }
            }
HTML;

$content = str_replace($target, $replacement, $content);

$target2 = <<<HTML
        document.getElementById('summaryTimeline').innerHTML = timelineHtml;
    })
    .catch(err => {
        document.getElementById('summaryUserInfo').innerHTML = '<div class="text-danger"><i class="fas fa-exclamation-triangle"></i> Gagal memuat data.</div>';
    });
}
</script>
HTML;

$replacement2 = <<<HTML
        document.getElementById('summaryTimeline').innerHTML = timelineHtml;
    })
    .catch(err => {
        document.getElementById('summaryUserInfo').innerHTML = '<div class="text-danger"><i class="fas fa-exclamation-triangle"></i> Gagal memuat data.</div>';
    });
}

function saveJurnalInline(userId, tanggal, itemId, checked, value) {
    const payload = {
        user_id: userId,
        tanggal: tanggal,
        checks: [
            { item_id: itemId, checked: checked ? 1 : 0, value: value || null }
        ]
    };

    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const token = csrfMeta ? csrfMeta.getAttribute('content') : '';

    fetch('/admin/jurnal-prajurit/save', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token
        },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(res => {
        if(res.status === 'saved') {
            // Success indicator (optional, maybe a small toast or visual feedback)
            // We can re-fetch or just let it stay visually correct as it is already toggled.
        }
    })
    .catch(err => {
        console.error(err);
        alert('Gagal menyimpan data jurnal. Silakan coba lagi.');
    });
}
</script>
HTML;

$content = str_replace($target2, $replacement2, $content);
file_put_contents($file, $content);
echo "Patched successfully\n";
