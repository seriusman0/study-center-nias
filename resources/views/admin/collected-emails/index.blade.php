@extends('layouts.admin')

@section('title', 'Collected Emails')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Collected Emails</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        {{-- Ringkasan --}}
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="info-box bg-info">
                    <span class="info-box-icon"><i class="fas fa-envelope"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Submitted</span>
                        <span class="info-box-number">{{ $totalInvited + $totalPending }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box bg-success">
                    <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Sudah Diundang</span>
                        <span class="info-box-number">{{ $totalInvited }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box bg-warning">
                    <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Belum Diundang</span>
                        <span class="info-box-number">{{ $totalPending }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter --}}
        <form method="GET" class="mb-3 d-flex gap-2 flex-wrap">
            <input type="text" name="q" class="form-control" style="max-width:220px"
                   placeholder="Cari email / nama..." value="{{ request('q') }}">
            <select name="invited" class="form-control" style="max-width:180px">
                <option value="">Semua</option>
                <option value="0" @selected(request('invited')==='0')>Belum Diundang</option>
                <option value="1" @selected(request('invited')==='1')>Sudah Diundang</option>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('admin.collected-emails.index') }}" class="btn btn-secondary">Reset</a>
        </form>

        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Daftar Email Terkumpul</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama User</th>
                            <th>Email</th>
                            <th>No HP</th>
                            <th>Submit</th>
                            <th class="text-center">Status Undangan</th>
                            <th>Diundang Oleh</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($emails as $index => $item)
                        <tr id="row-{{ $item->id }}">
                            <td>{{ $emails->firstItem() + $index }}</td>
                            <td>{{ $item->user->name ?? '—' }}</td>
                            <td>{{ $item->email }}</td>
                            <td>{{ $item->phone ?? '—' }}</td>
                            <td>{{ $item->created_at->format('d M Y H:i') }}</td>
                            <td class="text-center">
                                @if($item->isInvited())
                                    <span class="badge badge-success" id="badge-{{ $item->id }}">
                                        <i class="fas fa-check"></i> Sudah Diundang
                                    </span>
                                    <br>
                                    <small class="text-muted" id="date-{{ $item->id }}">
                                        {{ $item->invited_at->format('d M Y H:i') }}
                                    </small>
                                @else
                                    <span class="badge badge-secondary" id="badge-{{ $item->id }}">
                                        <i class="fas fa-clock"></i> Belum Diundang
                                    </span>
                                    <small class="text-muted d-none" id="date-{{ $item->id }}"></small>
                                @endif
                            </td>
                            <td id="by-{{ $item->id }}">
                                {{ $item->invitedByUser?->name ?? '—' }}
                                @if($item->invite_note)
                                    <br><small class="text-muted">{{ $item->invite_note }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                <button
                                    class="btn btn-sm {{ $item->isInvited() ? 'btn-outline-warning' : 'btn-success' }} btn-toggle-invite"
                                    id="btn-{{ $item->id }}"
                                    data-id="{{ $item->id }}"
                                    data-invited="{{ $item->isInvited() ? '1' : '0' }}"
                                    data-url="{{ route('admin.collected-emails.toggle-invite', $item) }}"
                                    title="{{ $item->isInvited() ? 'Batalkan undangan' : 'Tandai sudah diundang' }}">
                                    <i class="fas {{ $item->isInvited() ? 'fa-times' : 'fa-check' }}"></i>
                                    {{ $item->isInvited() ? 'Batalkan' : 'Tandai Diundang' }}
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">Belum ada data.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer clearfix">
                {{ $emails->links() }}
            </div>
        </div>

    </div>
</section>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.btn-toggle-invite').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const id       = this.dataset.id;
        const url      = this.dataset.url;
        const isInvited = this.dataset.invited === '1';

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({})
        })
        .then(async r => {
            if (!r.ok) {
                const text = await r.text();
                throw new Error('Network response was not ok: ' + text);
            }
            return r.json();
        })
        .then(data => {
            const nowInvited = data.is_invited;
            const badge      = document.getElementById('badge-' + id);
            const dateEl     = document.getElementById('date-' + id);
            const byEl       = document.getElementById('by-' + id);

            if (nowInvited) {
                badge.className    = 'badge badge-success';
                badge.innerHTML    = '<i class="fas fa-check"></i> Sudah Diundang';
                dateEl.classList.remove('d-none');
                dateEl.textContent = new Date(data.invited_at).toLocaleString('id-ID');
                byEl.textContent   = data.invited_by || '—';
                btn.className      = 'btn btn-sm btn-outline-warning btn-toggle-invite';
                btn.innerHTML      = '<i class="fas fa-times"></i> Batalkan';
                btn.dataset.invited = '1';
            } else {
                badge.className    = 'badge badge-secondary';
                badge.innerHTML    = '<i class="fas fa-clock"></i> Belum Diundang';
                dateEl.classList.add('d-none');
                dateEl.textContent = '';
                byEl.textContent   = '—';
                btn.className      = 'btn btn-sm btn-success btn-toggle-invite';
                btn.innerHTML      = '<i class="fas fa-check"></i> Tandai Diundang';
                btn.dataset.invited = '0';
            }
            btn.disabled = false;
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error';
            setTimeout(() => {
                btn.innerHTML = isInvited
                    ? '<i class="fas fa-times"></i> Batalkan'
                    : '<i class="fas fa-check"></i> Tandai Diundang';
            }, 2000);
        });
    });
});
</script>
@endpush
