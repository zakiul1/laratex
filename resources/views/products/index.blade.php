@extends('layouts.dashboard')

@section('content')
    @php
        use Illuminate\Support\Facades\Storage;
    @endphp

    <div class="max-w-7xl mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-xl font-semibold">All Products</h1>
            <a href="{{ route('products.create') }}"
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md">
                + Add New Product
            </a>
        </div>

        @if (session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">
                {{ session('success') }}
            </div>
        @endif

        <div class="overflow-x-auto bg-white shadow rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 text-left text-sm font-semibold text-gray-700">
                    <tr>
                        <th class="px-4 py-2">#</th>
                        <th class="px-4 py-2">Image</th>
                        <th class="px-4 py-2">Name</th>
                        <th class="px-4 py-2">Categories</th>
                        <th class="px-4 py-2">Price</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                    @forelse($products as $product)
                        <tr>
                            <td class="px-4 py-2">
                                {{ $loop->iteration + ($products->currentPage() - 1) * $products->perPage() }}
                            </td>

                            {{-- FEATURED IMAGE --}}
                            <td class="px-4 py-2">
                                @php
                                    $media = $product->featuredMedia->first();
                                    // try Spatie's getUrl(), fallback to disk path
if ($media && method_exists($media, 'getUrl')) {
    $url = $media->getUrl();
} elseif ($media && $media->path) {
    $url = Storage::disk('public')->url($media->path);
                                    } else {
                                        $url = null;
                                    }
                                @endphp

                                @if ($url)
                                    <img src="{{ $url }}" alt="{{ $product->name }}"
                                        class="w-16 h-16 object-cover rounded border" />
                                @else
                                    <span class="text-gray-400">No Image</span>
                                @endif
                            </td>

                            <td class="px-4 py-2">{{ $product->name }}</td>
                            <td class="px-4 py-2">
                                {{ $product->taxonomies->pluck('term.name')->join(', ') ?: '-' }}
                            </td>
                            <td class="px-4 py-2">{{ $product->price ?? '-' }}</td>
                            <td class="px-4 py-2">
                                <span class="{{ $product->status ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $product->status ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-2">
                                <div class="flex items-center gap-2">
                                    <!-- View button -->
                                    <a href="{{ route('products.show', $product->slug) }}" target="_blank"
                                        class="text-xs px-3 py-1 rounded bg-green-600 hover:bg-green-700 text-white">
                                        View
                                    </a>
                                    <a href="{{ route('products.edit', $product) }}"
                                        class="text-xs px-3 py-1 rounded bg-yellow-500 hover:bg-yellow-600 text-white">
                                        Edit
                                    </a>
                                    <form action="{{ route('products.destroy', $product) }}" method="POST"
                                        onsubmit="return confirm('Are you sure?');">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="text-xs px-3 py-1 rounded bg-red-600 hover:bg-red-700 text-white">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                                No products found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $products->links() }}
        </div>
    </div>
@endsection
