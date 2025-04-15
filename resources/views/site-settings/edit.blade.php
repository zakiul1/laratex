@extends('layouts.dashboard')

@section('content')
    <div class="max-w-3xl mx-auto p-6 bg-white rounded shadow">
        <h2 class="text-lg font-semibold mb-4">Edit Site Settings</h2>

        <div id="successMessage" class="hidden mb-4 p-4 bg-green-100 text-green-800 rounded"></div>
        <div id="errorMessage" class="hidden mb-4 p-4 bg-red-100 text-red-800 rounded"></div>

        <form id="siteSettingsForm" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Site Name -->
                <div>
                    <label class="block text-sm font-medium">Site Name</label>
                    <input type="text" name="site_name" value="{{ $setting->site_name }}"
                        class="w-full border rounded px-3 py-2 mt-1" />
                </div>

                <!-- Logo Upload -->
                <div x-data="logoControl()" class="space-y-2">
                    <label class="block text-sm font-medium">Logo</label>
                    <input type="file" name="logo" @change="previewImage($event)" class="w-full mt-1" accept="image/*" />

                    <!-- Preview -->
                    <template x-if="preview || existingLogo">
                        <div class="relative w-fit">
                            <img :src="preview ?? existingLogo" class="h-12 border rounded">
                            <button type="button"
                                class="absolute -top-2 -right-2 bg-red-600 text-white text-xs rounded-full w-5 h-5"
                                @click="deleteLogo">
                                ×
                            </button>
                        </div>
                    </template>
                </div>

                <!-- Show Ribbon -->
                <div class="md:col-span-2">
                    <label class="flex items-center mt-2">
                        <input type="hidden" name="show_ribbon" value="0">
                        <input type="checkbox" name="show_ribbon" value="1" {{ $setting->show_ribbon ? 'checked' : '' }}>
                        <span class="ml-2 text-sm">Show Ribbon on Frontend</span>
                    </label>
                </div>

                <!-- Ribbon Settings -->
                <div class="md:col-span-2 border-t pt-4 mt-4">
                    <h3 class="font-semibold text-sm mb-2">Ribbon Settings</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium">Left Text</label>
                            <input type="text" name="ribbon_left_text" value="{{ $setting->ribbon_left_text }}"
                                class="w-full border rounded px-3 py-2 mt-1" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Phone</label>
                            <input type="text" name="ribbon_phone" value="{{ $setting->ribbon_phone }}"
                                class="w-full border rounded px-3 py-2 mt-1" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Email</label>
                            <input type="email" name="ribbon_email" value="{{ $setting->ribbon_email }}"
                                class="w-full border rounded px-3 py-2 mt-1" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Background Color</label>
                            <input type="color" name="ribbon_bg_color" value="{{ $setting->ribbon_bg_color ?? '#0a4b78' }}"
                                class="w-16 h-10 border rounded cursor-pointer" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Text Color</label>
                            <input type="color" name="ribbon_text_color"
                                value="{{ $setting->ribbon_text_color ?? '#ffffff' }}"
                                class="w-16 h-10 border rounded cursor-pointer" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <button type="button" id="submitBtn"
                class="mt-6 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save</button>
        </form>
    </div>
    @push('scripts')
        <script>
            function logoControl() {
                return {
                    preview: null,
                    existingLogo: '{{ $setting->logo ? asset("storage/" . $setting->logo) : '' }}',
                    previewImage(event) {
                        const file = event.target.files[0];
                        if (!file) return;
                        const reader = new FileReader();
                        reader.onload = e => this.preview = e.target.result;
                        reader.readAsDataURL(file);
                    },
                    deleteLogo() {
                        if (!confirm('Delete this logo?')) return;
                        fetch('{{ route("site-settings.remove-logo") }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: new URLSearchParams({ _method: 'DELETE' })
                        }).then(res => {
                            if (res.ok) {
                                this.preview = null;
                                this.existingLogo = null;
                                document.querySelector('input[name="logo"]').value = '';
                            }
                        });
                    }
                }
            }

            document.addEventListener('DOMContentLoaded', () => {
                const form = document.getElementById('siteSettingsForm');
                const submitBtn = document.getElementById('submitBtn');
                const successDiv = document.getElementById('successMessage');
                const errorDiv = document.getElementById('errorMessage');

                submitBtn.addEventListener('click', () => {
                    const formData = new FormData(form);
                    fetch("{{ route('site-settings.update') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        },
                        body: formData
                    })
                        .then(async res => {
                            if (!res.ok) {
                                const errorHtml = await res.text();  // this will be your rendered HTML error
                                throw new Error(errorHtml); // This goes to .catch
                            }

                            return res.json();
                        })
                        .then(data => {
                            successDiv.textContent = data.message || 'Settings updated successfully.';
                            successDiv.classList.remove('hidden');
                            errorDiv.classList.add('hidden');
                        })
                        .catch(err => {
                            console.error('Error occurred:', err.message);
                            errorDiv.innerText = 'An error occurred. Check console for more info.';
                            errorDiv.classList.remove('hidden');
                            successDiv.classList.add('hidden');
                        });

                });
            });
        </script>
    @endpush

@endsection