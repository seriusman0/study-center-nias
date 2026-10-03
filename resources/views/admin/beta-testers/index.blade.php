@extends('layouts.admin')

@section('page-title', 'Manage Beta Testers')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Permintaan Akses Beta Tester</h3>
        <p class="text-muted mb-0 mt-1" style="font-size:0.9rem">Daftar pengguna yang ingin mendapatkan akses Play Store.</p>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th>TANGGAL</th>
                        <th>EMAIL (AKUN GOOGLE)</th>
                        <th>WHATSAPP</th>
                        <th>STATUS</th>
                        <th class="text-right">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($testers as $tester)
                    <tr>
                        <td>{{ $tester->created_at->format('d M Y H:i') }}</td>
                        <td class="font-weight-bold">{{ $tester->email }}</td>
                        <td>
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $tester->whatsapp) }}" target="_blank">
                                <i class="fab fa-whatsapp text-success"></i> {{ $tester->whatsapp }}
                            </a>
                        </td>
                        <td>
                            @if($tester->status === 'added')
                                <span class="badge badge-success px-2 py-1">Sudah Ditambahkan</span>
                            @else
                                <span class="badge badge-warning px-2 py-1">Menunggu (Pending)</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <form action="{{ route('admin.beta-testers.update', $tester->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                @if($tester->status === 'pending')
                                    <input type="hidden" name="status" value="added">
                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-check"></i> Tandai Selesai
                                    </button>
                                @else
                                    <input type="hidden" name="status" value="pending">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-undo"></i> Batal (Jadikan Pending)
                                    </button>
                                @endif
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            Belum ada data permintaan beta tester.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($testers->hasPages())
    <div class="card-footer">
        {{ $testers->links('pagination::bootstrap-4') }}
    </div>
    @endif
</div>
@endsection
