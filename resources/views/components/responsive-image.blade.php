{{-- resources/views/components/responsive-image.blade.php --}}
<picture>
    {{-- 1) If AVIF conversions exist, emit an <source> for type="image/avif" --}}
    @if (!empty($avifSrcset))
        <source type="image/avif" srcset="{{ $avifSrcset }}" sizes="{{ $sizes }}" />
    @endif

    {{-- 2) If WebP conversions exist, emit an <source> for type="image/webp" --}}
    @if (!empty($webpSrcset))
        <source type="image/webp" srcset="{{ $webpSrcset }}" sizes="{{ $sizes }}" />
    @endif

    {{-- 3) Finally, fallback <img> --}}
    <img src="{{ $fallback }}" sizes="{{ $sizes }}" {{-- Merge any other attributes passed to <x-responsive-image>, e.g. alt, loading, class, width, height, aria-* etc. --}}
        {{ $attributes->merge([
            'alt' => $attributes->get('alt', ''),
            'loading' => $attributes->get('loading', 'lazy'),
            'fetchpriority' => $attributes->get('fetchpriority', 'low'),
            'width' => $attributes->get('width', 'auto'),
            'height' => $attributes->get('height', 'auto'),
            'class' => $attributes->get('class', ''),
        ]) }} />
</picture>
