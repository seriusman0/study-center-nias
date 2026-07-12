@extends('layouts.admin')

@section('page-title', 'Edit Template: ' . $template->name)

@push('head')
{{-- CodeMirror --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/dracula.min.css">
<style>
.editor-wrap { display: flex; gap: 16px; }
.editor-col  { flex: 1; min-width: 0; }
.preview-col { flex: 1; min-width: 0; }
.CodeMirror  { height: 500px; font-size: 13px; border-radius: 0 0 4px 4px; }
.preview-frame {
    width: 100%; height: 500px;
    border: 1px solid #dee2e6; border-radius: 4px;
    background: #fff;
    overflow: auto;
    padding: 16px;
}
.placeholder-ref { font-size: 12px; }
.placeholder-ref code { background: #f1f5f9; padding: 1px 5px; border-radius: 3px; font-size: 11px; }
@media (max-width: 991px) {
    .editor-wrap { flex-direction: column; }
}
</style>
@endpush

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.nametag-templates.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left mr-1"></i> Kembali
    </a>
    @if($template->is_system)
    <span class="badge badge-info ml-2">Template Sistem</span>
    @endif
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    {{ session('success') }}
</div>
@endif

<form method="POST" action="{{ route('admin.nametag-templates.update', $template) }}" id="editForm">
    @csrf @method('PUT')

<div class="card mb-3">
    <div class="card-body">
        <div class="form-row">
            <div class="form-group col-md-4">
                <label>Nama Template</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $template->name) }}" required>
            </div>
            <div class="form-group col-md-4">
                <label>Deskripsi</label>
                <input type="text" name="description" class="form-control" value="{{ old('description', $template->description) }}">
            </div>
            <div class="form-group col-md-2">
                <label>Lebar (cm)</label>
                <input type="number" name="width" id="inputWidth" class="form-control" step="0.1" min="3" max="30"
                       value="{{ old('width', $template->width) }}" required>
            </div>
            <div class="form-group col-md-2">
                <label>Tinggi (cm)</label>
                <input type="number" name="height" id="inputHeight" class="form-control" step="0.1" min="3" max="30"
                       value="{{ old('height', $template->height) }}" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-3 mb-0">
                <label>Orientasi</label>
                <select name="orientation" class="form-control">
                    <option value="portrait"  {{ $template->orientation === 'portrait'  ? 'selected' : '' }}>Portrait</option>
                    <option value="landscape" {{ $template->orientation === 'landscape' ? 'selected' : '' }}>Landscape</option>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-code mr-1"></i> HTML Template</span>
        <div>
            <button type="button" class="btn btn-sm btn-outline-info" id="btnPreview">
                <i class="fas fa-eye mr-1"></i> Preview
            </button>
            <button type="submit" class="btn btn-sm btn-success ml-2">
                <i class="fas fa-save mr-1"></i> Simpan
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="editor-wrap p-3">
            <div class="editor-col">
                <div class="bg-dark text-white px-3 py-1 rounded-top d-flex justify-content-between align-items-center" style="font-size:12px">
                    <span>HTML Editor</span>
                    <span class="text-muted">Ctrl+S = Simpan</span>
                </div>
                <textarea name="html_content" id="htmlEditor">{{ old('html_content', $template->html_content) }}</textarea>
            </div>
            <div class="preview-col">
                <div class="bg-secondary text-white px-3 py-1 rounded-top" style="font-size:12px">
                    Preview (data contoh)
                </div>
                <div class="preview-frame" id="previewFrame">
                    <p class="text-muted text-center" style="margin-top:180px">Klik <strong>Preview</strong> untuk melihat hasil</p>
                </div>
            </div>
        </div>

        <div class="px-3 pb-3">
            <div class="placeholder-ref">
                <strong>Placeholder tersedia:</strong>
                <code>{name}</code> Nama siswa &nbsp;
                <code>{kelas}</code> Kelas &nbsp;
                <code>{sekolah}</code> Sekolah &nbsp;
                <code>{cabang}</code> Cabang &nbsp;
                <code>{photo_html}</code> HTML foto (img atau placeholder) &nbsp;
                <code>{photo_url}</code> URL foto mentah &nbsp;
                <code>{qr_html}</code> QR code ID siswa (SVG inline, siap cetak) &nbsp;
                <code>{logo_url}</code> URL logo Study Center &nbsp;
                <code>{corner_tr_url}</code> URL gambar sudut kanan atas &nbsp;
                <code>{corner_bl_url}</code> URL gambar sudut kiri bawah &nbsp;
                <code>{width}</code> Lebar dalam cm &nbsp;
                <code>{height}</code> Tinggi dalam cm
            </div>
        </div>
    </div>
</div>

</form>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/xml/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/css/css.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/htmlmixed/htmlmixed.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/closetag.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/matchbrackets.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/fold/foldcode.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/fold/foldgutter.min.js"></script>
<script>
(function() {
    var editor = CodeMirror.fromTextArea(document.getElementById('htmlEditor'), {
        mode: 'htmlmixed',
        theme: 'dracula',
        lineNumbers: true,
        lineWrapping: true,
        autoCloseTags: true,
        matchBrackets: true,
        indentUnit: 4,
        tabSize: 4,
        extraKeys: {
            'Ctrl-S': function(cm) { document.getElementById('editForm').submit(); }
        }
    });

    var previewUrl = '/admin/nametag-templates/{{ $template->id }}/preview';
    var previewFrame = document.getElementById('previewFrame');
    var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    document.getElementById('btnPreview').addEventListener('click', function() {
        var html = editor.getValue();
        var width  = document.getElementById('inputWidth').value;
        var height = document.getElementById('inputHeight').value;

        fetch(previewUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
            body: JSON.stringify({ html_content: html, width: width, height: height })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            // Wrap in preview styles matching generate.blade.php
            previewFrame.innerHTML = '<style>'
                + '.nametag{position:relative;background:#f5efe2;overflow:hidden;border:1px dashed #cbd5e1;border-radius:2px;padding:4mm 5mm;display:flex;flex-direction:column;page-break-inside:avoid;}'
                + '.corner-tl{position:absolute;top:0;left:0;width:14mm;height:14mm;overflow:hidden;}'
                + '.corner-tl::before{content:"";position:absolute;top:-2mm;left:6mm;width:0;height:0;border-left:3mm solid transparent;border-right:3mm solid transparent;border-top:8mm solid #1e6b3a;}'
                + '.corner-tr{position:absolute;top:0;right:0;width:18mm;height:14mm;}'
                + '.corner-bl{position:absolute;bottom:0;left:0;width:16mm;height:14mm;}'
                + '.corner-tr svg,.corner-bl svg{width:100%;height:100%;display:block;}'
                + '.head{display:flex;align-items:center;gap:2mm;position:relative;z-index:2;}'
                + '.head img.logo{width:11mm;height:11mm;object-fit:contain;}'
                + '.head .titles{line-height:1.1;}'
                + '.head .title-main{font-size:11pt;font-weight:800;color:#5d3a14;letter-spacing:.5px;}'
                + '.head .title-sub{font-size:6pt;color:#1e6b3a;font-weight:700;letter-spacing:.3px;}'
                + '.head .title-tag{font-size:5.5pt;color:#1e6b3a;font-style:italic;}'
                + '.divider{height:1pt;background:#1e6b3a;margin:2mm 0 3mm;position:relative;z-index:1;}'
                + '.body{flex:1;display:flex;flex-direction:column;justify-content:center;gap:1.5mm;padding-left:2mm;position:relative;z-index:2;}'
                + '.row-line{display:flex;align-items:baseline;gap:2mm;font-size:9pt;}'
                + '.row-line .label{font-weight:700;color:#2d2d2d;min-width:18mm;}'
                + '.row-line .colon{color:#2d2d2d;}'
                + '.row-line .value{color:#2d2d2d;font-weight:500;flex:1;}'
                + '.nametag-photo{padding:3mm 4mm;}'
                + '.photo-body{flex:1;display:flex;gap:3mm;align-items:flex-start;position:relative;z-index:2;}'
                + '.photo-box{flex-shrink:0;width:20mm;height:26.7mm;overflow:hidden;border:1px solid #cbd5e1;background:#fff;}'
                + '.photo-placeholder{width:100%;height:100%;display:flex;align-items:center;justify-content:center;border:1.5px dashed #94a3b8;color:#94a3b8;font-size:6pt;text-align:center;line-height:1.3;}'
                + '.info-col{flex:1;display:flex;flex-direction:column;justify-content:center;gap:1.5mm;}'
                + '.info-name{font-size:9pt;font-weight:700;color:#1e3a5f;line-height:1.2;margin-bottom:1mm;}'
                + '.info-row{display:flex;gap:1mm;font-size:7.5pt;color:#2d2d2d;}'
                + '.info-label{font-weight:600;min-width:14mm;}'
                + '.info-sep{margin-right:1mm;}'
                + '.info-val{flex:1;}'
                + '.nametag-landscape{flex-direction:row!important;padding:3mm!important;gap:3mm;}'
                + '.ls-photo{flex-shrink:0;width:18mm;height:24mm;overflow:hidden;border:1px solid #cbd5e1;background:#fff;align-self:center;}'
                + '.photo-placeholder-ls{width:100%;height:100%;display:flex;align-items:center;justify-content:center;border:1.5px dashed #94a3b8;color:#94a3b8;font-size:5.5pt;text-align:center;line-height:1.3;}'
                + '.ls-content{flex:1;display:flex;flex-direction:column;justify-content:center;min-width:0;}'
                + '.ls-header{display:flex;align-items:center;gap:2mm;}'
                + '.logo-sm{width:9mm;height:9mm;object-fit:contain;}'
                + '.ls-brand{line-height:1.1;}'
                + '.ls-title{font-size:9pt;font-weight:800;color:#5d3a14;}'
                + '.ls-sub{font-size:5pt;color:#1e6b3a;font-weight:700;}'
                + '.ls-divider{height:1pt;background:#1e6b3a;margin:1.5mm 0;}'
                + '.ls-name{font-size:9pt;font-weight:700;color:#1e3a5f;line-height:1.2;margin-bottom:1.5mm;}'
                + '.ls-meta{display:flex;flex-wrap:wrap;gap:1mm;}'
                + '.ls-badge{font-size:6pt;background:#e8f4f0;color:#1e6b3a;padding:.5mm 2mm;border-radius:2px;font-weight:600;}'
                + '.ls-badge-cabang{background:#fef3e2;color:#5d3a14;}'
                + '</style>'
                + data.html;
        })
        .catch(function(e) {
            previewFrame.innerHTML = '<p class="text-danger p-3">Gagal memuat preview: ' + e.message + '</p>';
        });
    });
})();
</script>
@endpush
@endsection
