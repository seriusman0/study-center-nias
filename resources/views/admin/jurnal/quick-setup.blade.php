@extends('layouts.admin')
@section('page-title', 'Quick Setup Jurnal: ' . $targetUser->name)

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Setup Jurnal</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">{{ $targetUser->name }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
        @endif

        <div class="row">
            <div class="col-md-8">

                {{-- Sync form --}}
                <form method="POST" action="{{ url()->current() }}">
                    @csrf
                    <input type="hidden" name="action" value="sync">

                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <div class="mr-auto">
                                <strong>{{ $targetUser->name }}</strong>
                                <span class="badge badge-secondary ml-1">{{ $role }}</span>
                                <small class="text-muted ml-1">@ {{ $targetUser->username }}</small>
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleAll(true)">
                                    <i class="fas fa-check-double"></i> Pilih Semua
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAll(false)">
                                    <i class="far fa-square"></i> Kosongkan
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            @php
                                $labelMap = [
                                    'kerohanian'       => 'Kerohanian',
                                    'pendidikan'       => 'Pendidikan',
                                    'pendidikan_kuliah'=> 'Pendidikan Kuliah',
                                    'karakter'         => 'Karakter',
                                ];
                            @endphp

                            @forelse($templates as $kat => $items)
                            <div class="mb-4">
                                <div class="d-flex align-items-center mb-2">
                                    <h6 class="text-uppercase text-muted small font-weight-bold mb-0 mr-auto">
                                        {{ $labelMap[$kat] ?? $kat }}
                                    </h6>
                                    <button type="button" class="btn btn-sm btn-link py-0"
                                            onclick="toggleKat('{{ $kat }}', true)">Pilih Semua</button>
                                    <span class="text-muted mx-1">·</span>
                                    <button type="button" class="btn btn-sm btn-link py-0 text-muted"
                                            onclick="toggleKat('{{ $kat }}', false)">Kosongkan</button>
                                </div>
                                @foreach($items as $item)
                                <div class="form-check ml-2">
                                    <input type="checkbox" class="form-check-input jurnal-item kat-{{ $kat }}"
                                           name="template_ids[]" value="{{ $item->id }}" id="item{{ $item->id }}"
                                           {{ in_array($item->id, $assignedIds) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="item{{ $item->id }}">{{ $item->label }}</label>
                                </div>
                                @endforeach
                            </div>
                            @empty
                            <p class="text-muted">Belum ada template item aktif.</p>
                            @endforelse
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Simpan Item Jurnal
                            </button>
                        </div>
                    </div>
                </form>

            </div>

            <div class="col-md-4">

                {{-- Reset to defaults --}}
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-undo mr-1"></i> Reset ke Default</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-sm">Hapus semua assignment dan isi ulang dengan item default untuk role <strong>{{ $role }}</strong>.</p>
                        <form method="POST" action="{{ url()->current() }}"
                              onsubmit="return confirm('Reset semua item jurnal ke default?')">
                            @csrf
                            <input type="hidden" name="action" value="reset">
                            <button type="submit" class="btn btn-warning btn-block">
                                <i class="fas fa-undo mr-1"></i> Reset ke Default
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Info --}}
                <div class="card">
                    <div class="card-body text-sm text-muted">
                        <p><strong>Item yang dicentang</strong> akan aktif di jurnal harian user ini.</p>
                        <p class="mb-0">Item yang tidak dicentang tidak akan muncul di jurnal, tapi data check historis tetap tersimpan.</p>
                    </div>
                </div>

            </div>
        </div>

    </div>
</section>

@push('scripts')
<script>
function toggleAll(state) {
    document.querySelectorAll('.jurnal-item').forEach(cb => cb.checked = state);
}
function toggleKat(kat, state) {
    document.querySelectorAll('.kat-' + kat).forEach(cb => cb.checked = state);
}
</script>
@endpush
@endsection
