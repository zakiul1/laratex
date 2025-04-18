<!-- Top Ribbon -->
@php
    use App\Models\SiteSetting;
    use App\Models\Menu;
    use App\Models\ThemeSection;


    $headerMenu = Menu::where('location', 'header')->with(['items.post'])->first();


    $site = SiteSetting::first();
    $logofrom = ThemeSection::first();


@endphp

@if ($themeSettings?->show_ribbon)
    @include('partials.ribbon')
@endif




<!-- Main Navbar -->
<div class="bg-gray-100 text-gray-800 ">
    <div class="container mx-auto px-4 py-4 flex items-center justify-between">
    <!-- Logo -->
 
@php
$themeSettings = getThemeSetting();
@endphp

<a href="/" class="inline-block">
    @if ($site && $themeSettings->logo && Storage::disk('public')->exists($themeSettings->logo))
        <img src="{{ asset('storage/' . $themeSettings->logo) }}" alt="Site Logo" class="h-10">
    @else
        <span class="text-xl font-bold">{{ $site->site_name ?? config('app.name', 'Siatex') }}</span>
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