@extends('layouts.admin')

@section('page-title', 'Dashboard')

@section('content')
{{-- Info Boxes --}}
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner"><h3>{{ $stats['total_users'] }}</h3><p>Total Pengguna</p></div>
            <div class="icon"><i class="fas fa-users"></i></div>
            <a href="{{ route('admin.users') }}" class="small-box-footer">Lihat <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner"><h3>{{ $stats['total_blogs'] }}</h3><p>Total Blog</p></div>
            <div class="icon"><i class="fas fa-newspaper"></i></div>
            <a href="{{ route('admin.blogs') }}" class="small-box-footer">Lihat <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner"><h3>{{ $stats['total_comments'] }}</h3><p>Total Komentar</p></div>
            <div class="icon"><i class="fas fa-comments"></i></div>
            <a href="#" class="small-box-footer">Info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner"><h3>{{ $stats['total_cabangs'] }}</h3><p>Cabang</p></div>
            <div class="icon"><i class="fas fa-map-marker-alt"></i></div>
            <a href="{{ route('admin.cabangs') }}" class="small-box-footer">Lihat <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

{{-- Charts --}}
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h5 class="card-title m-0">Pengguna per Role</h5></div>
            <div class="card-body"><canvas id="roleChart" height="150"></canvas></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h5 class="card-title m-0">Blog per Cabang</h5></div>
            <div class="card-body"><canvas id="cabangChart" height="150"></canvas></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
new Chart(document.getElementById('roleChart'), {
    type: 'doughnut',
    data: {
        labels: @json($usersByRole->pluck('name')),
        datasets: [{ data: @json($usersByRole->pluck('users_count')),
            backgroundColor: ['#1e3a5f','#c9a84c','#2d5282','#4a7fb5','#6baed6'] }]
    },
    options: { responsive: true }
});

new Chart(document.getElementById('cabangChart'), {
    type: 'bar',
    data: {
        labels: @json($blogsByCabang->pluck('nama')),
        datasets: [{ label: 'Blog', data: @json($blogsByCabang->pluck('blogs_count')),
            backgroundColor: '#1e3a5f' }]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true } } }
});
</script>
@endpush
