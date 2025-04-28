@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
@endphp

<div class="seosearch-wrapper {{ $attrs['c_class'] }} style-{{ $attrs['style'] }}">
    <div
        class="grid
      grid-cols-{{ $attrs['column'] }}
      md:grid-cols-{{ $attrs['tcol'] }}
      sm:grid-cols-{{ $attrs['mcol'] }}
      gap-6">

        @foreach ($posts as $post)
            @switch($attrs['style'])
                {{-- Style: imgsrnk (Page 1) --}}
                @case('imgsrnk')
                    <div class="seosearch-item bg-white rounded shadow p-4">
                        @if ($attrs['img'] && ($first = $post->featured_image_ids[0] ?? null))
                            @php $url = Storage::url(\App\Models\Media::find($first)->path); @endphp
                            <img src="{{ $url }}" class="w-full h-48 object-cover rounded mb-4">
                        @endif

                        <h3 class="text-xl font-bold mb-2">{{ $post->title }}</h3>

                        @if ($attrs['excerpt_hide'])
                            <p class="text-gray-700 mb-4">
                                {{ Str::limit(strip_tags($post->content), $attrs['excerpt_hide']) }}
                            </p>
                        @endif

                        @if ($attrs['get_price'])
                            <button class="inline-block bg-blue-600 text-white px-4 py-2 rounded mb-2">
                                Get Price
                            </button>
                        @endif

                        <a href="{{ url($post->type . '/' . $post->slug) }}" class="text-blue-600 hover:underline">Read More</a>
                    </div>
                @break

                {{-- Style: img-right (Page 4) --}}
                @case('img-right')
                    <div class="seosearch-item bg-white rounded shadow p-4 flex items-center">
                        <div class="flex-1 pr-4">
                            <h3 class="text-xl font-semibold mb-2">{{ $post->title }}</h3>
                            @if ($attrs['excerpt_hide'])
                                <p class="text-gray-700 mb-4">
                                    {{ Str::limit(strip_tags($post->content), $attrs['excerpt_hide']) }}
                                </p>
                            @endif
                            <a href="{{ url($post->type . '/' . $post->slug) }}" class="text-blue-600 underline">Read More</a>
                        </div>
                        @if ($attrs['img'] && ($first = $post->featured_image_ids[0] ?? null))
                            @php $url = Storage::url(\App\Models\Media::find($first)->path); @endphp
                            <img src="{{ $url }}" class="w-1/3 h-auto object-cover rounded">
                        @endif
                    </div>
                @break

                {{-- Style: widget-post (Page 2 variant) --}}
                @case('widget-post')
                    <div class="seosearch-item bg-indigo-50 rounded shadow-lg p-6">
                        @if ($attrs['img'] && ($first = $post->featured_image_ids[0] ?? null))
                            @php $url = Storage::url(\App\Models\Media::find($first)->path); @endphp
                            <img src="{{ $url }}" class="w-full h-64 object-cover rounded mb-4">
                        @endif
                        <h3 class="text-2xl font-semibold mb-2">{{ $post->title }}</h3>
                        @if ($attrs['excerpt_hide'])
                            <p class="text-gray-600 mb-4">
                                {{ Str::limit(strip_tags($post->content), $attrs['excerpt_hide']) }}
                            </p>
                        @endif
                    </div>
                @break

                {{-- Default fallback --}}

                @default
                    <div class="seosearch-item bg-white rounded shadow p-4">
                        <h3 class="text-lg font-semibold mb-2">{{ $post->title }}</h3>
                        <a href="{{ url($post->type . '/' . $post->slug) }}" class="text-blue-600 hover:underline">Read
                            More</a>
                    </div>
            @endswitch
        @endforeach

    </div>
</div>
