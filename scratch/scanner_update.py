import re

file_path = '/var/www/study-center-nias/resources/views/admin/prajurit-jurnal/dashboard.blade.php'

with open(file_path, 'r') as f:
    content = f.read()

# 1. Replace scannerModal and remove jurnalModal
# find the block starting with {{-- ═══════ MODAL: QR SCANNER ═══════ --}} and ending just before @push('scripts')
modal_regex = re.compile(r'\{\{-- ═══════ MODAL: QR SCANNER ═══════ --\}\}.*?(?=@push\(\'scripts\'\))', re.DOTALL)

new_modals = """{{-- ═══════ MODAL: QR SCANNER & JURNAL ═══════ --}}
<div class="modal fade" id="scannerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content kid-modal-content">
            <div class="modal-header kid-modal-header d-flex align-items-center">
                <h5 class="modal-title kid-modal-title">
                    <i class="fas fa-qrcode mr-2"></i> Scanner & Jurnal Prajurit
                </h5>
                <button type="button" class="close" data-dismiss="modal" style="font-size: 2rem; color: #333;">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="row m-0">
                    <div class="col-md-5 p-4 bg-white border-right">
                        <div class="form-group mb-2" id="cameraSelectGroup" style="display:none;">
                            <label for="cameraSelect" class="small font-weight-bold">Pilih Kamera:</label>
                            <select id="cameraSelect" class="form-control form-control-sm"></select>
                        </div>
                        <div id="qr-reader" style="width:100%; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.1);"></div>
                        <div id="scan-status" class="mt-3 text-center text-muted font-weight-bold">Arahkan QR Code Prajurit ke kamera.</div>
                    </div>
                    <div class="col-md-7 p-4 bg-light" id="scannerResultContainer">
                        <div class="text-center text-muted d-flex flex-column align-items-center justify-content-center h-100" id="scannerResultPlaceholder" style="min-height: 400px;">
                            <i class="fas fa-qrcode fa-5x mb-3" style="color: #dee2e6;"></i>
                            <h5>Menunggu Hasil Scan...</h5>
                            <p class="small">Scan QR Prajurit untuk mulai mengisi jurnal.</p>
                        </div>
                        
                        <div id="scannerResultContent" style="display:none;">
                            <div class="d-flex align-items-center mb-4 bg-white p-3 rounded shadow-sm">
                                <div id="jurnalAvatarCol" class="mr-3"></div>
                                <div>
                                    <h4 class="font-weight-bold mb-1" id="jurnalModalTitleName" style="color: #333;"></h4>
                                    <div id="jurnalPrajuritInfo" class="text-muted small"></div>
                                </div>
                            </div>
                            
                            <div id="jurnalScores" class="row text-center mb-4"></div>
                            
                            <div id="jurnalItemsList" class="mx-auto bg-white p-4 rounded shadow-sm"></div>
                            
                            <div id="jurnalSaveStatus" class="mt-3 text-center" style="font-size:1.1rem; font-weight:bold;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .kid-modal-content { border-radius: 20px; border: none; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
    .kid-modal-header { background: linear-gradient(135deg, #FF9A9E 0%, #FECFEF 100%); color: #333; border-bottom: none; padding: 20px 25px; }
    .kid-modal-title { font-weight: 800; font-size: 1.5rem; letter-spacing: 1px; margin: 0; }
    .kid-check-item { background: #f8f9fa; border-radius: 12px; padding: 15px; border: 2px solid #e9ecef; transition: all 0.2s; cursor: pointer; }
    .kid-check-item:hover { border-color: #a3bffa; background: #f1f5f9; transform: translateY(-2px); }
    .kid-check-item input[type="checkbox"] { transform: scale(1.5); margin-right: 15px; cursor: pointer; }
    .kid-check-label { font-size: 1.1rem; font-weight: 600; color: #495057; margin: 0; cursor: pointer; user-select: none; }
    .kid-number-input { font-size: 1.2rem; border-radius: 12px; border: 2px solid #e9ecef; font-weight: bold; }
    .kid-number-input:focus { border-color: #FF9A9E; box-shadow: 0 0 0 3px rgba(255, 154, 158, 0.3); outline: none; }
    
    @keyframes kidBounceIn {
        0% { transform: scale(0.95); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }
    .kid-bounce { animation: kidBounceIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) both; }
</style>

"""

content = modal_regex.sub(new_modals, content)

with open(file_path, 'w') as f:
    f.write(content)

print("Modals replaced")
