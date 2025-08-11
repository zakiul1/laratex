@php
    // Demo data — replace with real meta if you have it
    $colorOptions = [
        ['label' => 'Blue & White', 'hex' => '#1f77ff'],
        ['label' => 'Beige', 'hex' => '#d9c7a1'],
        ['label' => 'Teal', 'hex' => '#2aa198'],
        ['label' => 'Black', 'hex' => '#111111'],
        ['label' => 'Red', 'hex' => '#e11d48'],
    ];
    $sizeOptions = ['M', 'L', 'XL', '2 XL', '3 XL'];

    $media = $product->featuredMedia->first();
    $productImg = $media?->getUrl('thumbnail') ?? ($media?->getUrl('medium') ?? '');
    $productTitle = $product->name ?? ($product->title ?? 'Product');
    $productUrl = route('products.show', $product->slug ?? $product->id);
@endphp

<div x-data="orderDrawer({
    product: { id: {{ $product->id }}, title: @js($productTitle), img: @js($productImg), url: @js($productUrl) },
    colors: @js($colorOptions),
    sizes: @js($sizeOptions),
    priceTiers: [
        { min: 2, max: 99, price: 6.47, old: 7.27, badge: '11% off' },
        { min: 100, max: 499, price: 6.32, old: 7.10 },
        { min: 500, max: 999, price: 6.12, old: 6.88 },
        { min: 1000, max: null, price: 5.94, old: 6.67 }
    ]
})" @open-order-drawer.window="open = true" x-cloak>
    <!-- Overlay + Drawer -->
    <div x-show="open" class="fixed inset-0 z-[60] flex" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="flex-1 bg-black/50" @click="open = false" aria-hidden="true"></div>

        <!-- Drawer -->
        <div class="w-full max-w-[480px] h-full bg-white shadow-xl flex flex-col translate-x-full data-[open=true]:translate-x-0 transition-transform duration-300"
            :data-open="open">
            <!-- Header -->
            <div class="flex items-center justify-between px-5 py-4 border-b">
                <h3 class="text-[18px] font-semibold">Select variations and quantity</h3>
                <button class="text-gray-500 hover:text-gray-700" @click="open=false" aria-label="Close">✕</button>
            </div>

            <!-- Scrollable content -->
            <div class="flex-1 overflow-y-auto px-5 py-4 space-y-6">

                <!-- Price tiers -->
                <div class="space-y-3">
                    <div class="grid grid-cols-4 gap-3">
                        <template x-for="(t,idx) in priceTiers" :key="idx">
                            <div class="border rounded-lg px-2.5 py-2 text-center">
                                <div class="text-xs text-gray-600" x-text="tierLabel(t)"></div>
                                <div class="text-lg font-semibold" x-text="formatPrice(t.price)"></div>
                                <div class="text-xs line-through text-gray-400" x-text="formatPrice(t.old)"></div>
                                <template x-if="idx === 0 && t.badge">
                                    <div class="inline-block mt-1 text-[10px] bg-orange-500 text-white rounded px-1.5 py-0.5"
                                        x-text="t.badge"></div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Colors -->
                <div class="space-y-2">
                    <div class="font-medium text-gray-800">
                        Color(<span x-text="colors.length"></span>):
                        <span class="text-gray-600" x-text="colors[selectedColor]?.label"></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <template x-for="(c,i) in colors" :key="i">
                            <button type="button" class="w-10 h-10 rounded-md border flex items-center justify-center"
                                :class="i === selectedColor ? 'ring-2 ring-black border-black' : 'border-gray-300'"
                                @click="selectedColor = i" :aria-label="`Select color ${c.label}`">
                                <span class="block w-8 h-8 rounded" :style="`background:${c.hex}`"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Sizes + qty steppers -->
                <div class="space-y-3">
                    <div class="font-medium text-gray-800">Size(<span x-text="sizes.length"></span>)</div>
                    <template x-for="(sz, i) in sizes" :key="sz">
                        <div class="flex items-center justify-between py-2 border-b last:border-b-0">
                            <div>
                                <span
                                    class="inline-flex items-center justify-center rounded-md border px-2.5 py-1 text-sm"
                                    :class="selectedSize === sz ? 'border-black' : 'border-gray-300 text-gray-700'"
                                    @click="selectedSize = sz" x-text="sz"></span>
                            </div>

                            <div class="flex items-center gap-4">
                                <div class="text-gray-800 font-medium" x-text="formatPrice(currentUnitPrice)"></div>

                                <div class="flex items-center gap-2">
                                    <button type="button"
                                        class="w-7 h-7 rounded-full border border-gray-300 text-gray-700"
                                        @click="dec(sz)" :disabled="qty[sz] === 0"
                                        :class="qty[sz] === 0 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-gray-50'">−</button>
                                    <span class="w-6 text-center" x-text="qty[sz] ?? 0"></span>
                                    <button type="button"
                                        class="w-7 h-7 rounded-full border border-gray-300 text-gray-700 hover:bg-gray-50"
                                        @click="inc(sz)">+</button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Shipping -->
                <div class="space-y-2">
                    <div class="font-medium text-gray-800">Shipping</div>
                    <div class="border rounded-lg p-3 text-sm text-gray-700">
                        <div class="font-medium">DPEX (Economy) <span class="text-gray-400">| siatex.com
                                Logistics</span></div>
                        <div>Shipping fee: Est. $20.65 for 2 pieces</div>
                        <div>Guaranteed delivery by <span class="font-medium">Sep 12</span></div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="border-t px-5 py-4">
                <div class="flex items-center justify-between text-sm mb-2">
                    <div class="text-gray-600">Subtotal</div>
                    <div class="font-semibold" x-text="`$${subtotal.toFixed(2)}`"></div>
                </div>
                <button type="button" class="w-full rounded-full bg-[#ff6a00] text-white font-semibold py-3"
                    @click="submitOrder()">
                    Start order
                </button>
            </div>
        </div>
    </div>
</div>

@once
    <script>
        function orderDrawer(seed) {
            return {
                open: false,
                product: seed.product,
                colors: seed.colors || [],
                sizes: seed.sizes || [],
                priceTiers: seed.priceTiers || [],
                selectedColor: 0,
                selectedSize: null,
                qty: {},

                get totalQty() {
                    return Object.values(this.qty).reduce((a, b) => a + (b || 0), 0);
                },
                get currentUnitPrice() {
                    const q = this.totalQty;
                    let found = this.priceTiers.find(t => q >= t.min && (t.max === null || q <= t.max));
                    return (found ? found.price : (this.priceTiers[0]?.price || 0));
                },
                get subtotal() {
                    return this.currentUnitPrice * this.totalQty;
                },

                tierLabel(t) {
                    if (t.max === null) return `>= ${t.min} pieces`;
                    return `${t.min} - ${t.max} pieces`;
                },
                formatPrice(v) {
                    return `$${Number(v).toFixed(2)}`;
                },

                inc(size) {
                    this.qty[size] = (this.qty[size] || 0) + 1;
                },
                dec(size) {
                    if ((this.qty[size] || 0) > 0) this.qty[size]--;
                },

                submitOrder() {
                    // If your dynamic cart store exists, add items and open the cart.
                    try {
                        const store = window.Alpine?.store('dynamicCart');
                        if (store) {
                            const color = this.colors[this.selectedColor]?.label || '';
                            const items = Object.entries(this.qty)
                                .filter(([, q]) => q > 0)
                                .map(([size, q]) => ({
                                    id: `${this.product.id}-${color}-${size}`,
                                    title: `${this.product.title} — ${color} / ${size}`,
                                    url: this.product.url,
                                    img: this.product.img,
                                    qty: q
                                }));
                            if (items.length) {
                                items.forEach(i => store.add(i));
                                store.showCart = true;
                                this.open = false;
                                return;
                            }
                        }
                    } catch (e) {}

                    // Fallback: just go to the product page
                    window.location.href = this.product.url + '#order';
                }
            }
        }
    </script>
@endonce
