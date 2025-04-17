@if($site && $site->show_ribbon)
    <div class="text-white text-sm py-2"
        style="background-color: {{ $site->ribbon_bg_color ?? '#0a4b78' }}; color: {{ $site->ribbon_text_color ?? '#ffffff' }};">
        <div class="container mx-auto px-4 flex justify-between items-center flex-wrap gap-4">

            {{-- Left Text --}}
            <div class="flex-1">
                {{ $site->ribbon_left_text ?? '' }}
            </div>

            {{-- Right Contact Info (Phone and Email) --}}
            <div class="flex items-center gap-4 flex-wrap text-white">
                {{-- Phone --}}
                @if($site?->ribbon_phone)
                    <span class="flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M6.62 10.79a15.533 15.533 0 006.59 6.59l2.2-2.2a1.003 1.003 0 011.11-.21c1.21.49 2.53.76 3.91.76a1 1 0 011 1V20a1 1 0 01-1 1c-9.39 0-17-7.61-17-17a1 1 0 011-1h3.5a1 1 0 011 1c0 1.38.27 2.7.76 3.91a1.003 1.003 0 01-.21 1.11l-2.2 2.2z">
                            </path>
                        </svg>
                        {{ $site->ribbon_phone }}
                    </span>
                @endif

                {{-- Email --}}
                @if($site?->ribbon_email)
                    <span class="flex items-center gap-1">
                        <svg aria-hidden="true" data-prefix="far" data-icon="envelope" class="w-4 h-4 text-white"
                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                            <path fill="currentColor"
                                d="M464 64H48C21.49 64 0 85.49 0 112v288c0 26.51 21.49 48 48 48h416c26.51 0 48-21.49 48-48V112c0-26.51-21.49-48-48-48zm0 48v40.805c-22.422 18.259-58.168 46.651-134.587 106.49-16.841 13.247-50.201 45.072-73.413 44.701-23.208.375-56.579-31.459-73.413-44.701C106.18 199.465 70.425 171.067 48 152.805V112h416zM48 400V214.398c22.914 18.251 55.409 43.862 104.938 82.646 21.857 17.205 60.134 55.186 103.062 54.955 42.717.231 80.509-37.199 103.053-54.947 49.528-38.783 82.032-64.401 104.947-82.653V400H48z">
                            </path>
                        </svg>
                        <a class="r-mail" style="color: {{ $site->ribbon_text_color ?? '#ffffff' }};"
                            href="mailto:{{ $site->ribbon_email }}">
                            {{ $site->ribbon_email }}
                        </a>
                    </span>
                @endif
            </div>
        </div>
    </div>
@endif