@php use Illuminate\Support\Facades\Storage; @endphp

@if ($products->isNotEmpty())
    <section class="py-12">
        <div class="max-w-7xl mx-auto px-4">
            <h2 class="text-3xl font-bold mb-8 text-center">Featured Products</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach ($products as $p)
                    <a href="{{ route('product.show', $p->slug) }}"
                        class="group block bg-white rounded-lg overflow-hidden shadow hover:shadow-lg transition">
                        @php $img = $p->featured_image; @endphp
                        @if ($img && Storage::disk('public')->exists($img))
                            <img src="{{ Storage::url($img) }}" alt="{{ $p->name }}"
                                class="w-full h-60 object-cover group-hover:scale-105 transition-transform duration-300" />
                        @else
                            <div class="w-full h-60 bg-gray-100 flex items-center justify-center text-gray-400">
                                No Image
                            </div>
                        @endif

                        <div class="p-4">
                            <h3 class="text-center text-sm font-semibold mb-2 uppercase">{{ $p->name }}</h3>
                            <div class="flex justify-center mb-4">
                                @for ($i = 0; $i < 5; $i++)
                                    <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11.049 2.927c…z" />
                                    </svg>
                                @endfor
                            </div>
                            <div class="text-center">
                                <span class="inline-block px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded">
                                    Read More
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
