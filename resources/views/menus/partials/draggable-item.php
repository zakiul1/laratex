<li class="menu-item border rounded p-3 bg-gray-50 cursor-move" data-item='@json($item)'>
    <div class="flex justify-between items-center">
        <span class="font-medium">{{ $item['title'] }}</span>
        <button @click.stop="$dispatch('remove', {el:$el})" class="text-red-500 hover:text-red-700">Remove</button>
    </div>
    @if (!empty($item['children']))
        <ul class="ml-6 mt-2 space-y-2">
            @foreach ($item['children'] as $child)
                @include('menus.partials.draggable-item', ['item' => $child])
            @endforeach
        </ul>
    @endif
</li>
