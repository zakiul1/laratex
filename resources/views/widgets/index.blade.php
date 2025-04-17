@extends('layouts.dashboard')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Manage Widgets</h1>

    @foreach ($widgets as $area => $widgetGroup)
        <div class="mb-10">
            <h2 class="text-lg font-semibold mb-2">{{ ucfirst(str_replace('_', ' ', $area)) }}</h2>

            <div x-data="sortableWidgetArea" class="space-y-2">
                @foreach ($widgetGroup as $widget)
                    <div class="p-3 bg-white shadow border rounded flex justify-between items-center" data-id="{{ $widget->id }}">
                        <div>
                            <strong>{{ $widget->title }}</strong>
                            <small class="text-gray-500">({{ $widget->widget_type }})</small>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('widgets.edit', $widget) }}" class="text-sm text-blue-600 hover:underline">Edit</a>
                            <form method="POST" action="{{ route('widgets.destroy', $widget) }}"
                                onsubmit="return confirm('Delete this widget?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-red-600 hover:underline">Delete</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    <div class="mt-8">
        <a href="{{ route('widgets.create') }}"
            class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
            + Add New Widget
        </a>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('sortableWidgetArea', () => ({
                init() {
                    Sortable.create(this.$el, {
                        animation: 150,
                        onEnd: () => {
                            const orders = {};
                            const items = Array.from(this.$el.querySelectorAll('[data-id]'));
                            items.forEach((el, index) => {
                                orders[el.dataset.id] = index;
                            });

                            fetch('{{ route('widgets.reorder') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ orders })
                            });
                        }
                    });
                }
            }));
        });
    </script>
@endpush