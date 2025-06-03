{{-- resources/views/plugins/DynamicGrid/admin/builder.blade.php --}}
@extends('layouts.dashboard')

@section('content')
    <div class="py-8">
        <h1 class="text-2xl font-bold mb-6">Dynamic Grid Shortcode Builder</h1>
        <div class="bg-white p-6 shadow rounded-lg space-y-6">

            {{-- Generated Shortcode --}}
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

                {{-- 1. Taxonomy --}}
                <div class="mb-4">
                    <label class="block font-medium">Taxonomy</label>
                    <select id="taxonomy" name="taxonomy" class="w-full border p-2 rounded">
                        <option value="">— Select Taxonomy —</option>
                        @foreach ($taxonomies as $taxo)
                            @if ($taxo !== 'media_category')
                                <option value="{{ $taxo }}" {{ old('taxonomy') == $taxo ? 'selected' : '' }}>
                                    {{ ucwords(str_replace('_', ' ', $taxo)) }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>

                {{-- 2. Category --}}
                <div class="mb-4">
                    <label class="block font-medium">Category</label>
                    <select id="category" name="category_id" class="w-full border p-2 rounded">
                        <option value="">— Select Category —</option>
                    </select>
                </div>

                {{-- 3. Type & Layout --}}
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

                {{-- 4. Columns --}}
                <div id="columns_wrapper" class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    @foreach ($config['columns'] as $device => $cols)
                        <div>
                            <label class="block font-medium">{{ ucfirst($device) }} Columns</label>
                            <input type="number" name="columns[{{ $device }}]"
                                value="{{ old("columns.$device", $cols) }}" min="1"
                                class="w-full border p-2 rounded" />
                        </div>
                    @endforeach
                </div>

                {{-- 5. Excerpt Words --}}
                <div id="excerpt_wrapper" class="mb-4">
                    <label class="block font-medium">Excerpt Words</label>
                    <input type="number" name="excerpt_words" value="{{ old('excerpt_words', $config['excerpt_words']) }}"
                        min="0" class="w-full border p-2 rounded" />
                </div>

                {{-- 6. Show Image --}}
                <div id="show_image_wrapper" class="flex items-center mb-4">
                    <input type="checkbox" id="show_image" name="show_image" value="1"
                        {{ old('show_image', $config['show_image']) ? 'checked' : '' }} class="h-4 w-4 mr-2" />
                    <label for="show_image" class="font-medium">Show Image</label>
                </div>

                {{-- 7. Button Type --}}
                <div id="button_wrapper" class="mb-4">
                    <label class="block font-medium">Button Type</label>
                    <select id="button_type" name="button_type" class="w-full border p-2 rounded"></select>
                </div>

                {{-- 8. Heading --}}
                <div id="heading_wrapper" class="mb-6">
                    <label class="block font-medium">Heading (optional)</label>
                    <input type="text" name="heading" value="{{ old('heading', $config['heading']) }}"
                        class="w-full border p-2 rounded" />
                </div>

                {{-- 9. Post ID --}}
                <div id="post_id_wrapper" class="mb-6">
                    <label class="block font-medium">Post ID</label>
                    <input type="number" name="post_id" value="{{ old('post_id', $config['post_id']) }}"
                        class="w-full border p-2 rounded" />
                </div>

                {{-- 10. Products Amount --}}
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
            // Pass the PHP layouts array into JS
            const layouts = @json($layouts);

            // Predefined button‐type options
            const buttonOptions = {
                default: [{
                        value: 'none',
                        label: 'None'
                    },
                    {
                        value: 'read_more',
                        label: 'Read More'
                    },
                    {
                        value: 'price',
                        label: 'Price'
                    },
                ],
                priceOnly: [{
                        value: 'none',
                        label: 'None'
                    },
                    {
                        value: 'price',
                        label: 'Price'
                    },
                ],
                readMoreOnly: [{
                        value: 'none',
                        label: 'None'
                    },
                    {
                        value: 'read_more',
                        label: 'Read More'
                    },
                ],
            };

            // Grab references to the select fields and wrappers
            const typeSelect = document.getElementById('type');
            const layoutSelect = document.getElementById('layout');
            const buttonType = document.getElementById('button_type');

            // Wrapper elements for show/hide
            function toggle(id, show) {
                document.getElementById(id).style.display = show ? '' : 'none';
            }

            // Populate the button_type <select> based on chosen layout
            function populateButtons(opts) {
                buttonType.innerHTML = '';
                opts.forEach(o => {
                    const opt = new Option(o.label, o.value);
                    // Retain old() selection if present
                    if (o.value === '{{ old('button_type', $config['button_type']) }}') {
                        opt.selected = true;
                    }
                    buttonType.add(opt);
                });
            }

            // Show/hide logic whenever type or layout changes
            function handleTypeLayout() {
                const t = typeSelect.value;
                const l = layoutSelect.value;

                // ------ 1) feature_post logic ------
                if (t === 'feature_post') {
                    // For both layout1 and layout2 of feature_post:
                    toggle('button_wrapper', false);
                    toggle('columns_wrapper', false);
                    toggle('post_id_wrapper', true);
                    toggle('show_image_wrapper', true);
                    toggle('excerpt_wrapper', true); // excerpt is used for both feature layouts
                    return;
                }

                // ------ 2) single_post logic ------
                if (t === 'single_post') {
                    // Common hides/shows for any single_post
                    toggle('show_image_wrapper', false);
                    toggle('post_id_wrapper', false);
                    toggle('product_amount_wrapper', true);
                    toggle('columns_wrapper', false);

                    // Now show/hide excerpt + button selection based on layout
                    if (l === 'layout1') {
                        toggle('excerpt_wrapper', false);
                        toggle('button_wrapper', true);
                        populateButtons(buttonOptions.priceOnly);
                    } else if (l === 'layout2') {
                        toggle('excerpt_wrapper', true);
                        toggle('button_wrapper', true);
                        populateButtons(buttonOptions.readMoreOnly);
                    } else {
                        toggle('excerpt_wrapper', false);
                        toggle('button_wrapper', true);
                        populateButtons(buttonOptions.default);
                    }
                    return;
                }

                // ------ 3) all other “widget” or custom types ------
                toggle('button_wrapper', true);
                toggle('show_image_wrapper', true);
                toggle('post_id_wrapper', true);
                toggle('product_amount_wrapper', false);
                toggle('columns_wrapper', false);
                toggle('excerpt_wrapper', false);
                populateButtons(buttonOptions.default);
            }

            // Whenever “Type” changes, rebuild the “Layout” options
            typeSelect.addEventListener('change', () => {
                layoutSelect.innerHTML = '';
                Object.entries(layouts[typeSelect.value] || {}).forEach(([k, label]) => {
                    layoutSelect.add(new Option(label, k));
                });
                handleTypeLayout();
            });

            // Whenever “Layout” changes, re-run show/hide logic
            layoutSelect.addEventListener('change', handleTypeLayout);

            // Run once on page load
            handleTypeLayout();

            // Copy‐to‐Clipboard for the generated shortcode textarea
            document.getElementById('copy-btn')?.addEventListener('click', () => {
                const ta = document.getElementById('shortcode');
                ta.select();
                document.execCommand('copy');
                const btn = document.getElementById('copy-btn');
                btn.textContent = 'Copied!';
                setTimeout(() => btn.textContent = 'Copy', 1500);
            });

            // AJAX: load categories when taxonomy changes
            const taxonomyEl = document.getElementById('taxonomy');
            const categoryEl = document.getElementById('category');
            const baseUrl = "{{ url('admin/plugins/dynamicgrid/categories') }}";
            const oldCat = "{{ old('category_id', '') }}";

            async function loadCategories(tax) {
                categoryEl.innerHTML = '<option>Loading…</option>';
                if (!tax) {
                    categoryEl.innerHTML = '<option value="">— Select Category —</option>';
                    return;
                }
                try {
                    const res = await fetch(`${baseUrl}/${encodeURIComponent(tax)}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const cats = await res.json();
                    let html = '<option value="">— Select Category —</option>';
                    if (cats.length) {
                        cats.forEach(c => {
                            html += `
                                <option
                                    value="${c.id}"
                                    ${c.id == oldCat ? 'selected' : ''}
                                >
                                    ${c.name}
                                </option>`;
                        });
                    } else {
                        html = '<option value="">— No categories —</option>';
                    }
                    categoryEl.innerHTML = html;
                } catch {
                    categoryEl.innerHTML = '<option value="">— Error loading —</option>';
                }
            }

            taxonomyEl.addEventListener('change', e => loadCategories(e.target.value));

            // On page load, if old('taxonomy') exists, fire one AJAX fetch
            @if (old('taxonomy'))
                loadCategories("{{ old('taxonomy') }}");
            @endif
        });
    </script>
@endpush
