@php
    use App\Models\Widget;
    use App\Models\Category;
    use App\Models\Menu;

    // Fetch all footer widgets in order
    $widgets = Widget::where('widget_area', 'footer')->where('status', true)->orderBy('order')->get();

    // Chunk them into rows of up to 4 widgets each
    $rows = $widgets->chunk(3);

    // Footer text fallback
    $footerText = data_get(
        $themeSettings->options,
        'footer_text',
        '© ' . date('Y') . ' All rights reserved SiATEX Bangladesh, Canada, USA - 1987-2025',
    );
@endphp

@if ($widgets->isNotEmpty())
    <footer class="bg-gray-50 py-12 mt-12">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">

            @foreach ($rows as $row)
                @php
                    $n = $row->count();
                    // decide how many columns on sm+; leave blank if 1
                    if ($n >= 4) {
                        $colsClass = 'sm:grid-cols-4';
                    } elseif ($n === 3) {
                        $colsClass = 'sm:grid-cols-3';
                    } elseif ($n === 2) {
                        $colsClass = 'sm:grid-cols-2';
                    } else {
                        $colsClass = '';
                    }
                @endphp

                <div class="grid grid-cols-1 {{ $colsClass }} gap-8 mb-8">
                    @foreach ($row as $widget)
                        <div>
                            <h3 class="text-[#0F2F5A] font-semibold text-lg mb-4">
                                {{ $widget->title }}
                            </h3>

                            {{-- Menu Widget --}}
                            @if ($widget->widget_type === 'menu')
                                @php $menu = Menu::with('items')->find($widget->content); @endphp
                                @if ($menu && $menu->items->count())
                                    <ul class="space-y-2 text-gray-700 text-sm">
                                        @foreach ($menu->items as $item)
                                            <li class="flex items-start">
                                                <span class="mr-2">›</span>
                                                <a href="{{ url($item->url) }}" class="hover:underline">
                                                    {{ $item->title }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif

                                {{-- Text Widget --}}
                            @elseif($widget->widget_type === 'text')
                                <div class="space-y-1 text-gray-700 text-sm">
                                    @foreach (explode("\n", $widget->content) as $line)
                                        @if (trim($line))
                                            <p>{{ trim($line) }}</p>
                                        @endif
                                    @endforeach
                                </div>

                                {{-- Category Widget --}}
                            @elseif($widget->widget_type === 'category')
                                @php
                                    $category = Category::where('slug', $widget->content)->with('children')->first();
                                @endphp
                                @if ($category && $category->children->count())
                                    <ul class="space-y-2 text-gray-700 text-sm">
                                        @foreach ($category->children as $child)
                                            <li class="flex items-start">
                                                <span class="mr-2">›</span>
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
            @endforeach

            <div class="border-t border-gray-200 pt-6">
                <div class="text-gray-600 text-sm text-center">
                    {!! $footerText !!}
                </div>
            </div>
        </div>
    </footer>
@endif
