@extends('layouts.admin')

@section('page-title', 'Kelola Template Name Tag')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <a href="{{ route('admin.nametags') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    {{ session('error') }}
</div>
@endif

<div class="row">
    @foreach($templates as $tpl)
    <div class="col-md-4 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <h6 class="mb-0 font-weight-bold">{{ $tpl->name }}</h6>
                    @if($tpl->is_system)
                        <span class="badge badge-info" style="font-size:10px">Sistem</span>
                    @else
                        <span class="badge badge-secondary" style="font-size:10px">Custom</span>
                    @endif
                </div>
                <p class="text-muted small mb-2">{{ $tpl->description }}</p>
                <div class="d-flex gap-2 mb-3" style="gap:6px">
                    <span class="badge badge-light border">{{ $tpl->width }} × {{ $tpl->height }} cm</span>
                    <span class="badge badge-light border">{{ ucfirst($tpl->orientation) }}</span>
                </div>
                <div class="d-flex" style="gap:6px">
                    <a href="{{ route('admin.nametag-templates.edit', $tpl) }}"
                       class="btn btn-sm btn-primary flex-fill">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </a>
                    <form method="POST" action="{{ route('admin.nametag-templates.duplicate', $tpl) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Duplikasi">
                            <i class="fas fa-copy"></i>
                        </button>
                    </form>
                    @if(!$tpl->is_system)
                    <form method="POST" action="{{ route('admin.nametag-templates.destroy', $tpl) }}"
                          onsubmit="return confirm('Hapus template ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
