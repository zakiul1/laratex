@extends('layouts.dashboard')

@section('content')
    <div x-data="menuBuilder({{ json_encode($menu->items) }})" x-init="init"
        class="max-w-7xl mx-auto mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Left Panel -->
        <div class="space-y-4">
            <!-- Pages -->
            <div class="bg-white dark:bg-neutral-900 shadow rounded p-4">
                <h3 class="font-semibold mb-3">Pages</h3>
                @foreach (\App\Models\Post::where('type', 'page')->get() as $page)
                    <div class="flex justify-between text-sm py-1">
                        <span>{{ $page->title }}</span>
                        <button type="button"
                            @click="addItem({ title: '{{ $page->title }}', type: 'page', reference_id: {{ $page->id }} })"
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

        <!-- Right Panel -->
        <div class="md:col-span-2 space-y-6">
            <!-- Save Menu Settings -->
            <form method="POST" action="{{ route('menus.update', $menu) }}">
                @csrf
                @method('PUT')

                <div class="bg-white dark:bg-neutral-900 shadow rounded p-4 space-y-4">
                    <h3 class="text-lg font-semibold">Menu Settings</h3>

                    <div>
                        <label class="text-sm font-medium">Menu Name</label>
                        <input type="text" name="name" value="{{ $menu->name }}"
                            class="mt-1 w-full border rounded px-3 py-2 text-sm">
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="auto_add_pages" id="auto_add_pages" {{ $menu->auto_add_pages ? 'checked' : '' }}>
                        <label for="auto_add_pages" class="text-sm">Automatically add new top-level pages to this
                            menu</label>
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

                    <div class="flex items-center gap-4">
                        <button type="submit"
                            class="bg-blue-600 text-white px-5 py-2 rounded text-sm hover:bg-blue-700">Save Menu</button>
                        <a href="{{ route('menus.destroy', $menu) }}"
                            onclick="event.preventDefault(); document.getElementById('delete-menu').submit();"
                            class="text-red-600 text-sm hover:underline">Delete Menu</a>
                    </div>
                </div>
            </form>

            <form id="delete-menu" action="{{ route('menus.destroy', $menu) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>

            <!-- Menu Structure Drag & Drop -->
            <div class="bg-white dark:bg-neutral-900 shadow rounded p-4">
                <h3 class="text-lg font-semibold mb-2">Menu Items</h3>
                <ul class="space-y-4" x-ref="menuList">
                    <template x-for="(item, index) in items" :key="index">
                        <li class="menu-item" :data-id="index" x-data="{ open: true }">
                            <div
                                class="drag-handle cursor-move px-4 py-2 flex justify-between items-center bg-gray-100 dark:bg-neutral-800 rounded">
                                <div class="flex items-center gap-2">
                                    <button @click="open = !open" x-show="item.children?.length" class="text-xs">
                                        <span x-show="open">▾</span>
                                        <span x-show="!open">▸</span>
                                    </button>
                                    <span x-text="item.title"></span>
                                </div>
                                <button @click="removeItem(index)"
                                    class="text-red-500 text-sm hover:underline">Remove</button>
                            </div>
                            <ul class="ml-6 space-y-4 mt-2" x-ref="el => registerNested(el, index)"
                                x-show="open && item.children && item.children.length">
                                <template x-for="(child, cIndex) in item.children" :key="cIndex">
                                    <li
                                        class="menu-item bg-gray-200 dark:bg-neutral-700 px-4 py-2 rounded flex justify-between items-center drag-handle cursor-move">
                                        <span x-text="child.title"></span>
                                        <button @click="removeChild(index, cIndex)"
                                            class="text-red-500 text-sm hover:underline">Remove</button>
                                    </li>
                                </template>
                            </ul>
                        </li>
                    </template>
                </ul>
                <button @click="saveMenu" class="mt-6 bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 text-sm">
                    Save Menu Structure
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function menuBuilder(initialItems) {
            return {
                items: JSON.parse(JSON.stringify(initialItems || [])),
                customLink: { title: '', url: '' },
                maxDepth: 3,

                addItem(item) {
                    this.items.push({ ...item, children: [] });
                },

                removeItem(index) {
                    this.items.splice(index, 1);
                },

                removeChild(pIndex, cIndex) {
                    this.items[pIndex].children.splice(cIndex, 1);
                },

                saveMenu() {
                    fetch(`{{ route('menus.updateStructure', $menu) }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ items: this.items })
                    }).then(r => r.json()).then(() => alert('Menu structure saved!'));
                },

                init() {
                    this.makeSortable(this.$refs.menuList, this.items, 1);
                },

                makeSortable(el, list, level) {
                    if (level > this.maxDepth) return;

                    Sortable.create(el, {
                        group: 'nested',
                        animation: 150,
                        fallbackOnBody: true,
                        handle: '.drag-handle',
                        ghostClass: 'bg-yellow-100',

                        onEnd: evt => {
                            const movedItem = list.splice(evt.oldIndex, 1)[0];
                            const indentThreshold = 30;
                            const mouseX = evt.originalEvent.clientX;
                            const containerX = el.getBoundingClientRect().left;
                            const offsetX = mouseX - containerX;

                            const previous = evt.to.children[evt.newIndex - 1];
                            if (previous && offsetX > indentThreshold) {
                                const prevIndex = evt.newIndex - 1;
                                const parent = list[prevIndex];
                                if (!parent.children) parent.children = [];
                                if (level < this.maxDepth) {
                                    parent.children.push(movedItem);
                                } else {
                                    list.splice(evt.newIndex, 0, movedItem);
                                }
                            } else {
                                if (evt.from !== evt.to) {
                                    const parentLi = evt.from.closest('li');
                                    if (parentLi) {
                                        const parentIndex = [...this.$refs.menuList.children].indexOf(parentLi);
                                        if (this.items[parentIndex]?.children) {
                                            const fromList = this.items[parentIndex].children;
                                            fromList.splice(evt.oldIndex, 1);
                                        }
                                    }
                                }
                                list.splice(evt.newIndex, 0, movedItem);
                            }
                        }
                    });

                    [...el.children].forEach((li, i) => {
                        const subList = li.querySelector('ul');
                        if (subList && list[i]) {
                            if (!list[i].children) list[i].children = [];
                            this.makeSortable(subList, list[i].children, level + 1);
                        }
                    });
                },

                registerNested(el, parentIndex) {
                    if (!el || el.classList.contains('sortable-registered')) return;
                    el.classList.add('sortable-registered');
                    this.makeSortable(el, this.items[parentIndex].children, 2);
                }
            }
        }
    </script>
@endpush