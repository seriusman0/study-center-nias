<div class="nametag nametag-photo" style="width:{{ $width }}cm;height:{{ $height }}cm">
    <div class="corner-tr">
        <svg viewBox="0 0 60 50" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <polygon points="60,0 30,0 60,30" fill="#e07a25"/>
            <polygon points="60,30 45,15 60,15" fill="#1e6b3a"/>
            <polygon points="30,0 45,0 35,12" fill="#c9a84c"/>
        </svg>
    </div>

    {{-- Header --}}
    <div class="head">
        <img src="{{ asset('assets/img/logo.png') }}" alt="Logo" class="logo" onerror="this.style.display='none'">
        <div class="titles">
            <div class="title-main">STUDY CENTER</div>
            <div class="title-sub">STUDY CENTER KABUPATEN NIAS</div>
            <div class="title-tag">SECOND HOME FOR THE BETTER FUTURE</div>
        </div>
    </div>
    <div class="divider"></div>

    {{-- Body: foto kiri, info kanan --}}
    <div class="photo-body">
        {{-- Foto siswa (3:4 ratio) --}}
        <div class="photo-box">
            @if($sp?->photo)
                <img src="{{ asset('storage/' . $sp->photo) }}" alt="{{ $s->name }}" class="photo-img">
            @else
                <div class="photo-placeholder">
                    <span>Tempel<br>Foto</span>
                </div>
            @endif
        </div>

        {{-- Info --}}
        <div class="info-col">
            <div class="info-name">{{ $s->name }}</div>
            @if($sp?->grade_class)
            <div class="info-row">
                <span class="info-label">Kelas</span>
                <span class="info-sep">:</span>
                <span class="info-val">{{ $sp->grade_class }}</span>
            </div>
            @endif
            @if($sp?->school_name)
            <div class="info-row">
                <span class="info-label">Sekolah</span>
                <span class="info-sep">:</span>
                <span class="info-val">{{ $sp->school_name }}</span>
            </div>
            @endif
            @if($s->cabang?->nama)
            <div class="info-row">
                <span class="info-label">Cabang</span>
                <span class="info-sep">:</span>
                <span class="info-val">{{ $s->cabang->nama }}</span>
            </div>
            @endif
        </div>
    </div>
</div>
