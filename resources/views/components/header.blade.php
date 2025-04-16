<!-- Top Ribbon -->
@php
use App\Models\SiteSetting;
use App\Models\Menu;


$headerMenu = Menu::where('location', 'header')->with(['items.post'])->first();


$site = SiteSetting::first();


@endphp
@if($site && $site->show_ribbon)
    <div class="text-white text-sm py-2"
        style="background-color: {{ $site->ribbon_bg_color ?? '#0a4b78' }}; color: {{ $site->ribbon_text_color ?? '#ffffff' }};">
        <div class="container mx-auto px-4 flex justify-between items-center flex-wrap gap-4">

            {{-- Left Text --}}
            <div class="flex-1">
                {{ $site->ribbon_left_text ?? '' }}
            </div>

            {{-- Right Contact Info (Phone and Email) --}}
            <div class="flex items-center gap-4 flex-wrap text-white">
                {{-- Phone --}}
                @if($site?->ribbon_phone)
                    <span class="flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M6.62 10.79a15.533 15.533 0 006.59 6.59l2.2-2.2a1.003 1.003 0 011.11-.21c1.21.49 2.53.76 3.91.76a1 1 0 011 1V20a1 1 0 01-1 1c-9.39 0-17-7.61-17-17a1 1 0 011-1h3.5a1 1 0 011 1c0 1.38.27 2.7.76 3.91a1.003 1.003 0 01-.21 1.11l-2.2 2.2z">
                            </path>
                        </svg>
                        {{ $site->ribbon_phone }}
                    </span>
                @endif

                {{-- Email --}}
                @if($site?->ribbon_email)
                    <span class="flex items-center gap-1">
                        <svg aria-hidden="true" data-prefix="far" data-icon="envelope" class="w-4 h-4 text-white"
                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                            <path fill="currentColor"
                                d="M464 64H48C21.49 64 0 85.49 0 112v288c0 26.51 21.49 48 48 48h416c26.51 0 48-21.49 48-48V112c0-26.51-21.49-48-48-48zm0 48v40.805c-22.422 18.259-58.168 46.651-134.587 106.49-16.841 13.247-50.201 45.072-73.413 44.701-23.208.375-56.579-31.459-73.413-44.701C106.18 199.465 70.425 171.067 48 152.805V112h416zM48 400V214.398c22.914 18.251 55.409 43.862 104.938 82.646 21.857 17.205 60.134 55.186 103.062 54.955 42.717.231 80.509-37.199 103.053-54.947 49.528-38.783 82.032-64.401 104.947-82.653V400H48z">
                            </path>
                        </svg>
                        <a class="r-mail" style="color: {{ $site->ribbon_text_color ?? '#ffffff' }};"
                            href="mailto:{{ $site->ribbon_email }}">
                            {{ $site->ribbon_email }}
                        </a>
                    </span>
                @endif
            </div>
        </div>
    </div>
@endif



<!-- Main Navbar -->
<div class="bg-gray-100 text-gray-800 ">
    <div class="container mx-auto px-4 py-4 flex items-center justify-between">
    <!-- Logo -->
 
    <a href="{{ url('/') }}" class="text-white font-bold text-lg flex items-center">
        @if ($site && $site->logo && Storage::disk('public')->exists($site->logo))
            <img src="{{ asset('storage/' . $site->logo) }}" alt="{{ $site->site_name }} Logo" class="h-10">
        @else
            <span>{{ $site->site_name ?? config('app.name', 'WORKYIND') }}</span>
        @endif
    </a>


        <!-- Navigation -->
    
<nav class="hidden md:flex items-center gap-6 text-sm uppercase font-semibold">
    @if ($headerMenu && $headerMenu->items)
        @foreach ($headerMenu->items as $item)
            <div class="relative group">
                @php
                    $link = '#';

                    if ($item->type === 'custom') {
                        $link = $item->url;
                    } elseif ($item->type === 'page' && $item->reference_id) {
                        $post = \App\Models\Post::find($item->reference_id);
                        if ($post) {
                            $link = route('page.show', $post->slug);
                        }
                    } elseif ($item->type === 'post' && $item->reference_id) {
                        $post = \App\Models\Post::find($item->reference_id);
                        if ($post) {
                            $link = route('posts.show', $post->slug);
                        }
                    } elseif ($item->type === 'category' && $item->reference_id) {
                        $category = \App\Models\Category::find($item->reference_id);
                        if ($category) {
                            $link = route('category.show', $category->slug);
                        }
                    }
                @endphp

                <a href="{{$item->url }}" class="hover:text-red-500">
                    {{ $item->title }}
                </a>

                @if (!empty($item->children))
                    <div
                        class="absolute left-0 top-full mt-2 w-40 bg-white text-black shadow-lg rounded hidden group-hover:block z-50">
                        @foreach ($item->children as $child)
                            @php
                                $childLink = '#';

                                if ($child->type === 'custom') {
                                    $childLink = $child->url;
                                } elseif ($child->type === 'page' && $child->reference_id) {
                                    $post = \App\Models\Post::find($child->reference_id);
                                    if ($post) {
                                        $childLink = route('page.show', $post->slug);
                                    }
                                } elseif ($child->type === 'post' && $child->reference_id) {
                                    $post = \App\Models\Post::find($child->reference_id);
                                    if ($post) {
                                        $childLink = route('posts.show', $post->slug);
                                    }
                                } elseif ($child->type === 'category' && $child->reference_id) {
                                    $category = \App\Models\Category::find($child->reference_id);
                                    if ($category) {
                                        $childLink = route('category.show', $category->slug);
                                    }
                                }
                            @endphp

                            <a href="{{ $childLink }}" class="block px-4 py-2 text-sm hover:bg-gray-100">
                                {{ $child->title }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    @endif
</nav>


        <!-- Actions -->
        <div class="flex items-center gap-4">
            <button class="text-white hover:text-red-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-4.35-4.35M16.65 16.65A7.5 7.5 0 1116.65 2.5a7.5 7.5 0 010 14.15z" />
                </svg>
            </button>

            <a href="#" class="bg-red-600 px-4 py-1.5 text-sm rounded text-white font-medium hover:bg-red-700">
                Inquiry
            </a>
        </div>
    </div>
</div>