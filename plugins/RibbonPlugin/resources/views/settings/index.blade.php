{{-- plugins/RibbonPlugin/resources/views/settings/index.blade.php --}}
@extends('layouts.dashboard')

@section('content')
<div class=" mx-auto p-6 bg-white rounded-lg shadow-lg">
  <div class="flex items-center mb-6">
    <x-lucide-arrow-left class="w-5 h-5 text-gray-500 mr-2 hover:text-gray-700 cursor-pointer" onclick="history.back()" />
    <h2 class="text-2xl font-semibold text-gray-800">Ribbon Settings</h2>
  </div>

  @if(session('success'))
    <div class="mb-4 px-4 py-2 bg-green-100 border border-green-200 text-green-800 rounded">
      {{ session('success') }}
    </div>
  @endif

  <form action="{{ route('ribbon-plugin.settings.update') }}" method="POST" novalidate>
    @csrf

    <div class="space-y-5">
      <div>
        <label for="left_text" class="block text-sm font-medium text-gray-700 mb-1">Left Text</label>
        <input
          id="left_text"
          type="text"
          name="left_text"
          value="{{ old('left_text', $setting->left_text ?? '') }}"
          placeholder="SiATEX – Clothing manufacturer since 1987"
          class="w-full rounded-md border border-gray-300 px-4 py-2 focus:border-primary focus:ring-2 focus:ring-primary-light transition"
        />
        @error('left_text')
          <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label for="rfq_text" class="block text-sm font-medium text-gray-700 mb-1">RFQ Text</label>
        <input
          id="rfq_text"
          type="text"
          name="rfq_text"
          value="{{ old('rfq_text', $setting->rfq_text ?? '') }}"
          placeholder="RFQ Form"
          class="w-full rounded-md border border-gray-300 px-4 py-2 focus:border-primary focus:ring-2 focus:ring-primary-light transition"
        />
        @error('rfq_text')
          <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label for="rfq_url" class="block text-sm font-medium text-gray-700 mb-1">RFQ URL</label>
        <input
          id="rfq_url"
          type="url"
          name="rfq_url"
          value="{{ old('rfq_url', $setting->rfq_url ?? '') }}"
          placeholder="https://example.com/rfq"
          class="w-full rounded-md border border-gray-300 px-4 py-2 focus:border-primary focus:ring-2 focus:ring-primary-light transition"
        />
        @error('rfq_url')
          <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
        <input
          id="phone"
          type="text"
          name="phone"
          value="{{ old('phone', $setting->phone ?? '') }}"
          placeholder="(02) 222‑285‑548"
          class="w-full rounded-md border border-gray-300 px-4 py-2 focus:border-primary focus:ring-2 focus:ring-primary-light transition"
        />
        @error('phone')
          <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
        <input
          id="email"
          type="email"
          name="email"
          value="{{ old('email', $setting->email ?? '') }}"
          placeholder="sales@siatex.com"
          class="w-full rounded-md border border-gray-300 px-4 py-2 focus:border-primary focus:ring-2 focus:ring-primary-light transition"
        />
        @error('email')
          <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
      </div>
    </div>

    <div class="mt-6">
      <button
        type="submit"
        class="inline-flex items-center px-6 py-2 bg-green-600 text-white font-medium rounded-md hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary-light transition"
      >
       
        Save Settings
      </button>
    </div>
  </form>
</div>
@endsection
