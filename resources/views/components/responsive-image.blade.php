{{-- resources/views/components/responsive-image.blade.php --}}
<picture>
    {{-- 1) AVIF source (if avifSrcset is not empty) --}}
    @if (!empty($avifSrcset))
        <source type="image/avif" srcset="{{ $avifSrcset }}" sizes="{{ $sizes }}">
    @endif

    {{-- 2) WebP source (if webpSrcset is not empty) --}}
    @if (!empty($webpSrcset))
        <source type="image/webp" srcset="{{ $webpSrcset }}" sizes="{{ $sizes }}">
    @endif

    {{-- 3) Fallback <img> --}}
    <img src="{{ $fallback }}" srcset="{{ $avifSrcset || $webpSrcset ? '' : '' }}" sizes="{{ $sizes }}"
        @if ($loading) loading="{{ $loading }}" @endif
        @if ($fetchpriority) fetchpriority="{{ $fetchpriority }}" @endif
        alt="{{ $media->getCustomProperty('alt') ?? '' }}"
        width="{{ optional($media->getCustomProperty('width'))->__toString() ?? '' }}"
        height="{{ optional($media->getCustomProperty('height'))->__toString() ?? '' }}"
        class="{{ $attributes->get('class') }}">
</picture>
