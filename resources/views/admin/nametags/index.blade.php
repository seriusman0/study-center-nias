@extends('layouts.admin')

@section('page-title', 'Generator Name Tag')

@push('head')
<style>
.tpl-card {
    border: 2px solid #dee2e6; border-radius: 8px; padding: 14px 16px;
    cursor: pointer; transition: border-color .15s, box-shadow .15s;
    background: #fff; user-select: none;
}
.tpl-card:hover { border-color: #1e3a5f; }
.tpl-card.selected { border-color: #1e6b3a; box-shadow: 0 0 0 3px rgba(30,107,58,.15); background: #f0faf4; }
.tpl-card input[type=radio] { display: none; }
.tpl-card .tpl-name { font-weight: 700; font-size: 14px; color: #1e3a5f; margin-bottom: 4px; }
.tpl-card .tpl-desc { font-size: 12px; color: #6b7280; margin-bottom: 6px; }
.tpl-card .tpl-badges { display: flex; gap: 6px; flex-wrap: wrap; }
.tpl-badge { font-size: 11px; padding: 2px 8px; border-radius: 4px; font-weight: 600; }
.tpl-badge-size { background: #e8f4f0; color: #1e6b3a; }
.tpl-badge-orient { background: #fef3e2; color: #5d3a14; }
.tpl-check { float: right; color: #1e6b3a; display: none; }
.tpl-card.selected .tpl-check { display: inline; }
</style>
@endpush

@section('content')
<form method="POST" action="{{ route('admin.nametags.generate') }}" target="_blank">
    @csrf

    {{-- Hidden inputs diisi JS saat template dipilih --}}
    <input type="hidden" name="template"   id="inputTemplate"  value="{{ $templates->first()?->slug }}">
    <input type="hidden" name="width_cm"   id="inputWidth"     value="{{ $templates->first()?->width }}">
    <input type="hidden" name="height_cm"  id="inputHeight"    value="{{ $templates->first()?->height }}">

    <div class="card mb-3">
        <div class="card-header"><h6 class="mb-0">Pilih Template</h6></div>
        <div class="card-body">
            <div class="row">
                @foreach($templates as $tpl)
                <div class="col-md-4 mb-3">
                    <label class="tpl-card w-100 {{ $loop->first ? 'selected' : '' }}"
                           data-slug="{{ $tpl->slug }}"
                           data-width="{{ $tpl->width }}"
                           data-height="{{ $tpl->height }}">
                        <input type="radio" name="_tpl_radio" value="{{ $tpl->slug }}" {{ $loop->first ? 'checked' : '' }}>
                        <i class="fas fa-check-circle tpl-check"></i>
                        <div class="tpl-name">
                            {{ $tpl->name }}
                            @if(!$tpl->is_system)
                                <span class="badge badge-sm badge-secondary" style="font-size:10px;font-weight:500">Custom</span>
                            @endif
                        </div>
                        <div class="tpl-desc">{{ $tpl->description }}</div>
                        <div class="tpl-badges">
                            <span class="tpl-badge tpl-badge-size">{{ $tpl->width }} × {{ $tpl->height }} cm</span>
                            <span class="tpl-badge tpl-badge-orient">{{ ucfirst($tpl->orientation) }}</span>
                        </div>
                    </label>
                </div>
                @endforeach
                <div class="col-md-4 mb-3 d-flex align-items-center">
                    <a href="{{ route('admin.nametag-templates.index') }}" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="fas fa-edit mr-1"></i> Kelola / Edit Template
                    </a>
                </div>
            </div>

            <div class="form-row align-items-center mt-2">
                <div class="form-group col-md-6 mb-0">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" name="auto_print" value="1" id="autoPrint"
                               class="custom-control-input" checked>
                        <label class="custom-control-label" for="autoPrint">Buka dialog cetak otomatis</label>
                    </div>
                </div>
                <div class="form-group col-md-6 mb-0 text-right">
                    <button type="submit" class="btn btn-primary" id="genBtn" disabled>
                        <i class="fas fa-print mr-1"></i> Generate Name Tag
                        (<span id="selCount">0</span>)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <form method="GET" action="{{ route('admin.nametags') }}" class="form-inline" onsubmit="event.stopPropagation();">
                <input type="text" name="q" value="{{ request('q') }}"
                       placeholder="Cari nama/sekolah/kelas"
                       class="form-control form-control-sm mr-2" style="width:240px">
                <select name="cabang_id" class="form-control form-control-sm mr-2">
                    <option value="">Semua Cabang</option>
                    @foreach($cabangs as $c)
                    <option value="{{ $c->id }}" {{ request('cabang_id') == $c->id ? 'selected' : '' }}>{{ $c->nama }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-sm btn-outline-secondary">Cari</button>
            </form>
            <div>
                <button type="button" class="btn btn-sm btn-outline-primary" id="selAll">Pilih semua di halaman</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="selClear">Bersihkan</button>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width:40px"></th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Sekolah</th>
                        <th>Kelas</th>
                        <th>Cabang</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $s)
                    <tr>
                        <td class="text-center">
                            <input type="checkbox" name="user_ids[]" value="{{ $s->id }}" class="user-cb">
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $s->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($s->name).'&size=28&background=1e3a5f&color=fff' }}"
                                     class="img-circle mr-2" style="width:24px;height:24px;object-fit:cover" alt="">
                                {{ $s->name }}
                            </div>
                        </td>
                        <td class="text-muted" style="font-size:13px">{{ $s->username }}</td>
                        <td style="font-size:13px">{{ $s->studentProfile?->school_name ?? '-' }}</td>
                        <td style="font-size:13px">{{ $s->studentProfile?->grade_class ?? '-' }}</td>
                        <td style="font-size:13px">{{ $s->cabang?->nama ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada siswa.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($students->lastPage() > 1)
        <div class="card-footer">
            {{ $students->withQueryString()->links('pagination::bootstrap-4') }}
        </div>
        @endif
    </div>
</form>

@push('scripts')
<script>
(function() {
    var cbs  = document.querySelectorAll('.user-cb');
    var sel  = document.getElementById('selCount');
    var btn  = document.getElementById('genBtn');

    function refresh() {
        var n = document.querySelectorAll('.user-cb:checked').length;
        sel.textContent = n;
        btn.disabled = n === 0;
    }

    cbs.forEach(function(cb){ cb.addEventListener('change', refresh); });

    document.getElementById('selAll').addEventListener('click', function() {
        cbs.forEach(function(cb){ cb.checked = true; });
        refresh();
    });
    document.getElementById('selClear').addEventListener('click', function() {
        cbs.forEach(function(cb){ cb.checked = false; });
        refresh();
    });

    // Template card selection
    document.querySelectorAll('.tpl-card').forEach(function(card) {
        card.addEventListener('click', function() {
            document.querySelectorAll('.tpl-card').forEach(function(c){ c.classList.remove('selected'); });
            card.classList.add('selected');
            card.querySelector('input[type=radio]').checked = true;
            document.getElementById('inputTemplate').value = card.dataset.slug;
            document.getElementById('inputWidth').value    = card.dataset.width;
            document.getElementById('inputHeight').value   = card.dataset.height;
        });
    });

    refresh();
})();
</script>
@endpush
@endsection
