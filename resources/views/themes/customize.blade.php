@extends('layouts.dashboard')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 max-w-7xl mx-auto" x-data="themeCustomizer()" x-init="broadcast()"
        @input.debounce.300="broadcast()">
        {{-- Form --}}
        <div class="col-span-1 bg-white p-6 rounded shadow space-y-6">
            <h2 class="text-xl font-semibold mb-4">
                Customize Theme: <span class="capitalize">{{ getActiveTheme() }}</span>
            </h2>

            @if (session('success'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
            @endif

            <form x-ref="form" @submit.prevent="saveSettings" method="POST" action="{{ route('themes.customize.update') }}"
                enctype="multipart/form-data" class="space-y-6">
                @csrf

                {{-- Site Identity --}}
                @include('themes.customize.partials.identity')

                {{-- Typography & Colors --}}
                @include('themes.customize.partials.typography')

                {{-- Footer & Custom CSS --}}
                @include('themes.customize.partials.footer-css')

                <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded">
                    Save Settings
                </button>
            </form>

            {{-- Reset --}}
            <form method="POST" action="{{ route('themes.customize.reset') }}"
                onsubmit="return confirm('Reset to default?')" class="mt-4">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded">
                    Reset to Default
                </button>
            </form>
        </div>

        {{-- Live Preview --}}
        <div class="col-span-2 bg-white rounded shadow overflow-hidden">
            <iframe x-ref="previewFrame" src="/" class="w-full h-[600px] border-0"></iframe>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('themeCustomizer', themeCustomizer)
        })

        function themeCustomizer() {
            return {
                // —— Core Theme Settings —— 
                primaryColor: @json($settings->primary_color ?? '#0d6efd'),
                linkColor: @json(data_get($settings->options, 'link_color', $settings->primary_color ?? '#0d6efd')),
                fontFamily: @json($settings->font_family ?? 'sans-serif'),
                footerText: @json(data_get($settings->options, 'footer_text', '')),
                customCss: @json(data_get($settings->options, 'custom_css', '')),

                siteTitle: @json($settings->site_title ?? config('app.name')),
                siteTitleColor: @json(data_get($settings->options, 'site_title_color', '#000000')),
                tagline: @json(data_get($settings->options, 'tagline', '')),
                taglineColor: @json(data_get($settings->options, 'tagline_color', '#000000')),
                showTagline: @json(data_get($settings->options, 'show_tagline', false)),
                contactPhone: @json(data_get($settings->options, 'contact_phone', '')),

                // —— Typography State —— 
                typography: {
                    headings: {
                        h1: @json(data_get($settings->options, 'typography.headings.h1', 32)),
                        h2: @json(data_get($settings->options, 'typography.headings.h2', 28)),
                        h3: @json(data_get($settings->options, 'typography.headings.h3', 24)),
                        h4: @json(data_get($settings->options, 'typography.headings.h4', 20)),
                        h5: @json(data_get($settings->options, 'typography.headings.h5', 16)),
                        h6: @json(data_get($settings->options, 'typography.headings.h6', 14)),
                    },
                    strong: {
                        weight: @json(data_get($settings->options, 'typography.strong.weight', 700))
                    },
                    paragraph: {
                        line_height: @json(data_get($settings->options, 'typography.paragraph.line_height', 1.6))
                    },
                    list: {
                        marker: @json(data_get($settings->options, 'typography.list.marker', 'disc'))
                    },
                    anchor: {
                        color: @json(data_get($settings->options, 'typography.anchor.color', '#0d6efd'))
                    },
                },

                // —— UI State for Accordion —— 
                open: null,
                openSub: null,
                typographySections: [{
                        key: 'fontFamily',
                        label: 'Font Family'
                    },
                    {
                        key: 'headings',
                        label: 'Headings'
                    },
                    {
                        key: 'strong',
                        label: 'Strong'
                    },
                    {
                        key: 'paragraph',
                        label: 'Paragraph'
                    },
                    {
                        key: 'list',
                        label: 'List'
                    },
                    {
                        key: 'anchor',
                        label: 'Anchor'
                    },
                    {
                        key: 'importExport',
                        label: 'Import/Export'
                    },
                ],
                get currentSectionLabel() {
                    const sec = this.typographySections.find(s => s.key === this.openSub)
                    return sec ? sec.label : ''
                },

                toggleSection(section) {
                    this.open = this.open === section ? null : section
                    this.openSub = null
                },

                // —— Send all settings to the iframe for live preview —— 
                broadcast() {
                    // deep-clone the reactive typography into a plain object
                    const flatTypography = JSON.parse(JSON.stringify(this.typography))

                    const payload = {
                        type: 'themePreview',
                        primaryColor: this.primaryColor,
                        linkColor: this.linkColor,
                        fontFamily: this.fontFamily,
                        footerText: this.footerText,
                        customCss: this.customCss,
                        siteTitle: this.siteTitle,
                        siteTitleColor: this.siteTitleColor,
                        tagline: this.tagline,
                        taglineColor: this.taglineColor,
                        showTagline: this.showTagline,
                        contactPhone: this.contactPhone,
                        typography: flatTypography
                    }
                    console.log('[Customizer] broadcasting:', payload)
                    this.$refs.previewFrame
                        .contentWindow
                        .postMessage(payload, window.location.origin)
                },

                // —— Persist to server, then re-broadcast —— 
                saveSettings() {
                    const form = this.$refs.form
                    const data = new FormData(form)

                    fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: data
                        })
                        .then(r => r.json())
                        .then(json => {
                            if (json.success) {
                                ntfy(json.message, 'success')
                                this.broadcast()
                            } else {
                                ntfy(json.message || 'Save failed', 'error')
                            }
                        })
                        .catch(() => ntfy('An error occurred', 'error'))
                }
            }
        }
    </script>
@endpush
