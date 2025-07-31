{{-- resources/views/themes/siatexbd/templates/post.blade.php --}}
@extends('themes.siatexbd.layout')

@section('content')
    @php
        use Illuminate\Support\Str;

        // Grab the first featured image (if any)
        $media = $post->featuredMedia->first();
        $mediaUrl = $media ? $media->getUrl('large') : '';
        $detailUrl = route('posts.show', $post->slug);
    @endphp

    <div class="bg-white py-12 relative">
        @auth
            <a href="{{ route('posts.edit', $post) }}" class="text-xs px-3 py-3 rounded bg-green-500  text-white">
                Edit
            </a>
        @endauth

        <div class="container mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- Breadcrumb --}}
            <nav class="text-sm text-gray-700" aria-label="Breadcrumb">
                <ol class="flex flex-wrap space-x-2">
                    <li>
                        <a href="{{ route('home') }}" class="hover:underline text-gray-700">
                            Home
                        </a>
                    </li>
                    <li>/</li>

                    @if (isset($category) && $category?->term)
                        <li>
                            <a href="{{ route('categories.show', $category->term->slug) }}"
                                class="hover:underline text-gray-700">
                                {{ $category->term->name }}
                            </a>
                        </li>
                        <li>/</li>
                    @endif

                    <li class="font-semibold text-gray-900" aria-current="page">
                        {{ $post->title }}
                    </li>
                </ol>
            </nav>

            {{-- Post Detail Card --}}
            <div class="bg-gray-100 p-12">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">

                    {{-- Left Column: Title & Excerpt --}}
                    <div class="space-y-4">
                        <div class="w-16 h-1 bg-red-600"></div>

                        @if (isset($category) && $category?->term)
                            <h2 class="font-medium text-lg my-2 uppercase text-gray-700">
                                {{ $category->term->name }}
                            </h2>
                        @endif

                        <h2 class="text-3xl md:text-[32px] font-sans text-[#0e4f7f]">
                            {{ $post->title }}
                        </h2>

                        @if (!empty($post->excerpt))
                            <p class="text-gray-700 text-justify leading-relaxed">
                                {!! nl2br(e($post->excerpt)) !!}
                            </p>
                        @endif
                    </div>

                    {{-- Right Column: Featured Image --}}
                    @if ($media)
                        <div class="md:sticky md:top-4">
                            <div class="overflow-hidden" style="aspect-ratio:1/1;">
                                <a href="{{ $detailUrl }}" class="block w-full h-full">
                                    <x-responsive-image :media="$media" :breakpoints="[
                                        150 => 'thumbnail',
                                        300 => 'medium',
                                        480 => 'mobile',
                                        768 => 'tablet',
                                        1024 => 'large',
                                    ]"
                                        sizes="(max-width:768px) 100vw, 50vw" width="1024" height="1024" loading="lazy"
                                        class="w-full h-full object-contain" alt="{{ $post->title }}" />
                                </a>
                            </div>
                        </div>
                    @else
                        <div>
                            <div class="w-full h-80 flex items-center justify-center text-gray-400">
                                No image available
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Full Content --}}
            <div class="prose max-w-none">
                {!! apply_filters('the_content', $pageOutput) !!}
            </div>
        </div>
    </div>
@endsection
