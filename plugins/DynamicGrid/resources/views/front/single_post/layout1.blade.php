{{-- resources/views/plugins/DynamicGrid/templates/single_layout1.blade.php --}}
@php
    use Illuminate\Support\Str;

    $isProductTax = $opts['taxonomy'] === 'product';
    $modelClass = $isProductTax ? \App\Models\Product::class : \App\Models\Post::class;
    $relation = $isProductTax ? 'taxonomies' : 'termTaxonomies';

    // Build base query
    $query = $modelClass::whereHas($relation, function ($q) use ($opts) {
        $q->where('taxonomy', $opts['taxonomy'])->where('term_id', $opts['category_id']);
    });

    if (($opts['type'] ?? '') === 'single_post' && !empty($opts['product_amount'])) {
        $query->take(intval($opts['product_amount']));
    }

    $items = $query->get();
@endphp

@if ($items->isEmpty())
    <div class="p-4 bg-yellow-50 text-yellow-800 rounded">
        No items found for “{{ $opts['taxonomy'] }}” & category {{ $opts['category_id'] }}.
    </div>
@else
    <div x-data="dynamicGridCart()" x-init="init()" class="relative single-layout1 space-y-6">
        {{-- Optional heading --}}
        @if (!empty($opts['heading']))
            <h2 class="text-4xl font-bold text-center">{{ $opts['heading'] }}</h2>
        @endif

        {{-- Grid --}}
        <div
            class="grid
                    grid-cols-{{ $opts['columns']['mobile'] }}
                    sm:grid-cols-{{ $opts['columns']['tablet'] }}
                    md:grid-cols-{{ $opts['columns']['medium'] }}
                    lg:grid-cols-{{ $opts['columns']['desktop'] }}
                    xl:grid-cols-{{ $opts['columns']['large'] }}
                    gap-6">
            @foreach ($items as $item)
                @php
                    $media = $item->featuredMedia->first();
                    $title = $isProductTax ? $item->name : $item->title;
                    $img = $media ? $media->getUrl() : '';
                    $url = $isProductTax ? route('products.show', $item->slug) : route('posts.show', $item->slug);
                @endphp

                <div class="bg-white rounded-lg p-4 flex flex-col items-center text-center">
                    @if (!empty($opts['show_image']) && $media)
                        <a href="{{ $url }}">
                            <x-responsive-image :media="$media" alt="{{ $title }}"
                                class="w-full h-auto object-cover rounded mb-4" />
                        </a>
                    @endif

                    <h3 class="font-medium text-lg my-2">{{ $title }}</h3>

                    @if (!empty($opts['show_description']) && !empty($opts['excerpt_words']))
                        <p class="text-gray-600 mb-4">
                            {{ Str::words(strip_tags($item->description ?? $item->content), $opts['excerpt_words'], '…') }}
                        </p>
                    @endif

                    @if (($opts['button_type'] ?? '') === 'price')
                        <button type="button"
                            class="get-price-btn mt-auto px-4 py-2 text-blue-600 font-medium border-b-2 border-blue-600 hover:text-blue-800"
                            data-id="{{ $item->id }}" data-title="{{ e($title) }}"
                            data-image="{{ $img }}" data-url="{{ $url }}">
                            Get Price
                        </button>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Cart Icon --}}
        <button type="button" @click="showCart = true" class="fixed top-6 right-6 bg-white p-2 rounded-full shadow">
            <x-lucide-shopping-cart class="w-6 h-6" />
            <span x-show="cart.length > 0" x-text="cart.length"
                class="absolute -top-1 -right-1 bg-red-600 text-white rounded-full text-xs w-5 h-5 flex items-center justify-center"></span>
        </button>

        {{-- Step 1: Cart Overview --}}
        <div x-show="showCart" x-cloak
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg w-96 p-4 relative">
                <button @click="showCart = false" class="absolute top-2 right-2 text-gray-500">✕</button>
                <h2 class="text-xl font-bold mb-4">Your Cart</h2>
                <ul class="space-y-2 max-h-60 overflow-auto text-sm">
                    <template x-for="item in cart" :key="item.id">
                        <li class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <img :src="item.img" class="w-8 h-8 object-cover rounded" />
                                <span x-text="item.title"></span>
                            </div>
                            <button @click="remove(item.id)" class="text-gray-500">✕</button>
                        </li>
                    </template>
                </ul>
                <div class="mt-4 text-right">
                    <button @click="goToForm()" class="bg-blue-600 text-white px-4 py-2 rounded">Continue</button>
                    <button @click="showCart = false" class="ml-2 bg-gray-300 px-4 py-2 rounded">Cancel</button>
                </div>
            </div>
        </div>

        {{-- Step 2: Contact Form --}}
        <div x-show="showForm" x-cloak
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-[500px] max-w-full p-6 relative space-y-4">
                <button @click="showForm = false" class="absolute top-3 right-3 text-gray-500">✕</button>
                <h2 class="text-lg font-semibold mb-2">
                    <span x-text="cart.length"></span> item(s) selected to get price.
                </h2>

                {{-- Validation Errors --}}
                <div x-show="errors.length > 0" class="bg-red-100 text-red-800 p-2 rounded mb-2">
                    <template x-for="err in errors" :key="err">
                        <div x-text="err"></div>
                    </template>
                </div>

                {{-- Form --}}
                <form @submit.prevent="submit()" class="space-y-4 text-sm">
                    <div class="grid grid-cols-2 gap-4">
                        <input type="text" x-model="name" placeholder="Name"
                            class="border border-gray-300 rounded px-4 py-2 w-full" required>
                        <input type="text" x-model="whatsapp" placeholder="WhatsApp"
                            class="border border-gray-300 rounded px-4 py-2 w-full">
                    </div>
                    <input type="email" x-model="email" placeholder="Email"
                        class="border border-gray-300 rounded px-4 py-2 w-full" required>
                    <textarea x-model="message" placeholder="Write your Message Here"
                        class="border border-gray-300 rounded px-4 py-2 w-full h-32" required></textarea>
                    <div class="flex justify-end space-x-2 pt-2">
                        <button type="submit" class="bg-blue-600 text-white rounded px-4 py-2">Send</button>
                        <button type="button" @click="showForm = false"
                            class="border border-gray-300 rounded px-4 py-2">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

{{-- Alpine component logic --}}
<script>
    function dynamicGridCart() {
        return {
            cart: [],
            showCart: false,
            showForm: false,
            name: '',
            whatsapp: '',
            email: '',
            message: '',
            errors: [],

            init() {
                document.querySelectorAll('.get-price-btn').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const id = btn.dataset.id;
                        const title = btn.dataset.title;
                        const img = btn.dataset.image;
                        const url = btn.dataset.url;
                        if (!this.cart.some(x => x.id === id)) {
                            this.cart.push({
                                id,
                                title,
                                img,
                                url
                            });
                            // show notification when added to cart
                            ntfy(`"${ title }" added to cart`);
                        }
                    });
                });
            },

            remove(id) {
                this.cart = this.cart.filter(x => x.id !== id);
            },

            goToForm() {
                this.errors = [];
                this.showCart = false;
                this.showForm = true;
            },

            async submit() {
                this.errors = [];
                const payload = {
                    name: this.name,
                    whatsapp: this.whatsapp,
                    email: this.email,
                    message: this.message,
                    products: this.cart
                };

                try {
                    let res = await fetch('/dynamicgrid/request-price', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document
                                .querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                        },
                        body: JSON.stringify(payload)
                    });

                    if (res.status === 422) {
                        let json = await res.json();
                        this.errors = Object.values(json.errors).flat();
                        return;
                    }

                    let json = await res.json();
                    alert(json.message || 'Request sent!');
                    this.showForm = false;
                    this.cart = [];
                    this.name = this.whatsapp = this.email = this.message = '';
                } catch (e) {
                    console.error(e);
                    alert('Error sending request.');
                }
            }
        }
    }
</script>
