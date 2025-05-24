@extends('themes.siatexbd.layout')

@section('content')
    @php
        // Preload featured media and URLs
        $media = $product->featuredMedia->first();
        $mediaUrl = $media ? $media->getUrl() : '';
        $detailUrl = route('products.show', $product->slug);
    @endphp

    <div x-data="dynamicGridCart()" class="bg-white py-12">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- Breadcrumb --}}
            <nav class="text-sm text-gray-500" aria-label="Breadcrumb">
                <ol class="flex flex-wrap space-x-2">
                    <li><a href="{{ route('home') }}" class="hover:underline">Home</a></li>
                    <li>/</li>
                    @if ($category)
                        <li>
                            <a href="{{ route('categories.show', $category->slug) }}" class="hover:underline">
                                {{ $category->name }}
                            </a>
                        </li>
                        <li>/</li>
                    @endif
                    <li class="font-semibold">{{ $product->name }}</li>
                </ol>
            </nav>

            {{-- Product Detail Card --}}
            <div class="bg-white rounded-lg shadow p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">

                    {{-- Left Column: Title, Description, Get Price --}}
                    <div class="space-y-4">
                        {{-- Accent Bar --}}
                        <div class="w-16 h-1 bg-red-600"></div>

                        {{-- Category Label --}}
                        @if ($category)
                            <p class="text-sm uppercase text-gray-500">{{ $category->name }}</p>
                        @endif

                        {{-- Product Name --}}
                        <h1 class="text-3xl md:text-[32px] lg:text-[32px] font-bold text-blue-800">
                            {{ $product->name }}
                        </h1>

                        {{-- Description --}}
                        <p class="text-gray-700 leading-relaxed">
                            @if (!empty($product->excerpt))
                                {!! nl2br(e($product->excerpt)) !!}
                            @elseif(!empty($product->description))
                                {!! nl2br(e(\Illuminate\Support\Str::limit(strip_tags($product->description), 3000, '…'))) !!}
                            @else
                                No description available.
                            @endif
                        </p>

                        {{-- Get Price Button --}}
                        <button type="button"
                            class="get-price-btn inline-block bg-blue-800 text-white px-6 py-3   hover:bg-blue-900 transition"
                            data-id="{{ $product->id }}" data-title="{{ e($product->name) }}"
                            data-image="{{ $mediaUrl }}" data-url="{{ $detailUrl }}">
                            Get Price
                        </button>
                    </div>

                    {{-- Right Column: Responsive Image --}}
                    <div>
                        @if ($media)
                            <div class="aspect-w-16 aspect-h-9  overflow-hidden ">
                                <x-responsive-image :media="$media" class="w-full h-full object-cover"
                                    alt="{{ $product->name }}" />
                            </div>
                        @else
                            <div class="w-full h-80 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400">
                                —
                            </div>
                        @endif
                    </div>

                </div>
            </div>

            {{-- Featured Products --}}
            {{--   @if (!empty($featuredProducts) && $featuredProducts->isNotEmpty())
                <h2 class="text-3xl font-bold">{{ $featuredCategory->term->name }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    @foreach ($featuredProducts as $fp)
                        @php
                            $fm = $fp->featuredMedia->first();
                            $fUrl = route('products.show', $fp->slug);
                        @endphp

                        <div class="bg-white rounded-lg shadow p-4 flex flex-col items-center text-center">
                            @if ($fm)
                                <a href="{{ $fUrl }}">
                                    <x-responsive-image :media="$fm" alt="{{ $fp->name }}"
                                        class="w-full h-auto object-cover mb-4" />
                                </a>
                            @else
                                <div
                                    class="w-full h-32 bg-gray-100 rounded mb-4 flex items-center justify-center text-gray-400">
                                    —
                                </div>
                            @endif

                            <h3 class="font-medium text-lg mb-2">{{ $fp->name }}</h3>
                            <p class="text-gray-600 mb-4">
                                {{ \Illuminate\Support\Str::limit($fp->description ?? '', 60, '…') }}
                            </p>

                            <button type="button"
                                class="get-price-btn px-4 py-2 text-blue-600 font-medium border-b-2 border-blue-600 hover:text-blue-800"
                                data-id="{{ $fp->id }}" data-title="{{ e($fp->name) }}"
                                data-image="{{ $fm ? $fm->getUrl() : '' }}" data-url="{{ $fUrl }}">
                                Get Price
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif --}}
            <div class=" ">
                {!! apply_filters('the_content', $pageOutput) !!}
            </div>
            {{-- Cart Icon --}}
            <button type="button" @click="showCart = true"
                class="fixed top-6 right-6 bg-white p-2 rounded-full shadow z-50">
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
                        <button @click="goToForm()" class="bg-blue-800 text-white px-4 py-2 rounded">Continue</button>
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
                    <div x-show="errors.length > 0" class="bg-red-100 text-red-800 p-2  mb-2">
                        <template x-for="err in errors" :key="err">
                            <div x-text="err"></div>
                        </template>
                    </div>

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
                            <button type="submit" class="bg-blue-800 text-white rounded px-4 py-2">Send</button>
                            <button type="button" @click="showForm = false"
                                class="border border-gray-300 rounded px-4 py-2">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    {{-- Alpine component --}}
    <script>
        function dynamicGridCart() {
            const cmp = {
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
                            const id = btn.dataset.id,
                                title = btn.dataset.title,
                                img = btn.dataset.image,
                                url = btn.dataset.url;
                            if (!this.cart.some(x => x.id === id)) {
                                this.cart.push({
                                    id,
                                    title,
                                    img,
                                    url
                                });
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
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(payload)
                        });

                        if (res.status === 422) {
                            let json = await res.json();
                            this.errors = Object.values(json.errors).flat();
                            return;
                        }

                        let json = await res.json();
                        ntfy(json.message || 'Request sent!');
                        this.showForm = false;
                        this.cart = [];
                        this.name = this.whatsapp = this.email = this.message = '';
                    } catch (e) {
                        console.error(e);
                        ntfy('Error sending request', 'error');
                    }
                }
            };

            // Initialize after Alpine binds
            setTimeout(() => cmp.init(), 0);

            return cmp;
        }
    </script>
@endsection
