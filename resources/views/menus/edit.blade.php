@extends('layouts.dashboard')

@section('content')
    <div x-data="menuBuilder({{ json_encode($menu->items) }})" x-init="init" class="max-w-7xl mx-auto mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Left Panel -->
        <div class="space-y-4">
            <!-- Pages (Collapsed by default + Search + Scrollable) -->
            <div x-data="{ openPages: false, searchPages: '' }" class="bg-white dark:bg-neutral-900 shadow rounded p-4">
                <div @click="openPages = !openPages" class="flex justify-between items-center cursor-pointer mb-3">
                    <h3 class="font-semibold flex items-center gap-2 cursor-pointer">
                        📄 Pages
                    </h3>
                    <span x-text="openPages ? '▾' : '▸'" class="text-xs text-gray-500"></span>
                </div>
                <div x-show="openPages" x-collapse>
                    <input type="text" x-model="searchPages" placeholder="Search pages..."
                        class="w-full mb-2 border rounded px-2 py-1 text-sm" />
                    <div class="max-h-60 overflow-y-auto space-y-1 divide-y divide-gray-200">
                        @foreach (\App\Models\Post::where('type', 'page')->get() as $page)
                            <template x-if="'{{ strtolower($page->title) }}'.includes(searchPages.toLowerCase())">
                                <div class="flex justify-between text-sm py-1 items-center">
                                    <span class="flex items-center gap-1">
                                        📝 {{ $page->title }}
                                    </span>
                                    <button type="button"
                                        @click="addItem({ title: '{{ $page->title }}', type: 'page', reference_id: {{ $page->id }}, url: '{{ route('page.show', $page->slug) }}' })"
                                        class="px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 cursor-pointer">Add</button>
                                </div>
                            </template>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Categories (Collapsed by default + Search + Scrollable) -->
            <div x-data="{ openCategories: false, searchCategories: '' }" class="bg-white dark:bg-neutral-900 shadow rounded p-4">
                <div @click="openCategories = !openCategories"
                    class="flex justify-between items-center cursor-pointer mb-3">
                    <h3 class="font-semibold flex items-center gap-2 cursor-pointer">
                        💂️ Categories
                    </h3>
                    <span x-text="openCategories ? '▾' : '▸'" class="text-xs text-gray-500"></span>
                </div>
                <div x-show="openCategories" x-collapse>
                    <input type="text" x-model="searchCategories" placeholder="Search categories..."
                        class="w-full mb-2 border rounded px-2 py-1 text-sm" />
                    <div class="max-h-60 overflow-y-auto space-y-1 divide-y divide-gray-200">
                        @foreach (\App\Models\Category::all() as $category)
                            <template x-if="'{{ strtolower($category->name) }}'.includes(searchCategories.toLowerCase())">
                                <div class="flex justify-between text-sm py-1 items-center">
                                    <span class="flex items-center gap-1">
                                        🏷️ {{ $category->name }}
                                    </span>
                                    <button type="button"
                                        @click="addItem({ title: '{{ $category->name }}', type: 'category', reference_id: {{ $category->id }}, url: '{{ route('categories.show', $category->slug) }}' })"
                                        class="px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 cursor-pointer">Add</button>
                                </div>
                            </template>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Posts (Collapsed by default + Search + Scrollable Frame) -->
            <div x-data="{ openPosts: false, search: '' }" class="bg-white dark:bg-neutral-900 shadow rounded p-4">
                <div @click="openPosts = !openPosts" class="flex justify-between items-center cursor-pointer mb-3">
                    <h3 class="font-semibold flex items-center gap-2 cursor-pointer">
                        📰 Posts
                    </h3>
                    <span x-text="openPosts ? '▾' : '▸'" class="text-xs text-gray-500"></span>
                </div>
                <div x-show="openPosts" x-collapse>
                    <input type="text" x-model="search" placeholder="Search posts..."
                        class="w-full mb-2 border rounded px-2 py-1 text-sm" />
                    <div class="max-h-60 overflow-y-auto space-y-1 divide-y divide-gray-200">
                        @foreach (\App\Models\Post::where('type', 'post')->get() as $post)
                            <template x-if="'{{ strtolower($post->title) }}'.includes(search.toLowerCase())">
                                <div class="flex justify-between text-sm py-1 items-center">
                                    <span class="flex items-center gap-1">
                                        ✍️ {{ $post->title }}
                                    </span>
                                    <button type="button"
                                        @click="addItem({ title: '{{ $post->title }}', type: 'post', reference_id: {{ $post->id }}, url: '/blog/{{ $post->slug }}' })"
                                        class="px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 cursor-pointer">Add</button>
                                </div>
                            </template>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Custom Link -->
            <div class="bg-white dark:bg-neutral-900 shadow rounded p-4 space-y-2">
                <h3 class="font-semibold flex items-center gap-2">
                    🔗 Custom Link
                </h3>
                <input type="text" x-model="customLink.title" placeholder="Link Text"
                    class="w-full border rounded px-2 py-1 text-sm" />
                <input type="text" x-model="customLink.url" placeholder="URL"
                    class="w-full border rounded px-2 py-1 text-sm" />
                <button @click="addItem({ ...customLink, type: 'custom' }); customLink = { title: '', url: '' }"
                    class="bg-blue-600 text-white text-sm px-4 py-2 rounded hover:bg-blue-700 cursor-pointer">Add to
                    Menu</button>
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
                        <input type="checkbox" name="auto_add_pages" id="auto_add_pages"
                            {{ $menu->auto_add_pages ? 'checked' : '' }}>
                        <label for="auto_add_pages" class="text-sm">Automatically add new top-level pages to this
                            menu</label>
                    </div>
                    <div class="flex items-center gap-6">
                        <label class="flex items-center gap-2">
                            <input type="radio" name="location" value="header"
                                {{ $menu->location == 'header' ? 'checked' : '' }}>
                            Header
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="location" value="footer"
                                {{ $menu->location == 'footer' ? 'checked' : '' }}>
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
                <ul class="space-y-2" x-ref="menuList">
                    <template x-for="(item, index) in items" :key="item.id || index">
                        <li class="menu-item" :data-id="index" x-data="{ open: true }">
                            <div
                                class="drag-handle cursor-move px-4 py-2 flex justify-between items-center bg-gray-100 dark:bg-neutral-800 rounded border border-gray-200">
                                <div class="flex items-center gap-2">
                                    <button @click="open = !open" class="text-xs" x-show="item.children?.length">
                                        <span x-show="open">▾</span>
                                        <span x-show="!open">▸</span>
                                    </button>
                                    <span class="font-medium" x-text="item.title"></span>
                                </div>
                                <button @click="removeItem(index)"
                                    class="text-red-500 text-sm hover:underline">Remove</button>
                            </div>
                            <ul class="ml-6 space-y-2 mt-2" x-ref="el => registerNested(el, index)"
                                x-show="open && item.children && item.children.length" x-collapse>
                                <template x-for="(child, cIndex) in item.children" :key="child.id || cIndex">
                                    <li class="menu-item" :data-id="`${index}-${cIndex}`" x-data="{ openChild: true }">
                                        <div
                                            class="drag-handle cursor-move px-4 py-2 flex justify-between items-center bg-gray-200 dark:bg-neutral-700 rounded border border-gray-300">
                                            <div class="flex items-center gap-2">
                                                <button @click="openChild = !openChild" class="text-xs"
                                                    x-show="child.children?.length">
                                                    <span x-show="openChild">▾</span>
                                                    <span x-show="!openChild">▸</span>
                                                </button>
                                                <span class="font-medium" x-text="child.title"></span>
                                            </div>
                                            <button @click="removeChild(index, cIndex)"
                                                class="text-red-500 text-sm hover:underline">Remove</button>
                                        </div>
                                        <ul class="ml-6 space-y-2 mt-2" x-ref="el => registerNested(el, index, cIndex)"
                                            x-show="openChild && child.children && child.children.length" x-collapse>
                                            <template x-for="(subChild, scIndex) in child.children"
                                                :key="subChild.id || scIndex">
                                                <li class="menu-item" :data-id="`${index}-${cIndex}-${scIndex}`">
                                                    <div
                                                        class="drag-handle cursor-move px-4 py-2 flex justify-between items-center bg-gray-300 dark:bg-neutral-600 rounded border border-gray-400">
                                                        <span class="font-medium" x-text="subChild.title"></span>
                                                        <button @click="removeSubChild(index, cIndex, scIndex)"
                                                            class="text-red-500 text-sm hover:underline">Remove</button>
                                                    </div>
                                                </li>
                                            </template>
                                        </ul>
                                    </li>
                                </template>
                            </ul>
                        </li>
                    </template>
                </ul>
                <button @click="saveMenu"
                    class="mt-6 bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 text-sm">
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
                items: JSON.parse(JSON.stringify(initialItems || [])).map((item, index) => ({
                    ...item,
                    id: item.id || `item-${index}`,
                    children: (item.children || []).map((child, cIndex) => ({
                        ...child,
                        id: child.id || `child-${index}-${cIndex}`,
                        children: (child.children || []).map((subChild, scIndex) => ({
                            ...subChild,
                            id: subChild.id || `subchild-${index}-${cIndex}-${scIndex}`
                        }))
                    }))
                })),
                customLink: {
                    title: '',
                    url: ''
                },
                maxDepth: 3,
                indentThreshold: 25, // Adjusted for better sensitivity

                addItem(item) {
                    this.items.push({
                        ...item,
                        id: `item-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`,
                        children: []
                    });
                },

                removeItem(index) {
                    this.items.splice(index, 1);
                },

                removeChild(pIndex, cIndex) {
                    this.items[pIndex].children.splice(cIndex, 1);
                },

                removeSubChild(pIndex, cIndex, scIndex) {
                    this.items[pIndex].children[cIndex].children.splice(scIndex, 1);
                },

                saveMenu() {
                    fetch(`{{ route('menus.updateStructure', $menu) }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            items: this.items
                        })
                    }).then(r => r.json()).then(() => alert('Menu structure saved!'));
                },

                init() {
                    this.makeSortable(this.$refs.menuList, this.items, 1);
                },

                makeSortable(el, list, level) {
                    if (level > this.maxDepth) return;

                    Sortable.create(el, {
                        group: 'nested-menu',
                        animation: 150,
                        handle: '.drag-handle',
                        ghostClass: 'bg-yellow-100',
                        dragClass: 'bg-gray-100',
                        forceFallback: true,
                        onEnd: ({
                            oldIndex,
                            newIndex,
                            from,
                            to,
                            item: draggedItem
                        }) => {
                            const fromPath = this.getItemPath(from);
                            const toPath = this.getItemPath(to);
                            let sourceList = this.items;
                            let targetList = this.items;

                            // Navigate to the source list
                            if (fromPath.length > 0) {
                                sourceList = fromPath.reduce((list, idx) => list[idx].children, this.items);
                            }

                            // Navigate to the target list
                            if (toPath.length > 0) {
                                targetList = toPath.reduce((list, idx) => list[idx].children, this.items);
                            }

                            // Remove the item from the source list
                            const movedItem = sourceList.splice(oldIndex, 1)[0];

                            // Calculate indentation level based on mouse position
                            const mouseX = event.clientX;
                            const containerX = to.getBoundingClientRect().left;
                            const offsetX = mouseX - containerX;
                            const currentLevel = fromPath.length + 1;
                            const targetLevel = toPath.length + 1;

                            // Determine if the item should be nested or un-nested
                            const shouldNest = offsetX > this.indentThreshold * targetLevel && newIndex > 0;
                            const shouldUnNest = offsetX < this.indentThreshold * (targetLevel - 1);

                            if (shouldNest && level < this.maxDepth) {
                                // Nest under the previous item
                                const parentIndex = newIndex - 1;
                                if (!targetList[parentIndex].children) {
                                    targetList[parentIndex].children = [];
                                }
                                targetList[parentIndex].children.push(movedItem);
                            } else if (shouldUnNest && targetLevel > 1) {
                                // Un-nest to the parent level
                                const parentPath = toPath.slice(0, -1);
                                let parentList = this.items;
                                if (parentPath.length > 0) {
                                    parentList = parentPath.reduce((list, idx) => list[idx].children, this
                                        .items);
                                }
                                const parentIndex = toPath[toPath.length - 1];
                                parentList.splice(parentIndex + 1, 0, movedItem);
                            } else {
                                // Place in the target list at the new index
                                targetList.splice(newIndex, 0, movedItem);
                            }

                            // Ensure the dragged item's nested list is sortable
                            const nestedUl = draggedItem.querySelector('ul');
                            if (nestedUl && movedItem.children) {
                                this.makeSortable(nestedUl, movedItem.children, level + 1);
                            }
                        }
                    });

                    // Initialize nested lists
                    [...el.children].forEach((li, i) => {
                        const subList = li.querySelector('ul');
                        if (subList && list[i] && list[i].children) {
                            this.makeSortable(subList, list[i].children, level + 1);
                        }
                    });
                },

                registerNested(el, parentIndex, childIndex = null) {
                    if (!el || el.classList.contains('sortable-registered')) return;
                    el.classList.add('sortable-registered');
                    let targetList = this.items[parentIndex].children;
                    let level = 2;
                    if (childIndex !== null) {
                        targetList = this.items[parentIndex].children[childIndex].children;
                        level = 3;
                    }
                    this.makeSortable(el, targetList, level);
                },

                getItemPath(el) {
                    const path = [];
                    let current = el.closest('li.menu-item');
                    while (current && current !== this.$refs.menuList) {
                        const parentUl = current.parentElement.closest('ul');
                        if (parentUl) {
                            const siblings = [...parentUl.children];
                            const index = siblings.indexOf(current);
                            path.unshift(index);
                        }
                        current = parentUl.closest('li.menu-item');
                    }
                    return path;
                }
            };
        }
    </script>
@endpush
