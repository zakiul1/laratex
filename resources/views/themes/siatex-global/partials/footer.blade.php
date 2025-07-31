@php
    use App\Models\Widget;
    use App\Models\Category;
    use App\Models\Menu;

    // Fetch all footer widgets in order
    $widgets = Widget::where('widget_area', 'footer')->where('status', true)->orderBy('order')->get();

    // Chunk them into rows of up to 3 widgets each
    $rows = $widgets->chunk(3);

    // Footer text fallback
    $footerText = data_get(
        $themeSettings->options,
        'footer_text',
        '© ' . date('Y') . ' All Rights Reserved SIATEX Global.',
    );
@endphp

@if ($widgets->isNotEmpty())
    <footer class="bg-[#2D3038] text-gray-300 py-12 mt-12">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">

            @foreach ($rows as $row)
                @php
                    $n = $row->count();
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
                            {{-- Widget title with red underline --}}
                            <h3 class="text-white font-semibold text-lg mb-4 inline-block relative">
                                {{ $widget->title }}
                                <span class="absolute left-0 bottom-0 w-10 h-0.5 bg-red-500"></span>
                            </h3>

                            @if ($widget->widget_type === 'menu')
                                @php $menu = Menu::with('items')->find($widget->content); @endphp
                                @if ($menu && $menu->items->count())
                                    <ul class="space-y-2 text-gray-300 text-sm">
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
                            @elseif($widget->widget_type === 'text')
                                <div class="space-y-2 text-gray-300 text-sm">
                                    @foreach (explode("\n", $widget->content) as $line)
                                        @if (trim($line))
                                            <p>{{ trim($line) }}</p>
                                        @endif
                                    @endforeach
                                </div>

                                {{-- Social icons under the first text widget --}}
                                @if ($loop->first)
                                    <div class="flex space-x-4 mt-6">
                                        <a href="#"
                                            class="w-8 h-8 flex items-center justify-center bg-gray-700 rounded-full hover:bg-gray-600 transition">
                                            <x-lucide-facebook class="w-4 h-4 text-white" />
                                        </a>
                                        <a href="#"
                                            class="w-8 h-8 flex items-center justify-center bg-gray-700 rounded-full hover:bg-gray-600 transition">
                                            <x-lucide-linkedin class="w-4 h-4 text-white" />
                                        </a>
                                        <a href="#"
                                            class="w-8 h-8 flex items-center justify-center bg-gray-700 rounded-full hover:bg-gray-600 transition">
                                            <x-lucide-twitter class="w-4 h-4 text-white" />
                                        </a>
                                        <a href="#"
                                            class="w-8 h-8 flex items-center justify-center bg-gray-700 rounded-full hover:bg-gray-600 transition">
                                            <x-lucide-instagram class="w-4 h-4 text-white" />
                                        </a>
                                    </div>
                                @endif
                            @elseif($widget->widget_type === 'category')
                                @php
                                    $category = Category::where('slug', $widget->content)->with('children')->first();
                                @endphp
                                @if ($category && $category->children->count())
                                    <ul class="space-y-2 text-gray-300 text-sm">
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

            {{-- Footer bottom --}}
            <div class="border-t border-gray-700 pt-6">
                <div class="text-center text-gray-500 text-sm">
                    {!! $footerText !!}
                </div>
            </div>
        </div>
    </footer>
@endif
