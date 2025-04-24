{{-- resources/views/theme/customize/partials/identity.blade.php --}}
<div class="border rounded shadow-sm">
    <button type="button" @click="toggleSection('identity')"
        class="w-full px-4 py-3 bg-gray-100 flex justify-between items-center">
        <span class="font-medium">Site Identity</span>
        <svg :class="{ 'rotate-180': open==='identity' }" class="h-4 w-4 transform transition-transform" fill="none"
            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <div x-show="open==='identity'" x-collapse class="px-4 py-4 space-y-4">
        <!-- Logo -->
        <label class="block font-medium">Logo</label>
        <input type="file" name="logo" class="w-full border p-2 rounded">
        @if ($settings->logo)
            <img src="{{ asset('storage/' . $settings->logo) }}" class="h-10 mt-2" alt="Logo">
        @endif

        <!-- Primary Color -->
        <label class="block font-medium">Primary Color</label>
        <input type="color" name="primary_color" x-model="primaryColor" class="w-8 h-8 p-0 border-0 rounded" />

        <!-- Site Title -->
        <label class="block font-medium">Site Title</label>
        <input type="text" name="site_title"
            value="{{ old('site_title', data_get($settings->options, 'site_title', config('app.name'))) }}"
            class="w-full border p-2 rounded">

        <!-- Site Title Color -->
        <label class="block font-medium">Site Title Color</label>
        <input type="color" name="site_title_color" x-model="siteTitleColor"
            value="{{ old('site_title_color', data_get($settings->options, 'site_title_color', '#000000')) }}"
            class="w-8 h-8 p-0 border-0 rounded">

        <!-- Tagline -->
        <label class="block font-medium">Tagline</label>
        <input type="text" name="tagline" x-model="tagline"
            value="{{ old('tagline', data_get($settings->options, 'tagline', '')) }}" class="w-full border p-2 rounded">

        <!-- Tagline Color -->
        <label class="block font-medium">Tagline Color</label>
        <input type="color" name="tagline_color" x-model="taglineColor"
            value="{{ old('tagline_color', data_get($settings->options, 'tagline_color', '#666666')) }}"
            class="w-8 h-8 p-0 border-0 rounded">

        <!-- Show Tagline -->
        <div class="flex items-center">
            <input type="checkbox" id="show_tagline" name="show_tagline" value="1" x-model="showTagline"
                class="mr-2">
            <label for="show_tagline" class="text-sm">Show Tagline with Site Title</label>
        </div>

        <!-- Contact Phone -->
        <label class="block font-medium">Contact Phone</label>
        <input type="text" name="contact_phone" x-model="contactPhone"
            value="{{ old('contact_phone', data_get($settings->options, 'contact_phone', '')) }}"
            class="w-full border p-2 rounded">
    </div>
</div>
