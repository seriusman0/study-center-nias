const { chromium } = require('playwright');

(async () => {
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage();
    
    await page.goto('http://localhost:8888');

    const dummyData = {
        status: 'found',
        user: { id: 1, name: 'Budi Santoso', kelas: 'SMA 1', avatar: '' },
        today: '2026-09-07',
        today_formatted: 'Senin, 7 September 2026',
        checkedIds: [2],
        numberValues: { 3: 50 },
        pl_checked: true,
        pb_checked: false,
        items: [
            { id: 1, label: 'Baca Alkitab', nama: 'Baca Alkitab', kategori: 'pembacaan', tipe: 'boolean', deskripsi: '' },
            { id: 2, label: 'Berdoa', nama: 'Berdoa', kategori: 'kerohanian', tipe: 'boolean', deskripsi: 'Berdoa pagi' },
            { id: 3, label: 'Push Up', nama: 'Push Up', kategori: 'prajurit', tipe: 'number', deskripsi: 'Olahraga' }
        ]
    };

    await page.evaluate((data) => {
        document.getElementById('publicScannerModal').classList.remove('hidden');
        window.openPublicJurnalModal(data);
    }, dummyData);
    
    await page.waitForTimeout(1000);
    
    const containerVisible = await page.isVisible('#scannerResultContainer');
    console.log('Result container visible:', containerVisible);
    
    const innerHTML = await page.$eval('#jurnalChecklistContainer', el => el.innerHTML);
    console.log('Checklist inner HTML length:', innerHTML.length);
    
    await browser.close();
})();
