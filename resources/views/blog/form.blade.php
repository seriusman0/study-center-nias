@extends('layouts.app')

@section('title', isset($blog) ? 'Edit Blog' : 'Tulis Blog Baru')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold text-[#1e3a5f] mb-8">
        {{ isset($blog) ? 'Edit Blog' : 'Tulis Blog Baru' }}
    </h1>

    <form method="POST"
          action="{{ isset($blog) ? route('blog.update', $blog->id) : route('blog.store') }}"
          enctype="multipart/form-data"
          class="space-y-5">
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

        <div class="grid grid-cols-2 gap-4">
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
                <label class="block text-sm font-medium mb-1">Tags (pisah koma)</label>
                <input type="text" name="tags"
                       value="{{ old('tags', isset($blog) ? $blog->tags->pluck('name')->join(', ') : '') }}"
                       class="w-full border rounded-xl px-3 py-2 outline-[#1e3a5f]"
                       placeholder="Pendidikan, Nias, ...">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Gambar Sampul (maks. 2MB)</label>
            <input type="file" name="image" accept="image/*" class="text-sm">
            @if(isset($blog) && $blog->image)
            <img src="{{ asset('storage/'.$blog->image) }}" class="mt-2 h-24 rounded-lg object-cover" alt="">
            @endif
        </div>

        {{-- Tiptap Editor --}}
        <div>
            <label class="block text-sm font-medium mb-1">Konten</label>
            <input type="hidden" name="content" id="content-input">
            <div id="editor" class="border rounded-xl overflow-hidden">
                <div id="editor-menu" class="flex flex-wrap gap-1 border-b p-2 bg-gray-50"></div>
                <div id="editor-content" class="tiptap p-4 min-h-[300px]"></div>
            </div>
        </div>

        <button type="submit"
                class="w-full py-3 bg-[#1e3a5f] text-white rounded-xl font-semibold hover:bg-[#2d5282] transition">
            {{ isset($blog) ? 'Perbarui Blog' : 'Terbitkan Blog' }}
        </button>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function() {
    const initialContent = @json(isset($blog) ? $blog->content : '');

    const editor = new TiptapEditor({
        element: document.getElementById('editor-content'),
        extensions: [StarterKit],
        content: initialContent,
        editorProps: { attributes: { class: 'tiptap outline-none min-h-[300px] p-4 prose max-w-none' } },
        onUpdate({ editor }) {
            document.getElementById('content-input').value = editor.getHTML();
        },
    });

    // Set initial value
    document.getElementById('content-input').value = editor.getHTML();

    // Menu bar
    const menu = document.getElementById('editor-menu');
    const addBtn = (label, action, isActive) => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.textContent = label;
        btn.className = 'px-2 py-1 text-sm rounded hover:bg-gray-100';
        btn.addEventListener('click', () => { action(); });
        menu.appendChild(btn);
    };
    addBtn('B', () => editor.chain().focus().toggleBold().run());
    addBtn('I', () => editor.chain().focus().toggleItalic().run());
    addBtn('H2', () => editor.chain().focus().toggleHeading({ level: 2 }).run());
    addBtn('List', () => editor.chain().focus().toggleBulletList().run());
    addBtn('Quote', () => editor.chain().focus().toggleBlockquote().run());
})();
</script>
@endpush
