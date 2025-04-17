@extends('themes.classic.layout')

@section('title', $page->meta_title ?? $page->title)

@section('meta')
    @if (!empty($page->meta_description))
        <meta name="description" content="{{ $page->meta_description }}">
    @endif
@endsection

@section('content')
    <section class="bg-white py-12">
        <div class="max-w-7xl mx-auto px-4">

            <h1 class="text-4xl font-extrabold text-center mb-12">{{ $page->title }}</h1>

            {{-- Info Row --}}
            <div class="grid grid-cols-1 md:grid-cols-3 text-center border-b pb-8 gap-6">
                @if (!empty($contact->address))
                    <div>
                        <div class="text-red-600 text-2xl mb-2">📍</div>
                        <h3 class="font-bold">Head Office</h3>
                        <p class="text-sm mt-1">{{ $contact->address }}</p>
                    </div>
                @endif

                @if (!empty($contact->phone1) || !empty($contact->phone2))
                    <div>
                        <div class="text-red-600 text-2xl mb-2">📞</div>
                        <h3 class="font-bold">Phone</h3>
                        @if (!empty($contact->phone1))
                            <p class="text-sm mt-1">{{ $contact->phone1 }}</p>
                        @endif
                        @if (!empty($contact->phone2))
                            <p class="text-sm">{{ $contact->phone2 }}</p>
                        @endif
                    </div>
                @endif

                @if (!empty($contact->email1) || !empty($contact->email2))
                    <div>
                        <div class="text-red-600 text-2xl mb-2">✉️</div>
                        <h3 class="font-bold">Email</h3>
                        @if (!empty($contact->email1))
                            <p class="text-sm mt-1">{{ $contact->email1 }}</p>
                        @endif
                        @if (!empty($contact->email2))
                            <p class="text-sm">{{ $contact->email2 }}</p>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Map --}}
            @if (!empty($contact->map_embed))
                <div class="mt-10">
                    {!! $contact->map_embed !!}
                </div>
            @endif

            {{-- Lower Row --}}
            <div class="mt-12 grid grid-cols-1 md:grid-cols-2 gap-8">
                {{-- Left Info --}}
                <div>
                    <h3 class="text-lg font-bold mb-2">Stay In Touch</h3>
                    <p class="mb-4 text-sm">Contact Us</p>

                    @if (!empty($contact->email1))
                        <p class="mb-1 text-sm">📧 {{ $contact->email1 }}</p>
                    @endif
                    @if (!empty($contact->email2))
                        <p class="mb-1 text-sm">📧 {{ $contact->email2 }}</p>
                    @endif
                    @if (!empty($contact->phone1))
                        <p class="mb-4 text-sm">📞 {{ $contact->phone1 }}</p>
                    @endif

                    @if (!empty($contact->social_instagram) || !empty($contact->social_facebook))
                        <h4 class="font-semibold text-sm mt-4">SOCIAL MEDIA</h4>
                        <div class="flex gap-4 mt-2 text-xl">
                            @if ($contact->social_instagram)
                                <a href="{{ $contact->social_instagram }}" target="_blank">📷</a>
                            @endif
                            @if ($contact->social_facebook)
                                <a href="{{ $contact->social_facebook }}" target="_blank">📘</a>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Contact Form --}}
                <div>
                    <h3 class="text-lg font-bold mb-4">Got Any Questions?</h3>

                    @if(session('success'))
                        <div class="mb-4 text-green-600">{{ session('success') }}</div>
                    @endif

                    <form method="POST" action="{{ route('contact.submit') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium">Name *</label>
                            <input type="text" name="name" required class="w-full mt-1 p-2 border border-gray-300 rounded">
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Email *</label>
                            <input type="email" name="email" required
                                class="w-full mt-1 p-2 border border-gray-300 rounded">
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Comment or Message *</label>
                            <textarea name="message" required rows="4"
                                class="w-full mt-1 p-2 border border-gray-300 rounded"></textarea>
                        </div>
                        <button type="submit" class="bg-black text-white px-6 py-2 rounded hover:bg-gray-800 text-sm">
                            SUBMIT
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection