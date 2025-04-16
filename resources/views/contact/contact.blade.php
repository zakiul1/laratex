@extends('layouts.app')

@section('content')
    <section class="bg-white py-12">
        <div class="max-w-7xl mx-auto px-4">

            <h1 class="text-4xl font-extrabold text-center mb-12">Contact</h1>

            {{-- Info Row --}}
            <div class="grid grid-cols-1 md:grid-cols-3 text-center border-b pb-8 gap-6">
                <div>
                    <div class="text-red-600 text-2xl mb-2">📍</div>
                    <h3 class="font-bold">Head Office</h3>
                    <p class="text-sm mt-1">{{ $contact->address }}</p>
                </div>
                <div>
                    <div class="text-red-600 text-2xl mb-2">📞</div>
                    <h3 class="font-bold">Phone</h3>
                    <p class="text-sm mt-1">{{ $contact->phone1 }}</p>
                    <p class="text-sm">{{ $contact->phone2 }}</p>
                </div>
                <div>
                    <div class="text-red-600 text-2xl mb-2">✉️</div>
                    <h3 class="font-bold">Email</h3>
                    <p class="text-sm mt-1">{{ $contact->email1 }}</p>
                    <p class="text-sm">{{ $contact->email2 }}</p>
                </div>
            </div>

            {{-- Map --}}
            <div class="mt-10">
                {!! $contact->map_embed !!}
            </div>

            {{-- Lower Row --}}
            <div class="mt-12 grid grid-cols-1 md:grid-cols-2 gap-8">
                {{-- Left Info --}}
                <div>
                    <h3 class="text-lg font-bold mb-2">Stay In Touch</h3>
                    <p class="mb-4 text-sm">Contact Us</p>

                    <p class="mb-1 text-sm">📧 <strong>Hassan Farhan:</strong> {{ $contact->email1 }}</p>
                    <p class="mb-1 text-sm">📧 <strong>M. Awais:</strong> {{ $contact->email2 }}</p>
                    <p class="mb-4 text-sm">📞 {{ $contact->phone1 }}</p>

                    <h4 class="font-semibold text-sm mt-4">SOCIAL MEDIA</h4>
                    <div class="flex gap-4 mt-2 text-xl">
                        @if($contact->social_instagram)
                            <a href="{{ $contact->social_instagram }}" target="_blank">📷</a>
                        @endif
                        @if($contact->social_facebook)
                            <a href="{{ $contact->social_facebook }}" target="_blank">📘</a>
                        @endif
                    </div>
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