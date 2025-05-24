@php
    use App\Models\Widget;
    use App\Models\Category;
    use App\Models\Menu;

    $footerWidgets = Widget::where('widget_area', 'footer')->where('status', true)->orderBy('order')->take(4)->get();

    // Grab whatever the user set in Theme Customizer, or default to © YEAR APP_NAME
    $footerText = data_get(
        $themeSettings->options,
        'footer_text',
        '© ' . date('Y') . ' ' . config('app.name') . '. Powered by Zedaxe & Hostwires',
    );
@endphp

@if ($footerWidgets->isNotEmpty())
    <footer class=" text-white font-[oswald]">
        <div class="bg-[#262626] pb-12">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8  py-12">

            </div>
            <div class="container mx-auto px-0 sm:px-4 md:px-6 lg:px-8">
                @php
                    $count = $footerWidgets->count();
                    $smCols = min($count, 2);
                    $mdCols = min($count, 3);
                    $lgCols = min($count, 4);
                    $gridColsClass = "grid grid-cols-1 sm:grid-cols-{$smCols} md:grid-cols-{$mdCols} lg:grid-cols-{$lgCols} gap-8 justify-items-center sm:justify-items-start";
                @endphp
                <div class="{{ $gridColsClass }}">
                    @foreach ($footerWidgets as $widget)
                        <div class="w-full">
                            <h3 class="font-bold text-lg sm:text-xl mb-4 text-center sm:text-left">{{ $widget->title }}
                            </h3>

                            {{-- Menu Widget --}}
                            @if ($widget->widget_type === 'menu')
                                @php
                                    $menu = Menu::with('items')->find($widget->content);
                                @endphp
                                @if ($menu && $menu->items->count())
                                    <ul class="space-y-2 text-sm sm:text-base text-center sm:text-left">
                                        @foreach ($menu->items as $item)
                                            <li>
                                                <a href="{{ url($item->url) }}" class="hover:underline">
                                                    {{ $item->title }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif

                                {{-- Text Widget --}}
                            @elseif ($widget->widget_type === 'text')
                                <div class="text-sm sm:text-base space-y-1 text-center sm:text-left">
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
                                    <ul class="space-y-2 text-sm sm:text-base text-center sm:text-left">
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
            </div>
        </div>


        <div class="bg-neutral-900 pt-4 pb-12">
            <div class="container mx-auto px-4 flex flex-col sm:flex-row items-center justify-between">
                {{-- Dynamic footer text --}}
                <div class="text-sm text-center sm:text-left">
                    {!! $footerText !!}
                </div>


            </div>
        </div>
    </footer>
@endif
