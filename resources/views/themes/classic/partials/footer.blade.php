@php
    use App\Models\Widget;
    use App\Models\Category;
    use App\Models\Menu;

    $footerWidgets = Widget::where('widget_area', 'footer')
        ->where('status', true)
        ->orderBy('order')
        ->take(4)
        ->get();
@endphp

@if ($footerWidgets->isNotEmpty())
    <footer class="bg-neutral-900 text-white py-12">
        <div
            class="max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-2 md:grid-cols-{{ $footerWidgets->count() }} gap-8 px-4">

            @foreach ($footerWidgets as $widget)
                <div>
                    <h3 class="font-bold text-lg mb-4">{{ $widget->title }}</h3>

                    {{-- Menu Widget --}}
                    @if ($widget->widget_type === 'menu')
                        @php
                            $menu = Menu::with('items')->find($widget->content);
                           @dd($widget);
                        @endphp
                        @if ($menu && $menu->items->count())
                            <ul class="space-y-2 text-sm">
                                @foreach ($menu->items as $item)
                                    <li>
                                        <a href="{{ url($item->url) }}" class="hover:underline">{{ $item->title }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        {{-- Text Widget --}}
                    @elseif ($widget->widget_type === 'text')
                        <div class="text-sm space-y-1">
                            @foreach (explode("\n", $widget->content) as $line)
                                @if (trim($line))
                                    <p>{{ trim($line) }}</p>
                                @endif
                            @endforeach
                        </div>

                        {{-- Category Widget --}}
                    @elseif ($widget->widget_type === 'category')
                        @php
                            $category = Category::where('slug', $widget->content)->with('children')->first();
                        @endphp
                        @if ($category && $category->children->count())
                            <ul class="space-y-2 text-sm">
                                @foreach ($category->children as $child)
                                    <li>
                                        <a href="{{ url('category/' . $child->slug) }}" class="hover:underline">
                                            {{ $child->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    @endif
                </div>
            @endforeach

        </div>
    </footer>
@endif