@extends('layouts.dashboard')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 max-w-7xl mx-auto">

    <!-- Customization Form -->
    <div class=" bg-white p-6 rounded shadow" x-data="themeCustomizer()" @input.debounce.500="previewChanges">
        <h2 class="text-xl font-semibold mb-4">Customize Theme: <span class="capitalize">{{ getActiveTheme() }}</span></h2>

        @if (session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('themes.customize.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Logo Upload -->
            <div>
                <label class="block font-medium mb-1">Logo</label>
                <input type="file" name="logo" class="w-full border p-2 rounded">
                @if ($settings->logo)
                    <img src="{{ asset('storage/' . $settings->logo) }}" class="h-10 mt-2" alt="Logo">
                @endif
            </div>

            <!-- Primary Color -->
            <div>
                <label class="block font-medium mb-1">Primary Color</label>
                <input type="color" name="primary_color" x-model="primaryColor" value="{{ $settings->primary_color ?? '#0d6efd' }}" class="w-24 h-10 border-2 rounded">
            </div>

            <!-- Font Family -->
            <div>
                <label class="block font-medium mb-1">Font Family</label>
                <select name="font_family" x-model="fontFamily" class="w-full border p-2 rounded">
                    <option value="sans-serif">Sans Serif</option>
                    <option value="serif">Serif</option>
                    <option value="monospace">Monospace</option>
                    <option value="'Poppins', sans-serif">Poppins</option>
                    <option value="'Open Sans', sans-serif">Open Sans</option>
                </select>
            </div>

            <!-- Footer Text -->
            <div>
                <label class="block font-medium mb-1">Footer Text</label>
                <input type="text" name="footer_text" x-model="footerText" value="{{ $settings->footer_text }}" class="w-full border p-2 rounded">
            </div>

            <!-- Custom CSS -->
            <div>
                <label class="block font-medium mb-1">Custom CSS</label>
                <textarea name="custom_css" x-model="customCss" rows="6" class="w-full border p-2 rounded">{{ $settings->custom_css }}</textarea>
            </div>

            <div class="flex justify-between items-center">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Save Settings</button>

                <form method="POST" action="{{ route('themes.customize.reset') }}" onsubmit="return confirm('Reset to default?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm text-red-500 hover:underline">Reset to Default</button>
                </form>
            </div>
        </form>

        <!-- Export/Import Buttons -->
        <div class="mt-6">
            <form action="{{ route('themes.customize.import') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-3">
                @csrf
                <input type="file" name="import_file" accept="application/json" class="border rounded p-2">
                <button class="bg-gray-700 text-white px-4 py-2 rounded">Import JSON</button>
            </form>
            <a href="{{ route('themes.customize.export') }}" class="text-sm text-blue-600 underline inline-block mt-2">Download Current Settings (JSON)</a>
        </div>
    </div>

    <!-- Live Preview -->
    <div class=" bg-white rounded shadow overflow-hidden">
        <iframe x-ref="previewFrame" src="{{ url('/') }}" class="w-full h-[600px] border-0"></iframe>
    </div>

</div>
@endsection

@push('scripts')
<script>
function themeCustomizer() {
    return {
        primaryColor: '{{ $settings->primary_color ?? "#0d6efd" }}',
        customCss: @json($settings->custom_css ?? ''),
        fontFamily: '{{ $settings->font_family ?? "sans-serif" }}',
        footerText: @json($settings->footer_text ?? ''),

        previewChanges() {
            const iframe = this.$refs.previewFrame;
            const doc = iframe.contentDocument || iframe.contentWindow.document;
            if (!doc) return;

            // Clear previous injected styles
            doc.querySelectorAll('[data-live-preview]').forEach(e => e.remove());

            const styles = `
                :root { --primary-color: ${this.primaryColor}; }
                body { font-family: ${this.fontFamily}; }
            `;

            const styleBlock = document.createElement('style');
            styleBlock.setAttribute('data-live-preview', 'true');
            styleBlock.textContent = styles;
            doc.head.appendChild(styleBlock);

            if (this.customCss) {
                const cssBlock = document.createElement('style');
                cssBlock.setAttribute('data-live-preview', 'true');
                cssBlock.textContent = this.customCss;
                doc.head.appendChild(cssBlock);
            }

            const footer = doc.querySelector('footer');
            if (footer) footer.innerText = this.footerText;
        }
    }
}
</script>
@endpush