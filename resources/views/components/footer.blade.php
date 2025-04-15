<footer class="bg-neutral-900 text-gray-300">
    <div class="container mx-auto px-4 py-10 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
        <!-- Company -->
        <div>
            <h4 class="text-white text-sm font-semibold mb-4 uppercase tracking-wide">Company</h4>
            <ul class="space-y-2 text-sm">
                <li><a href="#" class="hover:text-white">About Us</a></li>
                <li><a href="#" class="hover:text-white">How It Works</a></li>
                <li><a href="#" class="hover:text-white">Privacy Policy</a></li>
                <li><a href="#" class="hover:text-white">Terms & Conditions</a></li>
            </ul>
        </div>

        <!-- Products -->
        <div>
            <h4 class="text-white text-sm font-semibold mb-4 uppercase tracking-wide">Products</h4>
            <ul class="space-y-2 text-sm">
                <li><a href="#" class="hover:text-white">Workwear</a></li>
                <li><a href="#" class="hover:text-white">Tactical Gear</a></li>
                <li><a href="#" class="hover:text-white">Sportswear</a></li>
                <li><a href="#" class="hover:text-white">Custom Orders</a></li>
            </ul>
        </div>

        <!-- Support -->
        <div>
            <h4 class="text-white text-sm font-semibold mb-4 uppercase tracking-wide">Support</h4>
            <ul class="space-y-2 text-sm">
                <li><a href="#" class="hover:text-white">Contact Us</a></li>
                <li><a href="#" class="hover:text-white">FAQs</a></li>
                <li><a href="#" class="hover:text-white">Shipping & Returns</a></li>
            </ul>
        </div>

        <!-- Newsletter -->
        <div>
            <h4 class="text-white text-sm font-semibold mb-4 uppercase tracking-wide">Subscribe</h4>
            <p class="text-sm mb-3">Get the latest updates and offers.</p>
            <form action="#" method="POST" class="flex flex-col sm:flex-row items-center gap-2">
                <input type="email" name="email" placeholder="Your email"
                    class="w-full px-3 py-2 rounded bg-neutral-800 text-sm text-white placeholder-gray-400 focus:outline-none focus:ring focus:border-blue-500">
                <button type="submit"
                    class="px-4 py-2 bg-red-600 text-white text-sm rounded hover:bg-red-700 transition">Subscribe</button>
            </form>
        </div>
    </div>

    <div class="bg-neutral-800 text-center text-xs text-gray-400 py-4">
        &copy; {{ date('Y') }} WORKYIND. All rights reserved.
    </div>
</footer>