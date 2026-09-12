
function openSummaryModal(userId) {
    const url = `/admin/jurnal-prajurit/summary/${userId}`;
    
    document.getElementById('summaryUserInfo').innerHTML = '<div class="spinner-border text-info" role="status"></div><p class="mt-2 text-muted">Memuat data petualangan...</p>';
    document.getElementById('summaryTimeline').innerHTML = '';
    
    $('#summaryModal').modal('show');
    
    fetch(url, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(r => r.json())
    .then(data => {
        let avatarUrl = data.user.avatar || 'https://ui-avatars.com/api/?name='+encodeURIComponent(data.user.name)+'&size=150&background=8fd3f4&color=fff';
        
        let userInfoHtml = `
            <img src="${avatarUrl}" class="rounded-circle shadow" style="width:100px; height:100px; object-fit:cover; border: 4px solid #84fab0;" alt="Foto Profil">
            <h4 class="mt-3 font-weight-bold" style="color: #333;">${escHtml(data.user.name)}</h4>
            <span class="badge badge-primary" style="font-size:1rem; border-radius:15px; padding: 5px 15px;">Kelas: ${escHtml(data.user.kelas || '—')}</span>
        `;
        document.getElementById('summaryUserInfo').innerHTML = userInfoHtml;
        
        const matrix = data.matrix;
        const headers = matrix.headers; // ["Tanggal", "PL", "PB", ...]
        const rows = matrix.rows.slice().reverse(); // Reverse to show latest first
        
        let timelineHtml = '';
        
        rows.forEach((row, i) => {
            const dateStr = row[0]; // YYYY-MM-DD
            const d = new Date(dateStr);
            const formattedDate = d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
            
            let badgesHtml = '';
            let checkedCount = 0;
            let totalItems = headers.length - 1;
            
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
                    badgesHtml += `<span class="kid-badge kid-badge-yes"><i class="fas fa-check mr-1"></i> ${escHtml(label)}${valStr}</span>`;
                } else {
                    badgesHtml += `<span class="kid-badge kid-badge-no"><i class="fas fa-times mr-1"></i> ${escHtml(label)}</span>`;
                }
            }
            
            let iconStr = checkedCount === totalItems ? '🌟' : (checkedCount > 0 ? '👍' : '💤');
            
            timelineHtml += `
                <div class="kid-timeline-item">
                    <div class="kid-timeline-icon">${iconStr}</div>
                    <div class="kid-timeline-content">
                        <div class="kid-timeline-date">${formattedDate}</div>
                        <div>${badgesHtml}</div>
                    </div>
                </div>
            `;
        });
        
        if (timelineHtml === '') {
            timelineHtml = '<div class="text-center text-muted">Belum ada petualangan dicatat.</div>';
        }
        
        document.getElementById('summaryTimeline').innerHTML = timelineHtml;
    })
    .catch(err => {
        document.getElementById('summaryUserInfo').innerHTML = '<div class="text-danger"><i class="fas fa-exclamation-triangle"></i> Gagal memuat data.</div>';
    });
}
