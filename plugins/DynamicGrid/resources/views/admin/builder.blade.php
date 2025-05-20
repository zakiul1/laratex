{{-- resources/views/admin/dynamicgrid/builder.blade.php --}}
@extends('layouts.dashboard')

@section('content')
    <div class="py-8">
        <h1 class="text-2xl font-bold mb-6">Dynamic Grid Shortcode Builder</h1>
        <div class="bg-white p-6 shadow rounded-lg space-y-6">
            @if (session('shortcode'))
                <div class="space-y-2">
                    <label class="font-medium">Generated Shortcode</label>
                    <div class="relative">
                        <textarea id="shortcode" readonly class="w-full h-24 p-2 border rounded">{{ session('shortcode') }}</textarea>
                        <button id="copy-btn"
                            class="absolute top-2 right-2 bg-gray-200 hover:bg-gray-300 px-3 py-1 rounded text-sm">
                            Copy
                        </button>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.dynamicgrid.generate') }}">
                @csrf

                {{-- taxonomy --}}
                <div class="mb-4">
                    <label class="block font-medium">Taxonomy</label>
                    <select id="taxonomy" name="taxonomy" class="w-full border p-2 rounded">
                        @foreach ($taxonomies as $taxo)
                            <option value="{{ $taxo }}" {{ old('taxonomy') == $taxo ? 'selected' : '' }}>
                                {{ $taxo }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- category --}}
                <div class="mb-4">
                    <label class="block font-medium">Category</label>
                    <select id="category" name="category_id" class="w-full border p-2 rounded">
                        <option value="">— Select Category —</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- type & layout --}}
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block font-medium">Type</label>
                        <select id="type" name="type" class="w-full border p-2 rounded">
                            @foreach ($layouts as $tkey => $opts)
                                <option value="{{ $tkey }}"
                                    {{ old('type', $config['type']) == $tkey ? 'selected' : '' }}>
                                    {{ ucwords(str_replace('_', ' ', $tkey)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium">Layout</label>
                        <select id="layout" name="layout" class="w-full border p-2 rounded">
                            @foreach ($layouts[$config['type']] as $lkey => $label)
                                <option value="{{ $lkey }}"
                                    {{ old('layout', $config['layout']) == $lkey ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- columns (feature_post only) --}}
                <div id="columns_wrapper" class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    @foreach ($config['columns'] as $device => $cols)
                        <div>
                            <label class="block font-medium">{{ ucfirst($device) }} columns</label>
                            <input type="number" name="columns[{{ $device }}]"
                                value="{{ old("columns.$device", $cols) }}" min="1"
                                class="w-full border p-2 rounded" />
                        </div>
                    @endforeach
                </div>

                {{-- excerpt words --}}
                <div id="excerpt_wrapper" class="mb-4">
                    <label class="block font-medium">Excerpt Words</label>
                    <input type="number" name="excerpt_words" value="{{ old('excerpt_words', $config['excerpt_words']) }}"
                        min="0" class="w-full border p-2 rounded" />
                </div>

                {{-- show description (single_post/layout1 only) --}}
                <div id="description_wrapper" class="flex items-center mb-4">
                    <input type="checkbox" id="show_description" name="show_description" value="1"
                        {{ old('show_description', $config['show_description']) ? 'checked' : '' }} class="h-4 w-4 mr-2" />
                    <label for="show_description" class="font-medium">Show Description</label>
                </div>

                {{-- show image --}}
                <div id="show_image_wrapper" class="flex items-center mb-4">
                    <input type="checkbox" id="show_image" name="show_image" value="1"
                        {{ old('show_image', $config['show_image']) ? 'checked' : '' }} class="h-4 w-4 mr-2" />
                    <label for="show_image" class="font-medium">Show Image</label>
                </div>

                {{-- button type --}}
                <div id="button_wrapper" class="mb-4">
                    <label class="block font-medium">Button Type</label>
                    <select id="button_type" name="button_type" class="w-full border p-2 rounded"></select>
                </div>

                {{-- heading --}}
                <div id="heading_wrapper" class="mb-6">
                    <label class="block font-medium">Heading (optional)</label>
                    <input type="text" name="heading" value="{{ old('heading', $config['heading']) }}"
                        class="w-full border p-2 rounded" />
                </div>

                {{-- post ID --}}
                <div id="post_id_wrapper" class="mb-6">
                    <label class="block font-medium">Post ID</label>
                    <input type="number" name="post_id" value="{{ old('post_id', $config['post_id']) }}"
                        class="w-full border p-2 rounded" />
                </div>

                {{-- products amount --}}
                <div id="product_amount_wrapper" class="mb-6">
                    <label class="block font-medium">Products Amount</label>
                    <input type="number" name="product_amount"
                        value="{{ old('product_amount', $config['product_amount']) }}" min="1"
                        class="w-full border p-2 rounded" />
                </div>

                <button type="submit" class="w-full py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Generate Shortcode
                </button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const layouts = @json($layouts);
            const buttonOptions = {
                default: [{
                    value: 'none',
                    label: 'None'
                }, {
                    value: 'read_more',
                    label: 'Read More'
                }, {
                    value: 'price',
                    label: 'Price'
                }],
                priceOnly: [{
                    value: 'none',
                    label: 'None'
                }, {
                    value: 'price',
                    label: 'Price'
                }],
            };

            const typeSelect = document.getElementById('type');
            const layoutSelect = document.getElementById('layout');
            const buttonTypeSelect = document.getElementById('button_type');

            function toggle(id, show) {
                document.getElementById(id).style.display = show ? '' : 'none';
            }

            function populateButtonOptions(opts) {
                buttonTypeSelect.innerHTML = '';
                opts.forEach(o => {
                    const el = new Option(o.label, o.value);
                    // preserve old selection if present
                    if (o.value === '{{ old('button_type', $config['button_type']) }}') {
                        el.selected = true;
                    }
                    buttonTypeSelect.add(el);
                });
            }

            function handleTypeLayoutChange() {
                const t = typeSelect.value;
                const l = layoutSelect.value;

                if (t === 'single_post') {
                    // hide image & post_id for all single_post layouts
                    toggle('show_image_wrapper', false);
                    toggle('post_id_wrapper', false);
                    toggle('product_amount_wrapper', true);

                    // only for layout1 hide excerpt & show description; restrict button to priceOnly
                    if (l === 'layout1') {
                        toggle('excerpt_wrapper', false);
                        toggle('description_wrapper', true);
                        toggle('button_wrapper', true);
                        populateButtonOptions(buttonOptions.priceOnly);
                    } else {
                        // other single_post layouts
                        toggle('excerpt_wrapper', false);
                        toggle('description_wrapper', false);
                        toggle('button_wrapper', true);
                        populateButtonOptions(buttonOptions.default);
                    }

                    // columns never shown on single_post
                    toggle('columns_wrapper', false);
                } else if (t === 'feature_post') {
                    // feature_post uses columns, excerpt, default buttons
                    toggle('columns_wrapper', true);
                    toggle('excerpt_wrapper', true);
                    toggle('description_wrapper', false);
                    toggle('show_image_wrapper', true);
                    toggle('post_id_wrapper', true);
                    toggle('product_amount_wrapper', false);
                    toggle('button_wrapper', true);
                    populateButtonOptions(buttonOptions.default);
                } else {
                    // widget_post and others: minimal
                    toggle('columns_wrapper', false);
                    toggle('excerpt_wrapper', false);
                    toggle('description_wrapper', false);
                    toggle('show_image_wrapper', true);
                    toggle('post_id_wrapper', true);
                    toggle('product_amount_wrapper', false);
                    toggle('button_wrapper', true);
                    populateButtonOptions(buttonOptions.default);
                }
            }

            // rebuild layout-options when type changes
            typeSelect.addEventListener('change', () => {
                layoutSelect.innerHTML = '';
                Object.entries(layouts[typeSelect.value] || {}).forEach(([k, label]) => {
                    layoutSelect.add(new Option(label, k));
                });
                handleTypeLayoutChange();
            });
            // re-run toggles on layout change
            layoutSelect.addEventListener('change', handleTypeLayoutChange);

            // initial
            handleTypeLayoutChange();

            // copy shortcode
            document.getElementById('copy-btn')?.addEventListener('click', () => {
                const ta = document.getElementById('shortcode');
                ta.select();
                document.execCommand('copy');
                const btn = document.getElementById('copy-btn');
                btn.textContent = 'Copied!';
                setTimeout(() => btn.textContent = 'Copy', 1500);
            });
        });
    </script>
@endpush
