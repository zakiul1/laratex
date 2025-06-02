{{-- resources/views/components/responsive-image.blade.php --}}
<picture>
    {{-- 1) AVIF source (if available) --}}
    @if (!empty($avifSrcset))
        <source type="image/avif" srcset="{{ $avifSrcset }}" sizes="{{ $sizes }}">
    @endif

    {{-- 2) WebP source (if available) --}}
    @if (!empty($webpSrcset))
        <source type="image/webp" srcset="{{ $webpSrcset }}" sizes="{{ $sizes }}">
    @endif

    {{-- 3) JPEG/PNG fallback --}}
    <img src="{{ $fallback }}"
        srcset="{{ // If you want to include JPEG/PNG srcset, build it here.
            // For simplicity, you could omit srcset or reconstruct it similarly.
            '' }}"
        sizes="{{ $sizes }}" @if ($loading) loading="{{ $loading }}" @endif
        @if ($fetchpriority) fetchpriority="{{ $fetchpriority }}" @endif
        class="{{ $attributes->get('class') }}" alt="{{ $alt }}" {{-- ← explicitly render the alt attribute --}}>
</picture>
