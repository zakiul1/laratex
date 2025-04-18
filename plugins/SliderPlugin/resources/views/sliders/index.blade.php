@extends('layouts.dashboard')

@section('content')
    <div x-data="sliderIndex()" class="max-w-7xl mx-auto p-6 bg-white dark:bg-gray-900 rounded shadow">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold">All Sliders</h2>

            <a href="{{ route('slider-plugin.sliders.create') }}"
               class="inline-flex items-center gap-1 px-3 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
                <x-lucide-plus class="w-4 h-4" />
                Add New
            </a>
        </div>

        <!-- Search + Filters -->
        <form method="GET" action="{{ route('slider-plugin.sliders.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <input  type="text"
                    name="q"
                    placeholder="Search title…"
                    value="{{ request('q') }}"
                    class="col-span-2 md:col-span-1 w-full p-2 border rounded" />

            <select name="layout" class="w-full p-2 border rounded">
                <option value="">All layouts</option>
                <option value="one-column"  @selected(request('layout')==='one-column')>One Column</option>
                <option value="two-column"  @selected(request('layout')==='two-column')>Two Column</option>
            </select>

            <button type="submit" class="w-full md:w-auto px-4 py-2 bg-gray-800 text-white rounded">
                Filter
            </button>

            @if(request()->hasAny(['q','layout']))
                <a href="{{ route('slider-plugin.sliders.index') }}" class="w-full md:w-auto px-4 py-2 text-sm underline">
                    Reset
                </a>
            @endif
        </form>

        <!-- Slider list -->
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 dark:bg-gray-800 text-left whitespace-nowrap">
                    <tr>
                        <th class="px-4 py-2">#</th>
                        <th class="px-4 py-2">Thumbnail</th>
                        <th class="px-4 py-2">Title</th>
                        <th class="px-4 py-2">Layout</th>
                        <th class="px-4 py-2">Arrows</th>
                        <th class="px-4 py-2">Indicators</th>
                        <th class="px-4 py-2 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($sliders as $slider)
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <td class="px-4 py-2">{{ $sliders->firstItem() + $loop->index }}</td>

                        <!-- Thumbnail with modal preview -->
                        <td class="px-4 py-2">
                            <img  src="{{ asset('storage/' . ($slider->image ?? 'placeholder.jpg')) }}"
                                  alt=""
                                  class="h-12 w-20 object-cover rounded cursor-pointer"
                                  @click="openPreview('{{ asset('storage/' . $slider->image) }}')" />
                        </td>

                        <td class="px-4 py-2">{{ $slider->title }}</td>
                        <td class="px-4 py-2 capitalize">{{ str_replace('-', ' ', $slider->layout) }}</td>
                        <td class="px-4 py-2">{{ $slider->show_arrows ? 'Yes':'No' }}</td>
                        <td class="px-4 py-2">{{ $slider->show_indicators ? 'Yes':'No' }}</td>

                        <!-- Action buttons -->
                        <td class="px-4 py-2 text-right space-x-1">
                            <a href="{{ route('slider-plugin.sliders.edit', $slider->id) }}"
                               class="inline-flex px-2 py-1 rounded bg-yellow-500/20 text-yellow-600 hover:bg-yellow-500/30">
                                <x-lucide-pencil class="w-4 h-4" />
                            </a>

                            <form method="POST"
                                  action="{{ route('slider-plugin.sliders.destroy', $slider->id) }}"
                                  class="inline"
                                  onsubmit="return confirm('Delete this slider?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="inline-flex px-2 py-1 rounded bg-red-600/20 text-red-700 hover:bg-red-600/30">
                                    <x-lucide-trash class="w-4 h-4" />
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-6 text-center">No sliders found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">{{ $sliders->withQueryString()->links() }}</div>

        <!-- Image Preview Modal -->
        <div  x-show="previewSrc"
              x-cloak
              class="fixed inset-0 z-40 flex items-center justify-center bg-black/70 backdrop-blur-sm">
            <div class="relative max-w-2xl max-h-[90vh] p-4">
                <button  class="absolute top-1 right-1 bg-black/60 p-1 rounded-full text-white"
                         @click="previewSrc = null">✕</button>
                <img :src="previewSrc" class="w-full h-auto rounded shadow-lg" />
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function sliderIndex() {
                return {
                    previewSrc: null,
                    openPreview(src) {
                        this.previewSrc = src;
                    }
                }
            }
        </script>
    @endpush
@endsection
