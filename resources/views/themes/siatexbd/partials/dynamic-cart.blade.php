<div x-data>
    {{-- Cart Icon --}}
    <button @click="$store.dynamicCart.showCart = true" class="fixed top-6 right-6 bg-white p-2 rounded-full shadow z-50">
        <x-lucide-shopping-cart class="w-6 h-6" />
        <span x-show="$store.dynamicCart.items.length > 0" x-text="$store.dynamicCart.items.length"
            class="absolute -top-1 -right-1 bg-red-600 text-white rounded-full text-xs w-5 h-5 flex items-center justify-center"></span>
    </button>

    {{-- Cart Overview --}}
    <div x-show="$store.dynamicCart.showCart" x-cloak
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg w-96 p-4 relative">
            <button @click="$store.dynamicCart.showCart = false" class="absolute top-2 right-2 text-gray-500">✕</button>
            <h2 class="text-xl font-bold mb-4">Your Cart</h2>
            <ul class="space-y-2 max-h-60 overflow-auto text-sm">
                <template x-for="item in $store.dynamicCart.items" :key="item.id">
                    <li class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <img :src="item.img" class="w-8 h-8 object-cover rounded" />
                            <a :href="item.url" class="hover:underline" x-text="item.title"></a>
                        </div>
                        <button @click="$store.dynamicCart.remove(item.id)" class="text-gray-500">✕</button>
                    </li>
                </template>
            </ul>
            <div class="mt-4 text-right">
                <button @click="$store.dynamicCart.goToForm()"
                    class="bg-blue-800 text-white px-4 py-2 rounded">Continue</button>
                <button @click="$store.dynamicCart.showCart = false"
                    class="ml-2 bg-gray-300 px-4 py-2 rounded">Cancel</button>
            </div>
        </div>
    </div>

    {{-- Contact Form --}}
    <div x-show="$store.dynamicCart.showForm" x-cloak
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-[500px] max-w-full p-6 relative space-y-4">
            <button @click="$store.dynamicCart.showForm = false" class="absolute top-3 right-3 text-gray-500">✕</button>
            <h2 class="text-lg font-semibold mb-2">
                <span x-text="$store.dynamicCart.items.length"></span> item(s) selected to get price.
            </h2>

            {{-- Validation Errors --}}
            <div x-show="$store.dynamicCart.errors.length > 0" class="bg-red-100 text-red-800 p-2 mb-2 rounded">
                <template x-for="err in $store.dynamicCart.errors" :key="err">
                    <div x-text="err"></div>
                </template>
            </div>

            <form @submit.prevent="$store.dynamicCart.submit()" class="space-y-4 text-sm">
                <div class="grid grid-cols-2 gap-4">
                    <input type="text" x-model="$store.dynamicCart.name" placeholder="Name"
                        class="border border-gray-300 rounded px-4 py-2 w-full" required>
                    <input type="text" x-model="$store.dynamicCart.whatsapp" placeholder="WhatsApp"
                        class="border border-gray-300 rounded px-4 py-2 w-full">
                </div>
                <input type="email" x-model="$store.dynamicCart.email" placeholder="Email"
                    class="border border-gray-300 rounded px-4 py-2 w-full" required>
                <textarea x-model="$store.dynamicCart.message" placeholder="Write your Message Here"
                    class="border border-gray-300 rounded px-4 py-2 w-full h-32" required></textarea>
                <div class="flex justify-end space-x-2 pt-2">
                    <button type="submit" class="bg-blue-800 text-white rounded px-4 py-2">Send</button>
                    <button type="button" @click="$store.dynamicCart.showForm = false"
                        class="border border-gray-300 rounded px-4 py-2">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
