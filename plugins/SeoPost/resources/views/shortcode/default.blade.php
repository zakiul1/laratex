{{-- plugins/SeoPost/resources/views/shortcode/default.blade.php --}}
<div class="{{ $wrapperClass }}">
    <div class="grid grid-cols-2 gap-4">
      @foreach($posts as $post)
        <div class="post-item p-4 border rounded">
          @if($settings['img'] === 'yes' && isset($post->thumbnail_url))
            <img src="{{ $post->thumbnail_url }}" alt="{{ $post->title }}" class="mb-2 w-full" />
          @endif
  
          <h3 class="text-lg font-semibold">{{ $post->title }}</h3>
  
          @if($settings['excerpt-hide'])
            <p>{{ Str::limit($post->excerpt, (int) $settings['excerpt-hide']) }}</p>
          @else
            <p>{{ $post->excerpt }}</p>
          @endif
  
          @if($settings['get-price'] === 'yes')
            <button class="mt-2 px-3 py-1 bg-blue-600 text-white rounded">Get Price</button>
          @endif
        </div>
      @endforeach
    </div>
  </div>
  