<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin | Study Center Nias</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    {{-- Top Navbar --}}
    <nav class="main-header navbar navbar-expand navbar-white navbar-light border-bottom">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="{{ route('home') }}" class="nav-link text-muted" style="font-size:13px">
                    <i class="fas fa-home mr-1"></i> Beranda
                </a>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button">
                    <img src="{{ auth()->user()->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&size=28&background=1e3a5f&color=fff' }}"
                         class="img-circle mr-2" style="width:24px;height:24px;object-fit:cover" alt="">
                    <span style="font-size:13px">{{ auth()->user()->name }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a href="{{ route('profile.show', auth()->user()->username) }}" class="dropdown-item">
                        <i class="fas fa-user mr-2 text-muted"></i> Profil Saya
                    </a>
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="fas fa-sign-out-alt mr-2"></i> Keluar
                        </button>
                    </form>
                </div>
            </li>
        </ul>
    </nav>

    {{-- Sidebar --}}
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="{{ route('admin.dashboard') }}" class="brand-link d-flex align-items-center" style="padding:8px 16px;text-decoration:none">
            <span class="brand-text font-weight-bold text-white" style="font-size:13px;line-height:1.3">
                Study Center<br>
                <small style="font-weight:400;opacity:.65;font-size:11px">Admin Panel</small>
            </span>
        </a>
        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="{{ auth()->user()->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&background=1e3a5f&color=fff' }}"
                         class="img-circle elevation-2" style="width:34px;height:34px;object-fit:cover" alt="">
                </div>
                <div class="info">
                    <span class="d-block text-white" style="font-size:13px;line-height:1.4">{{ auth()->user()->name }}</span>
                    <small class="d-block text-white-50" style="font-size:11px">
                        {{ auth()->user()->cabang?->nama ?? 'Administrator' }}
                    </small>
                </div>
            </div>
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item">
                        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.users') }}" class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users"></i><p>Pengguna</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.cabangs') }}" class="nav-link {{ request()->routeIs('admin.cabangs*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-map-marker-alt"></i><p>Cabang</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.blogs') }}" class="nav-link {{ request()->routeIs('admin.blogs*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-newspaper"></i><p>Blog</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    {{-- Content Wrapper --}}
    <div class="content-wrapper" style="background:#f4f6f9">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
        @endif
        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
            @foreach($errors->all() as $err)<p class="mb-0">{{ $err }}</p>@endforeach
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
        @endif

        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h5 class="m-0 text-dark font-weight-bold">@yield('page-title', 'Admin')</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </div>
    </div>

    <footer class="main-footer">
        <strong>Study Center Nias &copy; {{ date('Y') }}</strong>
        <div class="float-right d-none d-sm-inline-block"><b>Admin Panel</b></div>
    </footer>

    <aside class="control-sidebar control-sidebar-dark"></aside>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/js/adminlte.min.js"></script>
@stack('scripts')
</body>
</html>
