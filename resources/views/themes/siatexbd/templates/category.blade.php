{{-- resources/views/themes/siatexbd/templates/category.blade.php --}}
@php
    use Illuminate\Support\Str;
@endphp

@extends('themes.siatexbd.layout')

@section('content')
    <div class="flex flex-col lg:flex-row container mx-auto px-4 sm:px-6 py-8 lg:space-x-6 space-y-6 lg:space-y-0">
        {{-- SIDEBAR --}}
        <aside class="w-full lg:w-64 hidden lg:block">
            <h2 class="text-base sm:text-lg md:text-xl font-bold mb-4">Categories</h2>
            <ul class="space-y-2">
                @foreach ($allCategories as $root)
                    @php
                        $isActiveRoot =
                            $root->term_taxonomy_id === $category->term_taxonomy_id ||
                            $category->parent === $root->term_taxonomy_id;
                    @endphp
                    <li>
                        <a href="{{ route('categories.show', $root->term->slug) }}"
                            class="block text-sm sm:text-base {{ $isActiveRoot ? 'font-semibold text-gray-900' : 'text-gray-700 hover:text-gray-900' }}">
                            {{ strtoupper($root->term->name) }}
                        </a>

                        @if ($isActiveRoot && $root->children->isNotEmpty())
                            <ul class="pl-4 mt-2 space-y-1">
                                @foreach ($root->children as $child)
                                    @php
                                        $isActiveChild = $child->term_taxonomy_id === $category->term_taxonomy_id;
                                    @endphp
                                    <li>
                                        <a href="{{ route('categories.show', $child->term->slug) }}"
                                            class="block text-sm sm:text-base {{ $isActiveChild ? 'font-semibold text-gray-900' : 'text-gray-600 hover:text-gray-800' }}">
                                            {{ strtoupper($child->term->name) }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>
        </aside>

        {{-- MAIN CONTENT --}}
        <div class="flex-1">
            {{-- Breadcrumb --}}
            <nav class="text-sm sm:text-base text-gray-500 mb-4" aria-label="Breadcrumb">
                <ol class="list-reset flex flex-wrap space-x-2">
                    <li><a href="{{ route('home') }}" class="hover:underline">Home</a></li>
                    @if ($category->parent)
                        <li>/</li>
                        <li>
                            <a href="{{ route('categories.show', $category->parentTerm->term->slug) }}"
                                class="hover:underline">
                                {{ $category->parentTerm->term->name }}
                            </a>
                        </li>
                    @endif
                    <li>/</li>
                    <li class="font-bold">{{ $category->term->name }}</li>
                </ol>
            </nav>

            {{-- Category Title --}}
            <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl uppercase font-extrabold mb-6">
                {{ strtoupper($category->term->name) }}
            </h1>

            {{-- Sub-categories --}}
            @if ($category->children->isNotEmpty())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($category->children as $sub)
                        @php
                            $subMedia = $sub->media->first();
                            $count = $sub->posts->count();
                        @endphp
                        <a href="{{ route('categories.show', $sub->term->slug) }}"
                            class="group block overflow-hidden rounded-lg shadow-lg">
                            @if ($subMedia)
                                <div class="w-full overflow-hidden" style="aspect-ratio:1/1;">
                                    <x-responsive-image :media="$subMedia" :breakpoints="[
                                        150 => 'thumbnail',
                                        300 => 'medium',
                                        480 => 'mobile',
                                        768 => 'tablet',
                                        1024 => 'large',
                                    ]"
                                        sizes="(max-width:640px) 100vw, (max-width:1024px) 50vw, 33vw" width="400"
                                        height="400" loading="lazy"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                        alt="{{ $sub->term->name }}" />
                                </div>
                            @else
                                <div
                                    class="w-full bg-gray-200 flex items-center justify-center text-gray-500 h-48 sm:h-56 md:h-64 lg:h-72">
                                    No Image
                                </div>
                            @endif

                            <div class="w-full text-center py-3 bg-white bg-opacity-80">
                                <h3 class="text-base sm:text-lg font-semibold">
                                    {{ strtoupper($sub->term->name) }}
                                </h3>
                                <p class="text-xs sm:text-sm text-gray-700">
                                    {{ $count }} {{ Str::plural('Product', $count) }}
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                {{-- ITEMS GRID --}}
                @if ($category->taxonomy === 'product')
                    {{-- Products --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($products as $product)
                            @php $prodMedia = $product->featuredMedia->first(); @endphp
                            <a href="{{ route('products.show', $product->slug) }}"
                                class="group block overflow-hidden rounded-lg shadow-lg">
                                @if ($prodMedia)
                                    <div class="w-full overflow-hidden" style="aspect-ratio:1/1;">
                                        <x-responsive-image :media="$prodMedia" :breakpoints="[
                                            150 => 'thumbnail',
                                            300 => 'medium',
                                            480 => 'mobile',
                                            768 => 'tablet',
                                            1024 => 'large',
                                        ]"
                                            sizes="(max-width:640px) 100vw, (max-width:1024px) 50vw, 33vw" width="400"
                                            height="400" loading="lazy"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                            alt="{{ $product->name }}" />
                                    </div>
                                @else
                                    <div
                                        class="w-full bg-gray-200 flex items-center justify-center text-gray-500 h-48 sm:h-56 md:h-64 lg:h-72">
                                        No Image
                                    </div>
                                @endif

                                <div class="w-full text-center py-3 bg-white bg-opacity-80">
                                    <h3 class="text-base sm:text-lg font-semibold">
                                        {{ $product->name }}
                                    </h3>
                                    <p class="text-xs sm:text-sm text-gray-700">
                                        ${{ number_format($product->price, 2) }}
                                    </p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    {{-- Posts --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($posts as $post)
                            <a href="{{ route('posts.show', $post->slug) }}"
                                class="group block overflow-hidden rounded-lg shadow-lg">
                                @if ($post->featuredMedia->first())
                                    <div class="w-full overflow-hidden" style="aspect-ratio:1/1;">
                                        <x-responsive-image :media="$post->featuredMedia->first()" :breakpoints="[
                                            150 => 'thumbnail',
                                            300 => 'medium',
                                            480 => 'mobile',
                                            768 => 'tablet',
                                            1024 => 'large',
                                        ]"
                                            sizes="(max-width:640px) 100vw, (max-width:1024px) 50vw, 33vw" width="400"
                                            height="400" loading="lazy"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                            alt="{{ $post->title }}" />
                                    </div>
                                @endif

                                <div class="w-full text-center py-3 bg-white bg-opacity-80">
                                    <h3 class="text-base sm:text-lg font-semibold">
                                        {{ $post->title }}
                                    </h3>
                                    <p class="text-xs sm:text-sm text-gray-700">
                                        {{ Str::limit(strip_tags($post->excerpt ?? $post->content), 100) }}
                                    </p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection
