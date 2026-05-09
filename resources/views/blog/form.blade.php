@extends('layouts.app')

@section('title', isset($blog) ? 'Edit Blog' : 'Tulis Blog Baru')

@push('head')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
<style>
    #editor-content { min-height: 400px; font-size: 16px; }
    .ql-toolbar.ql-snow { border-top-left-radius: 0.75rem; border-top-right-radius: 0.75rem; border-color: #e5e7eb; }
    .ql-container.ql-snow { border-bottom-left-radius: 0.75rem; border-bottom-right-radius: 0.75rem; border-color: #e5e7eb; }
    .ql-editor img { max-width: 100%; height: auto; border-radius: 0.5rem; margin: 0.5rem 0; }
    .ql-editor h2 { font-size: 1.5rem; font-weight: 700; color: #1e3a5f; margin: 1rem 0 0.5rem; }
    .ql-editor h3 { font-size: 1.25rem; font-weight: 600; color: #1e3a5f; margin: 0.75rem 0 0.5rem; }
    .ql-editor blockquote { border-left: 4px solid #c9a84c; padding-left: 1rem; color: #4b5563; }
    .ql-editor pre { background: #1f2937; color: #f9fafb; padding: 0.75rem 1rem; border-radius: 0.5rem; font-size: 0.875rem; }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold text-[#1e3a5f] mb-8">
        {{ isset($blog) ? 'Edit Blog' : 'Tulis Blog Baru' }}
    </h1>

    <form method="POST"
          action="{{ isset($blog) ? route('blog.update', $blog->id) : route('blog.store') }}"
          enctype="multipart/form-data"
          class="space-y-5"
          id="blogForm">
        @csrf
        @if(isset($blog))
        @method('PUT')
        @endif

        <div>
            <label class="block text-sm font-medium mb-1">Judul</label>
            <input type="text" name="title" required
                   value="{{ old('title', $blog->title ?? '') }}"
                   class="w-full border rounded-xl px-4 py-3 text-lg font-semibold outline-[#1e3a5f]"
                   placeholder="Judul blog...">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Cabang</label>
                <select name="cabang_id" required class="w-full border rounded-xl px-3 py-2 outline-[#1e3a5f]">
                    <option value="">Pilih cabang...</option>
                    @foreach($cabangs as $c)
                    <option value="{{ $c->id }}"
                        {{ old('cabang_id', $blog->cabang_id ?? '') == $c->id ? 'selected' : '' }}>
                        {{ $c->nama }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Tags <span class="text-gray-400 font-normal">(pisah koma)</span></label>
                <input type="text" name="tags"
                       value="{{ old('tags', isset($blog) ? $blog->tags->pluck('name')->join(', ') : '') }}"
                       class="w-full border rounded-xl px-3 py-2 outline-[#1e3a5f]"
                       placeholder="Pendidikan, Nias, ...">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Gambar Sampul <span class="text-gray-400 font-normal">(maks. 2MB)</span></label>
            <input type="file" name="image" accept="image/*" class="text-sm">
            @if(isset($blog) && $blog->image)
            <img src="{{ asset('storage/'.$blog->image) }}" class="mt-2 h-24 rounded-lg object-cover" alt="">
            @endif
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Konten</label>
            <p class="text-xs text-gray-500 mb-2">
                <i class="text-[#c9a84c]">Tip:</i> klik ikon gambar di toolbar untuk menyisipkan gambar di dalam tulisan.
            </p>
            <input type="hidden" name="content" id="content-input">
            <div id="editor-toolbar">
                <span class="ql-formats">
                    <select class="ql-header">
                        <option value="2">Heading</option>
                        <option value="3">Sub-heading</option>
                        <option selected>Paragraf</option>
                    </select>
                </span>
                <span class="ql-formats">
                    <button class="ql-bold" title="Tebal"></button>
                    <button class="ql-italic" title="Miring"></button>
                    <button class="ql-underline" title="Garis bawah"></button>
                    <button class="ql-strike" title="Coret"></button>
                </span>
                <span class="ql-formats">
                    <button class="ql-list" value="ordered" title="Daftar bernomor"></button>
                    <button class="ql-list" value="bullet" title="Daftar"></button>
                    <button class="ql-blockquote" title="Kutipan"></button>
                    <button class="ql-code-block" title="Kode"></button>
                </span>
                <span class="ql-formats">
                    <button class="ql-link" title="Tautan"></button>
                    <button class="ql-image" title="Sisipkan gambar"></button>
                    <button class="ql-video" title="Video"></button>
                </span>
                <span class="ql-formats">
                    <select class="ql-align"></select>
                </span>
                <span class="ql-formats">
                    <button class="ql-clean" title="Bersihkan format"></button>
                </span>
            </div>
            <div id="editor-content"></div>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('blog.index') }}"
               class="flex-none px-5 py-3 border border-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-50">
                Batal
            </a>
            <button type="submit"
                    class="flex-1 py-3 bg-[#1e3a5f] text-white rounded-xl font-semibold hover:bg-[#2d5282] transition">
                {{ isset($blog) ? 'Perbarui Blog' : 'Terbitkan Blog' }}
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script>
(function() {
    const initialContent = @json(isset($blog) ? $blog->content : '');
    const uploadUrl = @json(route('blog.upload-image'));
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    const quill = new Quill('#editor-content', {
        theme: 'snow',
        placeholder: 'Tulis cerita Anda di sini...',
        modules: {
            toolbar: {
                container: '#editor-toolbar',
                handlers: {
                    image: function() {
                        const input = document.createElement('input');
                        input.setAttribute('type', 'file');
                        input.setAttribute('accept', 'image/*');
                        input.click();
                        input.onchange = async () => {
                            const file = input.files[0];
                            if (!file) return;
                            if (file.size > 5 * 1024 * 1024) {
                                alert('Ukuran gambar maksimal 5MB.');
                                return;
                            }
                            const range = quill.getSelection(true);
                            const placeholder = quill.insertEmbed(range.index, 'image', '', 'user');
                            quill.setSelection(range.index + 1);

                            try {
                                const fd = new FormData();
                                fd.append('image', file);
                                fd.append('_token', csrfToken);
                                const res = await fetch(uploadUrl, {
                                    method: 'POST',
                                    body: fd,
                                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                                });
                                if (!res.ok) throw new Error('Upload gagal: ' + res.status);
                                const data = await res.json();
                                quill.deleteText(range.index, 1);
                                quill.insertEmbed(range.index, 'image', data.url, 'user');
                                quill.setSelection(range.index + 1);
                            } catch (e) {
                                quill.deleteText(range.index, 1);
                                alert('Gagal upload gambar: ' + e.message);
                            }
                        };
                    }
                }
            }
        },
    });

    if (initialContent) {
        quill.clipboard.dangerouslyPasteHTML(initialContent);
    }

    document.getElementById('blogForm').addEventListener('submit', function(e) {
        const html = quill.getSemanticHTML ? quill.getSemanticHTML() : quill.root.innerHTML;
        const text = quill.getText().trim();
        if (!text) {
            e.preventDefault();
            alert('Konten tidak boleh kosong.');
            return false;
        }
        document.getElementById('content-input').value = quill.root.innerHTML;
    });
})();
</script>
@endpush
