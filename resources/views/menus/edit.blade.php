@extends('layouts.dashboard')

@section('content')
<div x-data="menuBuilder({{ json_encode($menu->items) }})" x-init="init"
     class="max-w-7xl mx-auto mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">

    <!-- Left Panel: Add Menu Items -->
    <div class="space-y-4">
        <!-- Pages -->
        <div class="bg-white dark:bg-neutral-900 shadow rounded p-4">
            <h3 class="font-semibold mb-3">Pages</h3>
            @foreach (\App\Models\Post::where('type', 'page')->get() as $page)
                <div class="flex justify-between text-sm py-1">
                    <span>{{ $page->title }}</span>
                    <button type="button" @click="addItem({ title: '{{ $page->title }}', type: 'page', reference_id: {{ $page->id }} })"
                            class="text-blue-600 hover:underline">Add</button>
                </div>
            @endforeach
        </div>

        <!-- Posts -->
        <div class="bg-white dark:bg-neutral-900 shadow rounded p-4">
            <h3 class="font-semibold mb-3">Posts</h3>
            @foreach (\App\Models\Post::where('type', 'post')->limit(5)->get() as $post)
                <div class="flex justify-between text-sm py-1">
                    <span>{{ $post->title }}</span>
                    <button type="button" @click="addItem({ title: '{{ $post->title }}', type: 'post', reference_id: {{ $post->id }} })"
                            class="text-blue-600 hover:underline">Add</button>
                </div>
            @endforeach
        </div>

        <!-- Categories -->
        <div class="bg-white dark:bg-neutral-900 shadow rounded p-4">
            <h3 class="font-semibold mb-3">Categories</h3>
            @foreach (\App\Models\Category::all() as $cat)
                <div class="flex justify-between text-sm py-1">
                    <span>{{ $cat->name }}</span>
                    <button type="button" @click="addItem({ title: '{{ $cat->name }}', type: 'category', reference_id: {{ $cat->id }} })"
                            class="text-blue-600 hover:underline">Add</button>
                </div>
            @endforeach
        </div>

        <!-- Custom Link -->
        <div class="bg-white dark:bg-neutral-900 shadow rounded p-4 space-y-2">
            <h3 class="font-semibold">Custom Link</h3>
            <input type="text" x-model="customLink.title" placeholder="Link Text"
                   class="w-full border rounded px-2 py-1 text-sm" />
            <input type="text" x-model="customLink.url" placeholder="URL"
                   class="w-full border rounded px-2 py-1 text-sm" />
            <button @click="addItem({ ...customLink, type: 'custom' }); customLink = { title: '', url: '' }"
                    class="bg-blue-600 text-white text-sm px-4 py-2 rounded hover:bg-blue-700">Add to Menu</button>
        </div>
    </div>

    <!-- Right Panel: Menu Structure -->
    <div class="md:col-span-2 space-y-6">
        <form method="POST" action="{{ route('menus.update', $menu) }}">
            @csrf
            @method('PUT')

            <div class="bg-white dark:bg-neutral-900 shadow rounded p-4 space-y-4">
                <h3 class="text-lg font-semibold">Menu Structure</h3>

                <div>
                    <label class="text-sm font-medium">Menu Name</label>
                    <input type="text" name="name" value="{{ $menu->name }}"
                           class="mt-1 w-full border rounded px-3 py-2 text-sm">
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="auto_add_pages" id="auto_add_pages" {{ $menu->auto_add_pages ? 'checked' : '' }}>
                    <label for="auto_add_pages" class="text-sm">Automatically add new top-level pages to this menu</label>
                </div>

                <div class="flex items-center gap-6">
                    <label class="flex items-center gap-2">
                        <input type="radio" name="location" value="header" {{ $menu->location == 'header' ? 'checked' : '' }}>
                        Header
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="radio" name="location" value="footer" {{ $menu->location == 'footer' ? 'checked' : '' }}>
                        Footer
                    </label>
                </div>

                <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded text-sm hover:bg-blue-700">Save Menu</button>
                <a href="{{ route('menus.destroy', $menu) }}"
                   onclick="event.preventDefault(); document.getElementById('delete-menu').submit();"
                   class="ml-4 text-red-600 text-sm hover:underline">Delete Menu</a>
            </div>
        </form>

        <!-- Menu Builder -->
        <div class="bg-white dark:bg-neutral-900 shadow rounded p-4">
            <h3 class="text-lg font-semibold mb-2">Menu Items</h3>
            <ul class="space-y-2" x-ref="menuList">
                <template x-for="(item, index) in items" :key="index">
                    <li class="bg-gray-100 dark:bg-neutral-800 px-4 py-2 rounded flex justify-between items-center">
                        <span x-text="item.title"></span>
                        <button @click="removeItem(index)" class="text-red-500 text-sm hover:underline">Remove</button>
                    </li>
                </template>
            </ul>

            <button @click="saveMenu"
                    class="mt-4 bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 text-sm">
                Save Menu Structure
            </button>
        </div>
    </div>

    <form id="delete-menu" action="{{ route('menus.destroy', $menu) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection

@push('scripts')
<script>
function menuBuilder(initialItems) {
    return {
        items: initialItems || [],
        customLink: { title: '', url: '' },
        addItem(item) {
            this.items.push(item);
        },
        removeItem(index) {
            this.items.splice(index, 1);
        },
        saveMenu() {
            fetch(`{{ route('menus.updateStructure', $menu) }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ items: this.items })
            }).then(r => r.json())
              .then(d => alert('Menu structure saved!'));
        },
        init() {
            Sortable.create(this.$refs.menuList, {
                animation: 150,
                onEnd: evt => {
                    const moved = this.items.splice(evt.oldIndex, 1)[0];
                    this.items.splice(evt.newIndex, 0, moved);
                }
            });
        }
    }
}
</script>
@endpush
