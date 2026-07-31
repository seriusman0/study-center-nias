@extends('layouts.admin')

@section('page-title', $announcement ? 'Edit Pengumuman' : 'Buat Pengumuman')

@section('content')
<style>
.type-pills{display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:1rem}
.type-pill input{position:absolute;opacity:0;pointer-events:none}
.type-pill label{padding:.35rem 1rem;border-radius:999px;border:2px solid #dee2e6;font-size:.8rem;font-weight:600;cursor:pointer;margin:0}
.type-pill input:checked + label{border-color:currentColor}
.type-pill.info label{color:#1e40af;background:#dbeafe}
.type-pill.success label{color:#166534;background:#dcfce7}
.type-pill.warning label{color:#854d0e;background:#fef9c3}
.type-pill.danger label{color:#991b1b;background:#fee2e2}
.type-pill input:not(:checked) + label{background:#f8f9fa;color:#6c757d}
.form-label{font-size:.78rem;font-weight:600;color:#495057;margin-bottom:.25rem}
.preview-box{border-radius:.65rem;padding:.75rem 1rem;display:flex;gap:.75rem;align-items:flex-start;margin-top:1rem}
.preview-box.info{background:#dbeafe;border-left:4px solid #3b82f6}
.preview-box.success{background:#dcfce7;border-left:4px solid #22c55e}
.preview-box.warning{background:#fef9c3;border-left:4px solid #eab308}
.preview-box.danger{background:#fee2e2;border-left:4px solid #ef4444}
</style>

<div class="card" style="max-width:720px">
    <div class="card-body">
        <form method="POST"
              action="{{ $announcement ? route('admin.announcements.update', $announcement->id) : route('admin.announcements.store') }}">
            @csrf
            @if($announcement) @method('PUT') @endif

            <div class="mb-3">
                <label class="form-label">Judul <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control"
                       value="{{ old('title', $announcement?->title) }}" required maxlength="255">
                @error('title')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Isi Pengumuman <span class="text-danger">*</span></label>
                <textarea name="content" id="annContent" class="form-control" rows="3"
                          maxlength="1000" required>{{ old('content', $announcement?->content) }}</textarea>
                <div class="text-muted small mt-1"><span id="charCount">0</span>/1000 karakter</div>
                @error('content')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Tipe</label>
                <div class="type-pills">
                    @foreach(['info' => 'Info', 'success' => 'Sukses', 'warning' => 'Peringatan', 'danger' => 'Penting'] as $val => $label)
                    <div class="type-pill {{ $val }}">
                        <input type="radio" name="type" value="{{ $val }}" id="type-{{ $val }}"
                               {{ old('type', $announcement?->type ?? 'info') === $val ? 'checked' : '' }}>
                        <label for="type-{{ $val }}">{{ $label }}</label>
                    </div>
                    @endforeach
                </div>
                @error('type')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="form-row">
                <div class="form-group col-12 col-md-6">
                    <label class="form-label">Mulai Tampil</label>
                    <input type="datetime-local" name="starts_at" class="form-control"
                           value="{{ old('starts_at', $announcement?->starts_at?->format('Y-m-d\TH:i')) }}">
                    <small class="text-muted">Kosong = langsung tampil</small>
                    @error('starts_at')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="form-group col-12 col-md-6">
                    <label class="form-label">Berhenti Tampil</label>
                    <input type="datetime-local" name="ends_at" class="form-control"
                           value="{{ old('ends_at', $announcement?->ends_at?->format('Y-m-d\TH:i')) }}">
                    <small class="text-muted">Kosong = tidak ada batas waktu</small>
                    @error('ends_at')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="custom-control custom-switch mb-3">
                <input type="checkbox" name="is_active" value="1" id="isActive"
                       class="custom-control-input"
                       {{ old('is_active', $announcement?->is_active ?? true) ? 'checked' : '' }}>
                <label class="custom-control-label" for="isActive">Aktif</label>
            </div>

            {{-- Live preview --}}
            <div class="mb-3">
                <label class="form-label">Preview</label>
                <div id="previewBox" class="preview-box info">
                    <i id="previewIcon" class="fas fa-info-circle mt-1" style="font-size:1.1rem;flex-shrink:0;color:#1e40af"></i>
                    <div>
                        <strong id="previewTitle" style="font-size:.85rem">Judul pengumuman</strong>
                        <p id="previewContent" class="mb-0" style="font-size:.8rem;margin-top:.15rem">Isi pengumuman...</p>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('admin.announcements.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">
                    {{ $announcement ? 'Simpan Perubahan' : 'Buat Pengumuman' }}
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const typeConfig = {
    info:    { icon: 'fa-info-circle',         color: '#1e40af' },
    success: { icon: 'fa-check-circle',        color: '#166534' },
    warning: { icon: 'fa-exclamation-triangle', color: '#854d0e' },
    danger:  { icon: 'fa-times-circle',        color: '#991b1b' },
};

function updatePreview() {
    const type  = document.querySelector('input[name="type"]:checked')?.value ?? 'info';
    const title = document.getElementById('annContent').closest('form').querySelector('[name="title"]').value || 'Judul pengumuman';
    const content = document.getElementById('annContent').value || 'Isi pengumuman...';

    document.getElementById('previewBox').className = 'preview-box ' + type;
    const cfg = typeConfig[type];
    const icon = document.getElementById('previewIcon');
    icon.className = 'fas ' + cfg.icon + ' mt-1';
    icon.style.color = cfg.color;
    document.getElementById('previewTitle').textContent = title;
    document.getElementById('previewContent').textContent = content;
}

function updateCount() {
    const len = document.getElementById('annContent').value.length;
    document.getElementById('charCount').textContent = len;
}

document.querySelectorAll('input[name="type"]').forEach(el => el.addEventListener('change', updatePreview));
document.querySelector('[name="title"]').addEventListener('input', updatePreview);
document.getElementById('annContent').addEventListener('input', () => { updatePreview(); updateCount(); });

updatePreview();
updateCount();
</script>
@endpush
@endsection
