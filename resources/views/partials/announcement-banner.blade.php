@if(isset($announcements) && $announcements->isNotEmpty())
@php
$colors = [
    'info'    => ['bg' => '#dbeafe', 'border' => '#3b82f6', 'text' => '#1e3a8a', 'icon' => 'fa-info-circle'],
    'success' => ['bg' => '#dcfce7', 'border' => '#22c55e', 'text' => '#14532d', 'icon' => 'fa-check-circle'],
    'warning' => ['bg' => '#fef9c3', 'border' => '#eab308', 'text' => '#713f12', 'icon' => 'fa-exclamation-triangle'],
    'danger'  => ['bg' => '#fee2e2', 'border' => '#ef4444', 'text' => '#7f1d1d', 'icon' => 'fa-times-circle'],
];
@endphp
<div id="ann-wrapper">
    @foreach($announcements as $ann)
    @php $c = $colors[$ann->type] ?? $colors['info']; @endphp
    <div class="ann-item" data-ann-id="{{ $ann->id }}"
         style="background:{{ $c['bg'] }};border-bottom:2px solid {{ $c['border'] }};padding:.6rem 1rem;display:flex;align-items:flex-start;gap:.75rem;position:relative">
        <i class="fas {{ $c['icon'] }}" style="color:{{ $c['border'] }};font-size:1rem;margin-top:.1rem;flex-shrink:0"></i>
        <div style="flex:1;min-width:0">
            <strong style="font-size:.82rem;color:{{ $c['text'] }}">{{ $ann->title }}</strong>
            <span style="font-size:.8rem;color:{{ $c['text'] }};opacity:.9;margin-left:.4rem">{{ $ann->content }}</span>
        </div>
        @auth
        <button onclick="dismissAnn({{ $ann->id }}, this.closest('.ann-item'))"
                style="background:none;border:none;cursor:pointer;color:{{ $c['text'] }};opacity:.6;font-size:1rem;padding:0 .25rem;flex-shrink:0;line-height:1"
                title="Tutup">
            <i class="fas fa-times"></i>
        </button>
        @endauth
    </div>
    @endforeach
</div>
@auth
<script>
function dismissAnn(id, el) {
    el.style.opacity = '.4';
    fetch('/announcements/' + id + '/dismiss', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    }).then(() => {
        el.style.transition = 'max-height .3s, opacity .3s, padding .3s';
        el.style.overflow = 'hidden';
        el.style.maxHeight = el.scrollHeight + 'px';
        requestAnimationFrame(() => {
            el.style.maxHeight = '0';
            el.style.opacity = '0';
            el.style.padding = '0';
        });
        setTimeout(() => el.remove(), 350);
    });
}
</script>
@endauth
@endif
