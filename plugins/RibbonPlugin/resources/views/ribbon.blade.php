@if($setting)
  <div class="bg-primary text-white text-sm">
    <div class="container mx-auto px-4 py-2 flex items-center justify-between">
      {{-- Left text --}}
      <div>{{ $setting->left_text }}</div>

      {{-- Right side: RFQ link, phone & email --}}
      <div class="flex items-center space-x-6">
        @if($setting->rfq_url)
          <a href="{{ $setting->rfq_url }}"
             class="underline hover:text-gray-200">
            {{ $setting->rfq_text }}
          </a>
        @endif

        <div class="flex items-center space-x-1">
          <x-lucide-phone-call class="w-4 h-4" />
          <span>{{ $setting->phone }}</span>
        </div>

        <div class="flex items-center space-x-1">
          <x-lucide-mail class="w-4 h-4" />
          <span>{{ $setting->email }}</span>
        </div>
      </div>
    </div>
  </div>
@endif
