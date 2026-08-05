@extends('layouts.admin')
@section('page-title', 'Jurnal Hub')

@section('content')
<div x-data="jurnalHub()" x-init="init()">

{{-- Tab Nav --}}
<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a class="nav-link" :class="tab==='dashboard'?'active':''" href="#" @click.prevent="tab='dashboard'">
            <i class="fas fa-chart-bar mr-1"></i>Dashboard
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" :class="tab==='laporan'?'active':''" href="#" @click.prevent="switchTab('laporan')">
            <i class="fas fa-users mr-1"></i>Laporan
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" :class="tab==='items'?'active':''" href="#" @click.prevent="switchTab('items')">
            <i class="fas fa-list mr-1"></i>Item Jurnal
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" :class="tab==='pengaturan'?'active':''" href="#" @click.prevent="tab='pengaturan'">
            <i class="fas fa-cog mr-1"></i>Pengaturan
        </a>
    </li>
</ul>

{{-- ══════════════ TAB: DASHBOARD ══════════════ --}}
<div x-show="tab==='dashboard'" x-cloak>

    {{-- Bible + form open --}}
    <div class="row mb-3">
        <div class="col-md-8">
            <div class="card card-primary card-outline">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-center px-3 border-right">
                            <div class="text-muted small">Hari ke</div>
                            <div class="font-weight-bold" style="font-size:2rem;line-height:1">{{ $dayNo }}</div>
                        </div>
                        <div class="flex-grow-1">
                            @if($bible)
                            <div class="font-weight-bold">{{ $bible->pl_text }} &nbsp;/&nbsp; {{ $bible->pb_text }}</div>
                            <div class="text-muted small mt-1">Jadwal pembacaan alkitab hari ini</div>
                            @else
                            <div class="text-muted">Jadwal hari ke-{{ $dayNo }} belum diisi.
                                <a href="{{ route('admin.jurnal-college.bible') }}">Atur sekarang</a>
                            </div>
                            @endif
                        </div>
                        <a href="{{ route('admin.jurnal-college.bible') }}" class="btn btn-sm btn-outline-primary ml-3">
                            <i class="fas fa-cog"></i> Ubah Jadwal
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-outline {{ $config->isFormOpen() ? 'card-success' : 'card-warning' }}">
                <div class="card-body py-3 text-center">
                    <div class="text-muted small mb-1">Jam Form Jurnal</div>
                    <div class="font-weight-bold" style="font-size:1.1rem">
                        {{ substr($config->form_open_time, 0, 5) }} – {{ substr($config->form_close_time, 0, 5) }}
                    </div>
                    <span class="badge badge-{{ $config->isFormOpen() ? 'success' : 'warning' }} mt-1">
                        {{ $config->isFormOpen() ? 'Sedang Buka' : 'Sedang Tutup' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Stat cards --}}
    <div class="row">
        @foreach([
            ['role'=>'college',              'label'=>'College',          'color'=>'info',    'icon'=>'fa-graduation-cap'],
            ['role'=>'scholarship_teenager',  'label'=>'Remaja Beasiswa',  'color'=>'success', 'icon'=>'fa-star'],
            ['role'=>'student',               'label'=>'Siswa',            'color'=>'primary', 'icon'=>'fa-user-graduate'],
        ] as $s)
        @php $stat = $stats[$s['role']]; @endphp
        <div class="col-md-4">
            <div class="card card-outline card-{{ $s['color'] }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">{{ $s['label'] }}</div>
                            <div class="font-weight-bold" style="font-size:1.6rem">{{ $stat['total'] }}</div>
                            <div class="small">
                                <span class="badge badge-{{ $stat['active_today'] > 0 ? 'success' : 'secondary' }}">
                                    {{ $stat['active_today'] }} aktif hari ini
                                </span>
                                @if($stat['total'] > 0)
                                <span class="text-muted ml-1">
                                    ({{ round($stat['active_today'] / $stat['total'] * 100) }}%)
                                </span>
                                @endif
                            </div>
                        </div>
                        <i class="fas {{ $s['icon'] }} fa-2x text-{{ $s['color'] }}"></i>
                    </div>
                </div>
                <div class="card-footer p-0">
                    <a href="#" class="btn btn-link btn-sm btn-block text-left"
                       @click.prevent="switchTab('laporan'); role='{{ $s['role'] }}'">
                        Lihat Laporan <i class="fas fa-arrow-right float-right mt-1"></i>
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- ══════════════ TAB: LAPORAN ══════════════ --}}
<div x-show="tab==='laporan'" x-cloak>
    {{-- Role + search bar --}}
    <div class="card card-outline card-primary mb-3">
        <div class="card-body py-2">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="btn-group btn-group-sm mr-3">
                    <button class="btn" :class="role==='college'?'btn-primary':'btn-outline-secondary'" @click="setRole('college')">College</button>
                    <button class="btn" :class="role==='scholarship_teenager'?'btn-primary':'btn-outline-secondary'" @click="setRole('scholarship_teenager')">Remaja Beasiswa</button>
                    <button class="btn" :class="role==='student'?'btn-primary':'btn-outline-secondary'" @click="setRole('student')">Siswa</button>
                </div>
                <input type="text" class="form-control form-control-sm" style="width:200px"
                       placeholder="Cari nama…" x-model="userSearch" @input.debounce.400ms="loadUsers(1)">
                <template x-if="role==='college'">
                    <input type="text" class="form-control form-control-sm" style="width:160px"
                           placeholder="Filter kampus…" x-model="campusFilter" @input.debounce.400ms="loadUsers(1)">
                </template>
                <button class="btn btn-sm btn-outline-secondary" @click="loadUsers(usersPage)">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- Users table --}}
    <div class="card">
        <div class="card-body p-0" style="position:relative;min-height:100px">
            <div x-show="usersLoading" class="text-center py-4">
                <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
            </div>
            <table class="table table-sm table-hover mb-0" x-show="!usersLoading">
                <thead class="thead-light">
                    <tr>
                        <th>Nama</th>
                        <th x-show="role==='college'">Kampus</th>
                        <th class="text-center">7 Hari</th>
                        <th>Terakhir Aktif</th>
                        <th class="text-center">Hari Ini</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="users.length === 0 && !usersLoading">
                        <tr><td colspan="6" class="text-center text-muted py-3">Tidak ada pengguna.</td></tr>
                    </template>
                    <template x-for="u in users" :key="u.id">
                        <tr>
                            <td>
                                <strong x-text="u.name"></strong><br>
                                <small class="text-muted" x-text="'@' + u.username"></small>
                            </td>
                            <td x-show="role==='college'" x-text="u.campus || '—'"></td>
                            <td class="text-center">
                                <span class="badge"
                                      :class="u.checks7 >= 10 ? 'badge-success' : u.checks7 >= 5 ? 'badge-warning' : 'badge-secondary'"
                                      x-text="u.checks7"></span>
                            </td>
                            <td x-text="u.last_entry ? formatDate(u.last_entry) : '—'"></td>
                            <td class="text-center">
                                <span x-show="u.active_today" class="badge badge-success"><i class="fas fa-check"></i></span>
                                <span x-show="!u.active_today" class="text-muted">—</span>
                            </td>
                            <td>
                                <button class="btn btn-xs btn-info" @click="openMatrix(u)">
                                    <i class="fas fa-chart-bar"></i> Laporan
                                </button>
                                <a :href="exportUrl(u)" class="btn btn-xs btn-success">
                                    <i class="fas fa-download"></i> CSV
                                </a>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        {{-- Pagination --}}
        <div class="card-footer py-1 d-flex justify-content-between align-items-center" x-show="usersLastPage > 1">
            <button class="btn btn-sm btn-outline-secondary" :disabled="usersPage <= 1" @click="loadUsers(usersPage - 1)">
                <i class="fas fa-chevron-left"></i> Sebelumnya
            </button>
            <small class="text-muted">Hal <span x-text="usersPage"></span> / <span x-text="usersLastPage"></span></small>
            <button class="btn btn-sm btn-outline-secondary" :disabled="usersPage >= usersLastPage" @click="loadUsers(usersPage + 1)">
                Selanjutnya <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>

    {{-- Matrix slide-over --}}
    <div x-show="selectedUser !== null" x-cloak
         style="position:fixed;top:0;right:0;bottom:0;width:min(700px,100%);background:#fff;box-shadow:-4px 0 20px rgba(0,0,0,.15);z-index:1050;overflow-y:auto">
        <div class="p-3 border-bottom d-flex justify-content-between align-items-start">
            <div>
                <h5 class="mb-0" x-text="selectedUser ? selectedUser.name : ''"></h5>
                <small class="text-muted" x-text="selectedUser ? '@' + selectedUser.username : ''"></small>
            </div>
            <button class="btn btn-sm btn-outline-secondary" @click="selectedUser=null;matrix=null">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-3">
            {{-- Date range --}}
            <div class="form-inline mb-3">
                <label class="mr-2 text-muted small">Dari</label>
                <input type="date" class="form-control form-control-sm mr-3" x-model="matrixFrom">
                <label class="mr-2 text-muted small">Sampai</label>
                <input type="date" class="form-control form-control-sm mr-3" x-model="matrixTo">
                <button class="btn btn-sm btn-primary" @click="loadMatrix()">Tampilkan</button>
                <template x-if="selectedUser">
                    <a :href="exportUrl(selectedUser, matrixFrom, matrixTo)" class="btn btn-sm btn-success ml-2">
                        <i class="fas fa-download"></i> CSV
                    </a>
                </template>
            </div>

            <div x-show="matrixLoading" class="text-center py-4">
                <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
            </div>

            <div x-show="!matrixLoading && matrix">
                <div class="d-flex gap-3 mb-3">
                    <span class="badge badge-info px-3 py-2">
                        Skor: <strong x-text="matrix ? matrix.pct + '%' : ''"></strong>
                    </span>
                    <span class="badge badge-secondary px-3 py-2">
                        <span x-text="matrix ? matrix.checked : 0"></span> / <span x-text="matrix ? matrix.total : 0"></span> centang
                    </span>
                </div>

                <div style="overflow-x:auto">
                    <table class="table table-bordered table-sm" style="font-size:.78rem">
                        <thead class="thead-light">
                            <tr>
                                <template x-if="matrix">
                                    <template x-for="h in matrix.headers" :key="h">
                                        <th x-text="h" class="text-center" style="white-space:nowrap;min-width:60px"></th>
                                    </template>
                                </template>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-if="matrix">
                                <template x-for="row in matrix.rows" :key="row[0]">
                                    <tr>
                                        <template x-for="(cell, i) in row" :key="i">
                                            <td class="text-center"
                                                :class="cell==='Y' ? 'table-success' : ''"
                                                x-text="cell"></td>
                                        </template>
                                    </tr>
                                </template>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <template x-if="selectedUser">
                <div class="mt-3 border-top pt-3">
                    <a :href="quickSetupUrl(selectedUser)" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-sliders-h"></i> Atur Item Jurnal User Ini
                    </a>
                </div>
            </template>
        </div>
    </div>
    {{-- Overlay --}}
    <div x-show="selectedUser !== null" x-cloak
         style="position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:1049"
         @click="selectedUser=null;matrix=null"></div>
</div>

{{-- ══════════════ TAB: ITEM JURNAL ══════════════ --}}
<div x-show="tab==='items'" x-cloak>
    <div class="card card-outline card-primary mb-3">
        <div class="card-body py-2">
            <div class="btn-group btn-group-sm">
                <button class="btn" :class="role==='college'?'btn-primary':'btn-outline-secondary'" @click="role='college'">College</button>
                <button class="btn" :class="role==='scholarship_teenager'?'btn-primary':'btn-outline-secondary'" @click="role='scholarship_teenager'">Remaja Beasiswa</button>
                <button class="btn" :class="role==='student'?'btn-primary':'btn-outline-secondary'" @click="role='student'">Siswa</button>
            </div>
        </div>
    </div>

    {{-- Items grouped by kategori --}}
    @foreach(['college' => $itemsByRole['college']->groupBy('kategori'),
              'scholarship_teenager' => $itemsByRole['scholarship_teenager']->groupBy('kategori'),
              'student' => $itemsByRole['student']->groupBy('kategori')] as $roleKey => $grouped)
    <div x-show="role==='{{ $roleKey }}'">
        @foreach($grouped as $kategori => $items)
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-capitalize">{{ $kategori }}</h6>
                <button class="btn btn-sm btn-primary" data-toggle="modal"
                        data-target="#addItemModal"
                        onclick="setAddContext('{{ $roleKey }}', '{{ $kategori }}')">
                    <i class="fas fa-plus"></i> Tambah
                </button>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Label</th>
                            <th>Tipe Respons</th>
                            <th class="text-center">Aktif</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                        <tr>
                            <td>{{ $item->label }}</td>
                            <td><span class="badge badge-secondary">{{ $item->response_type }}</span></td>
                            <td class="text-center">
                                @if($item->is_active)
                                <span class="badge badge-success">Aktif</span>
                                @else
                                <span class="badge badge-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-xs btn-warning" data-toggle="modal"
                                        data-target="#editItemModal"
                                        onclick="setEditContext({{ $item->id }}, '{{ addslashes($item->label) }}', '{{ $item->response_type }}', '{{ $item->kategori }}', {{ $item->is_active ? 'true' : 'false' }}, '{{ $roleKey }}')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST"
                                      action="{{ $roleKey === 'student'
                                          ? route('admin.jurnal.life-items.destroy', $item)
                                          : ($roleKey === 'college'
                                              ? route('admin.jurnal-college.items.destroy', $item)
                                              : route('admin.jurnal-scholarship-teenager.items.destroy', $item)) }}"
                                      style="display:inline"
                                      onsubmit="return confirm('Hapus item ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                        @if($items->isEmpty())
                        <tr><td colspan="4" class="text-center text-muted py-2">Belum ada item.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
    </div>
    @endforeach
</div>

{{-- ══════════════ TAB: PENGATURAN ══════════════ --}}
<div x-show="tab==='pengaturan'" x-cloak>
    <div class="row">
        <div class="col-md-6">
            <div class="card card-outline card-primary">
                <div class="card-header"><h5 class="card-title mb-0">Jam Buka/Tutup Form Jurnal</h5></div>
                <div class="card-body">
                    @if(session('config_success'))
                    <div class="alert alert-success">{{ session('config_success') }}</div>
                    @endif
                    <form method="POST" action="{{ route('admin.jurnal-hub.config') }}">
                        @csrf
                        <div class="form-group">
                            <label>Jam Buka</label>
                            <input type="time" name="form_open_time" class="form-control"
                                   value="{{ substr($config->form_open_time, 0, 5) }}" required>
                        </div>
                        <div class="form-group">
                            <label>Jam Tutup</label>
                            <input type="time" name="form_close_time" class="form-control"
                                   value="{{ substr($config->form_close_time, 0, 5) }}" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-outline card-secondary">
                <div class="card-header"><h5 class="card-title mb-0">Tautan Pengaturan Lanjutan</h5></div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <a href="{{ route('admin.jurnal-college.bible-schedules.index') }}" class="d-flex align-items-center">
                                <i class="fas fa-calendar-alt mr-3 text-primary"></i>
                                <div>
                                    <div class="font-weight-bold">Jadwal Alkitab</div>
                                    <div class="text-muted small">Kelola jadwal pembacaan 366 hari</div>
                                </div>
                                <i class="fas fa-external-link-alt ml-auto text-muted"></i>
                            </a>
                        </li>
                        <li class="list-group-item">
                            <a href="{{ route('admin.jurnal-college.bible') }}" class="d-flex align-items-center">
                                <i class="fas fa-book-open mr-3 text-success"></i>
                                <div>
                                    <div class="font-weight-bold">Item Alkitab Harian</div>
                                    <div class="text-muted small">Edit PL/PB per hari ke-</div>
                                </div>
                                <i class="fas fa-external-link-alt ml-auto text-muted"></i>
                            </a>
                        </li>
                        <li class="list-group-item">
                            <a href="{{ route('admin.jurnal-college.index') }}" class="d-flex align-items-center">
                                <i class="fas fa-graduation-cap mr-3 text-info"></i>
                                <div>
                                    <div class="font-weight-bold">Dashboard College (lama)</div>
                                    <div class="text-muted small">Tampilan lama per-role</div>
                                </div>
                                <i class="fas fa-external-link-alt ml-auto text-muted"></i>
                            </a>
                        </li>
                        <li class="list-group-item">
                            <a href="{{ route('admin.jurnal-scholarship-teenager.index') }}" class="d-flex align-items-center">
                                <i class="fas fa-star mr-3 text-warning"></i>
                                <div>
                                    <div class="font-weight-bold">Dashboard Remaja Beasiswa (lama)</div>
                                    <div class="text-muted small">Tampilan lama per-role</div>
                                </div>
                                <i class="fas fa-external-link-alt ml-auto text-muted"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

</div>{{-- end x-data --}}

{{-- ══════════════ MODALS ══════════════ --}}

{{-- Add Item Modal --}}
<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addItemForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Item Jurnal</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Kategori</label>
                        <input type="text" name="kategori" id="addKategori" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label>Label</label>
                        <input type="text" name="label" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Tipe Respons</label>
                        <select name="response_type" class="form-control">
                            <option value="check">Check</option>
                            <option value="boolean">Boolean</option>
                            <option value="time_range">Time Range</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Tambah</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Item Modal --}}
<div class="modal fade" id="editItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editItemForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Item Jurnal</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Kategori</label>
                        <input type="text" name="kategori" id="editKategori" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label>Label</label>
                        <input type="text" name="label" id="editLabel" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Tipe Respons</label>
                        <select name="response_type" id="editResponseType" class="form-control">
                            <option value="check">Check</option>
                            <option value="boolean">Boolean</option>
                            <option value="time_range">Time Range</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="is_active" id="editIsActive" class="form-control">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Item modal helpers
const itemRoutes = {
    college:              { store: '{{ route('admin.jurnal-college.items.store') }}', update: '/admin/jurnal-college/items/' },
    scholarship_teenager: { store: '{{ route('admin.jurnal-scholarship-teenager.items.store') }}', update: '/admin/jurnal-scholarship-teenager/items/' },
    student:              { store: '{{ route('admin.jurnal.life-items.store') }}', update: '/admin/jurnal/life-items/' },
};

function setAddContext(role, kategori) {
    document.getElementById('addKategori').value = kategori;
    document.getElementById('addItemForm').action = itemRoutes[role].store;
}

function setEditContext(id, label, responseType, kategori, isActive, role) {
    document.getElementById('editKategori').value = kategori;
    document.getElementById('editLabel').value = label;
    document.getElementById('editResponseType').value = responseType;
    document.getElementById('editIsActive').value = isActive ? '1' : '0';
    document.getElementById('editItemForm').action = itemRoutes[role].update + id;
}

// Alpine component
function jurnalHub() {
    return {
        tab: 'dashboard',
        role: 'college',
        users: [],
        usersPage: 1,
        usersLastPage: 1,
        userSearch: '',
        campusFilter: '',
        usersLoading: false,
        selectedUser: null,
        matrix: null,
        matrixLoading: false,
        matrixFrom: '',
        matrixTo: '',

        init() {
            // Set default date range for matrix
            const today = new Date().toISOString().slice(0, 10);
            const from  = new Date(Date.now() - 13 * 86400000).toISOString().slice(0, 10);
            this.matrixTo   = today;
            this.matrixFrom = from;
        },

        switchTab(t) {
            this.tab = t;
            if (t === 'laporan' && this.users.length === 0) {
                this.loadUsers(1);
            }
        },

        setRole(r) {
            this.role = r;
            this.campusFilter = '';
            this.userSearch   = '';
            this.users        = [];
            this.usersPage    = 1;
            this.loadUsers(1);
        },

        async loadUsers(page) {
            this.usersLoading = true;
            this.usersPage    = page;
            try {
                const params = new URLSearchParams({ role: this.role, page, q: this.userSearch });
                if (this.campusFilter) params.set('campus', this.campusFilter);
                const res  = await fetch(`/admin/jurnal-hub/users?${params}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const json = await res.json();
                this.users        = json.data;
                this.usersPage    = json.current_page;
                this.usersLastPage = json.last_page;
            } finally {
                this.usersLoading = false;
            }
        },

        async openMatrix(user) {
            this.selectedUser = user;
            await this.loadMatrix();
        },

        async loadMatrix() {
            if (!this.selectedUser) return;
            this.matrixLoading = true;
            this.matrix        = null;
            try {
                const params = new URLSearchParams({
                    role: this.role,
                    from: this.matrixFrom,
                    to:   this.matrixTo,
                });
                const res  = await fetch(`/admin/jurnal-hub/matrix/${this.selectedUser.id}?${params}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                this.matrix = await res.json();
            } finally {
                this.matrixLoading = false;
            }
        },

        exportUrl(user, from, to) {
            const params = new URLSearchParams({ role: this.role });
            if (from) params.set('from', from);
            if (to)   params.set('to', to);
            return `/admin/jurnal-hub/export/${user.id}?${params}`;
        },

        quickSetupUrl(user) {
            const roleRoutes = {
                college:              `/admin/jurnal-college/users/${user.id}/quick-setup`,
                scholarship_teenager: `/admin/jurnal-scholarship-teenager/users/${user.id}/quick-setup`,
                student:              `/admin/jurnal/reports/${user.id}/quick-setup`,
            };
            return roleRoutes[this.role] || '#';
        },

        formatDate(d) {
            if (!d) return '—';
            return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
        },
    };
}
</script>
@endpush
