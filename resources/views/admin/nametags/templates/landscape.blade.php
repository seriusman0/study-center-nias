<div class="nametag nametag-landscape" style="width:{{ $width }}cm;height:{{ $height }}cm">
    <div class="corner-tr">
        <svg viewBox="0 0 60 50" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <polygon points="60,0 30,0 60,30" fill="#e07a25"/>
            <polygon points="60,30 45,15 60,15" fill="#1e6b3a"/>
            <polygon points="30,0 45,0 35,12" fill="#c9a84c"/>
        </svg>
    </div>
    <div class="corner-bl">
        <svg viewBox="0 0 60 50" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <polygon points="0,50 0,15 35,50" fill="#e07a25"/>
            <polygon points="0,50 25,50 0,30" fill="#1e6b3a"/>
        </svg>
    </div>

    {{-- Foto di kiri --}}
    <div class="ls-photo">
        @if($sp?->photo)
            <img src="{{ asset('storage/' . $sp->photo) }}" alt="{{ $s->name }}" class="photo-img-ls">
        @else
            <div class="photo-placeholder-ls">
                <span>Tempel<br>Foto</span>
            </div>
        @endif
    </div>

    {{-- Konten kanan --}}
    <div class="ls-content">
        <div class="ls-header">
            <img src="{{ asset('assets/img/logo.png') }}" alt="Logo" class="logo-sm" onerror="this.style.display='none'">
            <div class="ls-brand">
                <div class="ls-title">STUDY CENTER</div>
                <div class="ls-sub">KABUPATEN NIAS</div>
            </div>
        </div>
        <div class="ls-divider"></div>
        <div class="ls-name">{{ $s->name }}</div>
        <div class="ls-meta">
            @if($sp?->grade_class) <span class="ls-badge">{{ $sp->grade_class }}</span> @endif
            @if($sp?->school_name) <span class="ls-badge">{{ $sp->school_name }}</span> @endif
            @if($s->cabang?->nama)  <span class="ls-badge ls-badge-cabang">{{ $s->cabang->nama }}</span> @endif
        </div>
    </div>
</div>
