<div class="nametag" style="width:{{ $width }}cm;height:{{ $height }}cm">
    <div class="corner-tl"></div>
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
            <polygon points="0,15 0,5 18,50 12,50" fill="#c9a84c"/>
        </svg>
    </div>

    <div class="head">
        <img src="{{ asset('assets/img/logo.png') }}" alt="Logo" class="logo" onerror="this.style.display='none'">
        <div class="titles">
            <div class="title-main">STUDY CENTER</div>
            <div class="title-sub">STUDY CENTER KABUPATEN NIAS</div>
            <div class="title-tag">SECOND HOME FOR THE BETTER FUTURE</div>
        </div>
    </div>
    <div class="divider"></div>

    <div class="body">
        <div class="row-line">
            <span class="label">Nama</span><span class="colon">:</span>
            <span class="value">{{ $s->name }}</span>
        </div>
        <div class="row-line">
            <span class="label">Kelas</span><span class="colon">:</span>
            <span class="value">{{ $sp?->grade_class ?? '' }}</span>
        </div>
        <div class="row-line">
            <span class="label">Sekolah</span><span class="colon">:</span>
            <span class="value">{{ $sp?->school_name ?? '' }}</span>
        </div>
    </div>
</div>
