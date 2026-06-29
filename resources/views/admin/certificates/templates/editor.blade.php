@extends('layouts.admin')

@section('page-title', $template ? 'Edit Template Sertifikat' : 'Buat Template Sertifikat')

@section('content')
<form method="POST"
      action="{{ $template ? route('admin.certificates.templates.update', $template->id) : route('admin.certificates.templates.store') }}"
      enctype="multipart/form-data">
    @csrf
    @if($template) @method('PUT') @endif

    <div class="row">
        {{-- Editor --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Editor HTML/CSS</h6>
                    <small class="text-muted">Tag &lt;script&gt; dan event handler akan dihapus otomatis saat disimpan</small>
                </div>
                <div class="card-body p-2">
                    <textarea name="html_content" id="htmlEditor" class="form-control"
                              style="font-family: monospace; font-size: 13px; height: 620px; resize: vertical;"
                              placeholder="Tulis HTML/CSS sertifikat di sini. Gunakan placeholder seperti @{{nama_peserta}}."
                              required>{{ old('html_content', $template->html_content ?? '') }}</textarea>
                    @error('html_content')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-md-4">
            {{-- Meta --}}
            <div class="card mb-3">
                <div class="card-header"><h6 class="mb-0">Info Template</h6></div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Nama Template <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control form-control-sm"
                               value="{{ old('nama', $template->nama ?? '') }}" required maxlength="150">
                        @error('nama')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi" class="form-control form-control-sm" rows="2"
                                  maxlength="500">{{ old('deskripsi', $template->deskripsi ?? '') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Orientasi</label>
                        <select name="orientation" id="orientationSelect" class="form-control form-control-sm">
                            <option value="portrait"  {{ old('orientation', $template->orientation ?? 'portrait')  === 'portrait'  ? 'selected' : '' }}>Portrait (210×297 mm)</option>
                            <option value="landscape" {{ old('orientation', $template->orientation ?? 'portrait') === 'landscape' ? 'selected' : '' }}>Landscape (297×210 mm)</option>
                        </select>
                    </div>
                    <input type="hidden" name="paper_size" value="a4">
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active"
                                   value="1" {{ old('is_active', $template->is_active ?? true) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">Template Aktif</label>
                        </div>
                    </div>

                    {{-- Logo Upload --}}
                    <div class="form-group mb-0">
                        <label>Logo Template</label>
                        <div class="mb-2">
                            @if($template && $template->logo_path)
                                <img src="{{ asset('storage/' . $template->logo_path) }}"
                                     height="48" class="border rounded p-1 d-block mb-1" id="logoPreviewImg">
                                <small class="text-muted d-block">Logo saat ini</small>
                            @else
                                <img src="{{ asset('assets/img/logo.png') }}"
                                     height="48" class="border rounded p-1 d-block mb-1" id="logoPreviewImg">
                                <small class="text-muted d-block">Logo default</small>
                            @endif
                        </div>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="logoInput" name="logo"
                                   accept="image/png,image/jpeg,image/svg+xml">
                            <label class="custom-file-label" for="logoInput">Pilih file logo</label>
                        </div>
                        <small class="text-muted">PNG/JPG/SVG, maks 2MB. Biarkan kosong untuk pakai logo default.</small>
                        @error('logo')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                </div>
            </div>

            {{-- Placeholder Reference --}}
            <div class="card mb-3">
                <div class="card-header"><h6 class="mb-0">Placeholder Tersedia</h6></div>
                <div class="card-body p-2">
                    <p class="text-muted small mb-2">Klik untuk menyalin ke clipboard.</p>
                    @php
                    $placeholders = [
                        ['{{nama_peserta}}',    'Nama lengkap siswa'],
                        ['{{nama_kursus}}',     'Nama kursus/kelas'],
                        ['{{tanggal_lulus}}',   'Tanggal kelulusan (teks Indonesia)'],
                        ['{{nomor_sertifikat}}','Nomor unik sertifikat'],
                        ['{{cabang}}',          'Nama cabang siswa'],
                        ['{{tahun}}',           'Tahun penerbitan'],
                        ['{{logo}}',            'Logo template (base64, pakai di src="{{logo}}")'],
                        ['{{foto_peserta}}',    'Foto siswa 3:4 (base64, kosong jika tidak ada foto)'],
                    ];
                    @endphp
                    @foreach($placeholders as [$ph, $desc])
                    <div class="d-flex align-items-start mb-1">
                        <button type="button" class="btn btn-xs btn-outline-secondary mr-2 copy-btn flex-shrink-0"
                                data-value="{{ $ph }}" title="Salin {{ $ph }}">
                            <i class="fas fa-copy"></i>
                        </button>
                        <div>
                            <code style="font-size:12px">{{ $ph }}</code>
                            <small class="d-block text-muted" style="font-size:11px">{{ $desc }}</small>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Tips --}}
            <div class="card mb-3">
                <div class="card-header"><h6 class="mb-0">Tips PDF</h6></div>
                <div class="card-body p-2">
                    <ul class="small text-muted mb-0 pl-3">
                        <li>Canvas A4 portrait: <strong>210mm × 297mm</strong>; landscape: <strong>297mm × 210mm</strong>.</li>
                        <li>Gunakan <code>position:absolute</code> dalam container <code>position:relative; width:210mm; height:297mm</code>.</li>
                        <li>Set <code>margin:0</code> pada root div untuk full-bleed.</li>
                        <li>Font PDF-safe: <strong>DejaVu Sans</strong>.</li>
                        <li>Logo: <code>&lt;img src=&quot;@{{logo}}&quot; style=&quot;height:60px&quot;&gt;</code></li>
                        <li>Foto siswa (3:4): <code>&lt;img src=&quot;@{{foto_peserta}}&quot; style=&quot;width:75px;height:100px;object-fit:cover&quot;&gt;</code> — kosong jika tidak ada foto.</li>
                        <li>Background image: embed sebagai <code>data:image/png;base64,...</code> di CSS.</li>
                    </ul>
                </div>
            </div>

            {{-- Actions --}}
            <div class="d-flex flex-wrap" style="gap:8px">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-save"></i> Simpan Template
                </button>
                <button type="button" class="btn btn-info btn-sm" onclick="submitPreview()">
                    <i class="fas fa-eye"></i> Preview PDF
                </button>
                <a href="{{ route('admin.certificates.templates.index') }}" class="btn btn-secondary btn-sm">Batal</a>
            </div>
        </div>
    </div>
</form>

{{-- Hidden preview form (opens in new tab) --}}
<form method="POST" action="{{ route('admin.certificates.templates.preview') }}"
      target="_blank" id="previewForm" style="display:none">
    @csrf
    <input type="hidden" name="html_content" id="previewHtmlInput">
    <input type="hidden" name="orientation" id="previewOrientationInput">
    <input type="hidden" name="logo_path" value="{{ $template->logo_path ?? '' }}">
</form>
@endsection

@section('scripts')
<script>
// Copy placeholder to clipboard
document.querySelectorAll('.copy-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        navigator.clipboard.writeText(this.dataset.value);
        var icon = this.querySelector('i');
        icon.className = 'fas fa-check';
        setTimeout(function() { icon.className = 'fas fa-copy'; }, 1200);
    });
});

// Logo file input — preview image inline
document.getElementById('logoInput').addEventListener('change', function() {
    var label = this.nextElementSibling;
    label.textContent = this.files[0] ? this.files[0].name : 'Pilih file logo';

    if (this.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('logoPreviewImg').src = e.target.result;
        };
        reader.readAsDataURL(this.files[0]);
    }
});

// Preview PDF in new tab
function submitPreview() {
    document.getElementById('previewHtmlInput').value =
        document.getElementById('htmlEditor').value;
    document.getElementById('previewOrientationInput').value =
        document.getElementById('orientationSelect').value;
    document.getElementById('previewForm').submit();
}
</script>
@endsection
