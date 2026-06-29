@extends('layouts.admin')

@section('page-title', 'Blog')

@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" action="{{ route('admin.blogs') }}" class="form-inline">
            <input type="search" name="search" value="{{ request('search') }}"
                   placeholder="Cari blog..." class="form-control form-control-sm mr-2">
            <button type="submit" class="btn btn-sm btn-secondary">Cari</button>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Judul</th>
                    <th>Penulis</th>
                    <th>Cabang</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($blogs as $blog)
                <tr>
                    <td>
                        <a href="{{ route('blog.show', $blog->slug) }}" target="_blank"
                           class="font-weight-bold text-dark">
                            {{ Str::limit($blog->title, 60) }}
                        </a>
                    </td>
                    <td style="font-size:13px">{{ $blog->user?->name }}</td>
                    <td style="font-size:13px">{{ $blog->cabang?->nama }}</td>
                    <td style="font-size:12px" class="text-muted">
                        {{ $blog->published_at?->format('d/m/Y') }}
                    </td>
                    <td>
                        <a href="{{ route('blog.edit', $blog->slug) }}"
                           class="btn btn-xs btn-info">Edit</a>
                        <form method="POST" action="{{ route('admin.blogs.delete', $blog->id) }}"
                              class="d-inline" onsubmit="return confirm('Hapus blog ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-xs btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($blogs->lastPage() > 1)
    <div class="card-footer">
        {{ $blogs->withQueryString()->links('pagination::bootstrap-4') }}
    </div>
    @endif
</div>
@endsection
