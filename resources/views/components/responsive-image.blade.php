<!-- resources/views/components/responsive-image.blade.php -->
<picture>
    {{-- 1) Serve AVIF if available: --}}
    @if (!empty($avifSrcset))
        <source type="image/avif" srcset="{{ $avifSrcset }}" sizes="{{ $sizes }}" />
    @endif

    {{-- 2) Then serve WebP if available: --}}
    @if (!empty($webpSrcset))
        <source type="image/webp" srcset="{{ $webpSrcset }}" sizes="{{ $sizes }}" />
    @endif

    {{-- 3) Finally, the <img> tag uses whichever fallback (AVIF or WebP or original) --}}
    <img src="{{ $fallback }}" sizes="{{ $sizes }}"
        {{ $attributes->merge([
            'alt' => $attributes['alt'] ?? '',
            'loading' => $attributes['loading'] ?? 'lazy',
        ]) }} />
</picture>
