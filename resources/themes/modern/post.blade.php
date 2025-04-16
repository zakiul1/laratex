@section('content')
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold mb-4">{{ $post->title }}</h1>
        <p class="text-gray-600 text-sm mb-2">Published on {{ $post->created_at->format('F j, Y') }}</p>
        <div class="prose">
            {!! $post->content !!}
        </div>
    </div>
@endsection