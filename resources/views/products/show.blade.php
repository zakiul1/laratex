@extends('layouts.dashboard')

@section('content')
    <div class="max-w-5xl mx-auto px-4 py-6 bg-white shadow rounded">

        <h2 class="text-2xl font-semibold mb-4">{{ $product->name }}</h2>

        <div class="mb-6 text-gray-700 space-y-2">
            <p><strong>Description:</strong></p>
            <div class="border p-3 rounded text-sm bg-gray-50">
                {!! nl2br(e($product->description)) !!}
            </div>

            <p><strong>Price:</strong> ${{ number_format($product->price, 2) }}</p>
            <p><strong>Category:</strong> {{ $product->category->name ?? 'N/A' }}</p>
            <p><strong>Status:</strong>
                <span
                    class="inline-block px-2 py-1 rounded text-xs {{ $product->status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-700' }}">
                    {{ $product->status ? 'Active' : 'Inactive' }}
                </span>
            </p>
        </div>

        <div class="mb-6">
            <h3 class="font-semibold text-lg mb-2">Product Images</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @forelse ($product->images as $image)
                    <div class="border rounded overflow-hidden">
                        <img src="{{ asset('storage/' . $image->image) }}" class="w-full h-40 object-cover"
                            alt="Product Image" />
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No images uploaded.</p>
                @endforelse
            </div>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('products.edit', $product->id) }}"
                class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-sm rounded">
                Edit
            </a>
            <form action="{{ route('products.destroy', $product->id) }}" method="POST"
                onsubmit="return confirm('Are you sure you want to delete this product?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm rounded">
                    Delete
                </button>
            </form>
        </div>

    </div>
@endsection