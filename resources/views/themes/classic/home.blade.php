@extends('layout')

@section('content')
    <div class="">
        {!! apply_filters('slider.header', '') !!}
    </div>



    {{-- Category Section View --}}

    @php
        use Illuminate\Support\Facades\Schema;
        use App\Models\Category;

        $categories = collect();

        if (Schema::hasTable('categories')) {
            $query = Category::query();

            // only filter by `is_active` if that column exists
            if (Schema::hasColumn('categories', 'is_active')) {
                $query->where('is_active', true);
            }

            // only order by `sort_order` if that column exists
            if (Schema::hasColumn('categories', 'sort_order')) {
                $query->orderBy('sort_order', 'asc');
            }

            // finally limit to 3
            $categories = $query->take(3)->get();
        }
    @endphp

    @if ($categories->isNotEmpty())
        <section class="py-12 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4">
                <h2 class="text-3xl font-bold mb-8 text-center">Shop by Category</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($categories as $category)
                        {{-- Check if the category has a featured image --}}
                        <a href="{{ route('categories.show', $category->slug) }}"
                            class="group block relative overflow-hidden rounded-xl shadow-lg hover:shadow-2xl transition-shadow">
                            <img src="{{ asset('storage/' . $category->featured_image) }}" alt="{{ $category->name }}"
                                class="w-full h-auto object-cover group-hover:scale-105 transform transition-transform duration-500" />
                            <div class="absolute inset-0 bg-black bg-opacity-30 flex items-end p-4">
                                <h3 class="text-white text-xl font-semibold">{{ $category->name }}</h3>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Featured Products Section --}}

    {!! apply_filters('the_content', $page->content) !!}



@endsection













@push('scripts')
@endpush
