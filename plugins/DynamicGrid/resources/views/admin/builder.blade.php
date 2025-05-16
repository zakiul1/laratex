@extends('layouts.dashboard')

@section('content')
    <div class=" py-8">
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

                {{-- excerpt words (feature_post only) --}}
                <div id="excerpt_wrapper" class="mb-4">
                    <label class="block font-medium">Excerpt Words</label>
                    <input type="number" name="excerpt_words" value="{{ old('excerpt_words', $config['excerpt_words']) }}"
                        min="0" class="w-full border p-2 rounded" />
                </div>

                {{-- show image (all except single_post) --}}
                <div id="show_image_wrapper" class="flex items-center mb-6">
                    <input type="checkbox" id="show_image" name="show_image" value="1"
                        {{ old('show_image', $config['show_image']) ? 'checked' : '' }} class="h-4 w-4 mr-2" />
                    <label for="show_image" class="font-medium">Show Image</label>
                </div>

                {{-- button type (feature_post only) --}}
                <div id="button_wrapper" class="mb-4">
                    <label class="block font-medium">Button Type</label>
                    <select name="button_type" class="w-full border p-2 rounded">
                        <option value="none"
                            {{ old('button_type', $config['button_type']) == 'none' ? 'selected' : '' }}>None</option>
                        <option value="read_more"
                            {{ old('button_type', $config['button_type']) == 'read_more' ? 'selected' : '' }}>Read More
                        </option>
                        <option value="price"
                            {{ old('button_type', $config['button_type']) == 'price' ? 'selected' : '' }}>Price
                        </option>
                    </select>
                </div>

                {{-- heading (always) --}}
                <div id="heading_wrapper" class="mb-6">
                    <label class="block font-medium">Heading (optional)</label>
                    <input type="text" name="heading" value="{{ old('heading', $config['heading']) }}"
                        class="w-full border p-2 rounded" />
                </div>

                {{-- post ID (all except single_post) --}}
                <div id="post_id_wrapper" class="mb-6">
                    <label class="block font-medium">Post ID</label>
                    <input type="number" name="post_id" value="{{ old('post_id', $config['post_id']) }}"
                        class="w-full border p-2 rounded" />
                </div>

                {{-- new: products amount (single_post only) --}}
                <div id="product_amount_wrapper" class="mb-6" style="display:none;">
                    <label class="block font-medium">Products Amount</label>
                    <input type="number" name="product_amount"
                        value="{{ old('product_amount', $config['product_amount'] ?? '') }}" min="1"
                        class="w-full border p-2 rounded" />
                </div>

                <button type="submit" class="w-full py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Generate Shortcode
                </button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const layouts = @json($layouts);
            const typeSelect = document.getElementById('type');
            const layoutSelect = document.getElementById('layout');
            const copyBtn = document.getElementById('copy-btn');

            // 1) update layouts on type change
            typeSelect.addEventListener('change', () => {
                layoutSelect.innerHTML = '';
                Object.entries(layouts[typeSelect.value] || {}).forEach(([k, label]) => {
                    const o = new Option(label, k);
                    if (k === "{{ old('layout', $config['layout']) }}") o.selected = true;
                    layoutSelect.add(o);
                });
                handleTypeChange();
            });

            // 2) toggle wrappers
            function toggle(id, show) {
                document.getElementById(id).style.display = show ? '' : 'none';
            }

            function handleTypeChange() {
                const t = typeSelect.value;
                // single_post: hide image & post_id, show product_amount
                toggle('show_image_wrapper', t !== 'single_post');
                toggle('post_id_wrapper', t !== 'single_post');
                toggle('product_amount_wrapper', t === 'single_post');

                // feature_post: hide columns + excerpt + button
                toggle('columns_wrapper', t !== 'feature_post');
                toggle('excerpt_wrapper', t !== 'feature_post');
                toggle('button_wrapper', t !== 'feature_post');
            }

            handleTypeChange();

            // copy shortcode
            if (copyBtn) {
                copyBtn.addEventListener('click', () => {
                    const ta = document.getElementById('shortcode');
                    ta.select();
                    document.execCommand('copy');
                    copyBtn.textContent = 'Copied!';
                    setTimeout(() => copyBtn.textContent = 'Copy', 1500);
                });
            }
        });
    </script>
@endsection
