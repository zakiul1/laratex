@extends('layouts.dashboard')

@section('content')
    <div class="max-w-4xl mx-auto px-4 py-6">
        <h2 class="text-xl font-semibold mb-6">Edit Contact Info</h2>

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.contact.update') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium">Address</label>
                <input type="text" name="address" value="{{ old('address', $contact->address) }}"
                    class="w-full mt-1 p-2 border border-gray-300 rounded">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium">Phone 1</label>
                    <input type="text" name="phone1" value="{{ old('phone1', $contact->phone1) }}"
                        class="w-full mt-1 p-2 border border-gray-300 rounded">
                </div>
                <div>
                    <label class="block text-sm font-medium">Phone 2</label>
                    <input type="text" name="phone2" value="{{ old('phone2', $contact->phone2) }}"
                        class="w-full mt-1 p-2 border border-gray-300 rounded">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium">Email 1</label>
                    <input type="email" name="email1" value="{{ old('email1', $contact->email1) }}"
                        class="w-full mt-1 p-2 border border-gray-300 rounded">
                </div>
                <div>
                    <label class="block text-sm font-medium">Email 2</label>
                    <input type="email" name="email2" value="{{ old('email2', $contact->email2) }}"
                        class="w-full mt-1 p-2 border border-gray-300 rounded">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium">Google Map Embed Iframe</label>
                <textarea name="map_embed" rows="3"
                    class="w-full mt-1 p-2 border border-gray-300 rounded">{{ old('map_embed', $contact->map_embed) }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium">Instagram URL</label>
                    <input type="url" name="social_instagram"
                        value="{{ old('social_instagram', $contact->social_instagram) }}"
                        class="w-full mt-1 p-2 border border-gray-300 rounded">
                </div>
                <div>
                    <label class="block text-sm font-medium">Facebook URL</label>
                    <input type="url" name="social_facebook" value="{{ old('social_facebook', $contact->social_facebook) }}"
                        class="w-full mt-1 p-2 border border-gray-300 rounded">
                </div>
            </div>

            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                Save
            </button>
        </form>
    </div>
@endsection