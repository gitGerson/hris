{{-- font-size is the logo height, so the mark and the text scale together. --}}
<div class="flex items-center gap-[0.2em]" style="font-size: {{ $height }}">
    <img src="{{ asset('image/logo/1.png') }}" alt="{{ filament()->getBrandName() }}" class="h-[1em] w-auto" />
    @if ($showText)
        <span class="text-[0.42em] font-bold leading-none tracking-tight">HRIS</span>
    @endif
</div>
