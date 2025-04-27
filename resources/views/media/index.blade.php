{{-- resources/views/media/index.blade.php --}}
@extends('layouts.dashboard')

@section('content')
<div
  x-data='mediaLibrary(@json($mediaData), @json($categories))'
  class="p-6 bg-gray-50 rounded-lg shadow-lg"
>
  <h2 class="text-2xl font-bold mb-6">Media Library</h2>

  {{-- CATEGORY FILTER --}}
  <div class="flex flex-wrap gap-2 mb-4">
    <button
      @click="filterCategory = null"
      :class="filterCategory===null
               ? 'bg-purple-600 text-white'
               : 'bg-white text-gray-700'"
      class="px-3 py-1 rounded-lg shadow-sm"
    >All</button>

    <template x-for="(cat, idx) in categories" :key="idx">
      <button
        @click="filterCategory = cat.id"
        :class="filterCategory===cat.id
                 ? 'bg-purple-600 text-white'
                 : 'bg-white text-gray-700'"
        class="px-3 py-1 rounded-lg shadow-sm"
        x-text="cat.name"
      ></button>
    </template>
  </div>

  {{-- VIEW MODE + ACTIONS --}}
  <div class="flex items-center justify-between mb-6">
    <div class="flex space-x-4">
      <button
        @click="viewMode='grid'"
        :class="viewMode==='grid' ? 'text-purple-600 font-semibold' : 'text-gray-600'"
      >Grid</button>
      <button
        @click="viewMode='list'"
        :class="viewMode==='list' ? 'text-purple-600 font-semibold' : 'text-gray-600'"
      >List</button>
      <button
        @click="viewMode='thumb'"
        :class="viewMode==='thumb' ? 'text-purple-600 font-semibold' : 'text-gray-600'"
      >Thumb</button>
    </div>

    <div class="flex gap-2">
      <button
        @click="openUpload()"
        class="inline-flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg"
      >+ Upload</button>

      <button
        @click="bulkDelete()"
        :disabled="selected.length===0"
        class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg disabled:opacity-50"
      >🗑 Delete Selected (<span x-text="selected.length"></span>)</button>
    </div>
  </div>

  {{-- GALLERY --}}
  <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
    <template x-for="item in filtered" :key="item.id">
      <div class="relative" x-show="viewMode==='grid' || viewMode==='thumb'">
        <!-- Bulk checkbox -->
        <input type="checkbox" x-model="selected" :value="item.id"
               class="absolute top-2 left-2 z-10 rounded border-gray-300 bg-white"/>

        <img :src="item.url"
             :class="viewMode==='thumb' 
                      ? 'w-20 h-20 object-cover rounded-lg mx-auto'
                      : 'w-full h-40 object-cover rounded-lg shadow'"/>

        <div
          x-show="viewMode==='grid'"
          class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 bg-black bg-opacity-25 transition"
        >
          <button @click="showModal(item)"
                  class="bg-white p-2 rounded-full shadow mr-2">👁</button>
          <button @click="deleteMedia(item.id)"
                  class="bg-red-500 p-2 rounded-full shadow text-white">✕</button>
        </div>

        <div
          x-show="viewMode==='grid'"
          class="mt-2 text-xs truncate text-center"
          x-text="item.filename"
        ></div>
      </div>

      {{-- LIST ROW --}}
      <div
        x-show="viewMode==='list'"
        class="flex items-center justify-between bg-white rounded-lg shadow p-4"
      >
        <div class="flex items-center gap-4">
          <input type="checkbox" x-model="selected" :value="item.id"
                 class="rounded border-gray-300"/>
          <img :src="item.url" class="w-12 h-12 object-cover rounded"/>
          <span x-text="item.filename" class="font-medium"></span>
        </div>
        <button @click="deleteMedia(item.id)"
                class="text-red-500 hover:text-red-700">Delete</button>
      </div>
    </template>
  </div>

  {{-- PREVIEW MODAL --}}
  <div
    x-show="modalOpen"
    x-cloak
    class="fixed inset-0 bg-black/60 flex items-center justify-center z-50"
  >
    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden max-w-2xl w-full relative">
      <button @click="modalOpen=false"
              class="absolute top-4 right-4 text-3xl bg-white rounded-full p-1">×</button>
      <img :src="modalImage.url" class="w-full h-auto object-contain"/>
      <div class="p-4 text-center text-lg font-medium" x-text="modalImage.filename"></div>
    </div>
  </div>

  {{-- UPLOAD MODAL --}}
  <div
    x-show="uploadModalOpen"
    x-cloak
    x-transition
    class="fixed inset-0 backdrop-blur-md bg-black/40 flex items-center justify-center z-50"
  >
    <div @click.away="closeUpload()"
         class="bg-white rounded-2xl shadow-2xl w-full max-w-xl p-8 space-y-6">
      <h3 class="text-2xl font-semibold">Upload Media</h3>

      {{-- Category selector --}}
      <div class="flex items-center gap-4">
        <label class="block text-sm font-medium">Category</label>
        <select x-model="selectedCategory"
                class="border-gray-300 rounded-lg px-3 py-2 flex-1">
          <option value="">Uncategorized</option>
          <template x-for="(cat, idx) in categories" :key="idx">
            <option :value="cat.id" x-text="cat.name"></option>
          </template>
        </select>
        <button @click="showAddCategory = !showAddCategory"
                class="text-purple-600 hover:underline text-sm">
          + New
        </button>
      </div>

      {{-- New category form --}}
      <template x-if="showAddCategory">
        <div class="flex gap-4">
          <input x-model="newCategoryName" type="text"
                 placeholder="Category name"
                 class="flex-1 border-gray-300 rounded-lg px-3 py-2"/>
          <button @click="addCategory()"
                  class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg">
            Create
          </button>
        </div>
      </template>

      {{-- File picker + previews --}}
      <div>
        <input x-ref="fileInput" type="file" multiple @change="addFiles" class="hidden"/>
        <button @click="$refs.fileInput.click()"
                class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg">
          Select Files
        </button>
      </div>

      <div class="flex overflow-x-auto space-x-4 py-2">
        <template x-for="(f, idx) in files" :key="idx">
          <div class="relative w-28 h-28 bg-gray-100 rounded-lg overflow-hidden">
            <img :src="f.preview" class="w-full h-full object-cover"/>
            <button @click="remove(idx)"
                    class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1">×</button>
            <div class="absolute bottom-0 left-0 w-full h-1 bg-gray-200">
              <div class="h-full bg-purple-600 transition-all"
                   :style="`width:${f.progress}%`"></div>
            </div>
          </div>
        </template>
      </div>

      {{-- Actions --}}
      <div class="flex justify-end gap-4">
        <button @click="closeUpload()"
                class="px-4 py-2 bg-gray-200 rounded-lg">Cancel</button>
        <button @click="uploadAll()"
                :disabled="files.length===0"
                class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg disabled:opacity-50">
          Upload
        </button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function mediaLibrary(initialMedia, initialCategories) {
  return {
    // state
    files: [],
    library: initialMedia,
    categories: initialCategories,
    filterCategory: null,
    viewMode: 'grid',
    modalOpen: false,
    modalImage: {},
    uploadModalOpen: false,
    selectedCategory: null,
    showAddCategory: false,
    newCategoryName: '',
    selected: [],

    // computed
    get filtered() {
      let arr = this.library.filter(item =>
        !this.filterCategory
        || item.categories.includes(this.filterCategory)
      );
      return arr;
    },

    // methods
    openUpload() {
      this.files = [];
      this.uploadModalOpen = true;
    },
    closeUpload() {
      this.uploadModalOpen = false;
      this.showAddCategory = false;
      this.newCategoryName = '';
    },
    showModal(item) {
      this.modalImage = item;
      this.modalOpen = true;
    },
    bulkDelete() {
  

  fetch('{{ route("admin.media.bulkDelete") }}', {
    method: 'DELETE',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': '{{ csrf_token() }}'
    },
    body: JSON.stringify({ ids: this.selected })
  })
  .then(res => {
    if (!res.ok) {
      return res.text().then(text => { throw new Error(`Server returned ${res.status}: ${text}`); });
    }
    return res.json();
  })
  .then(json => {
    if (json.deleted) {
      // remove from UI
      this.library = this.library.filter(m => !this.selected.includes(m.id));
      this.selected = [];
      window.location.reload();
    } else {
      alert('No items were deleted.');
    }
  })
  .catch(err => {
    console.error('Bulk delete error:', err);
    alert('Could not delete selected items: ' + err.message);
  });
},

deleteMedia(id) {
  if (!confirm('Delete this media?')) return;

  const url = '{{ route("admin.media.destroy", ["media"=>":id"]) }}'.replace(':id', id);
  fetch(url, {
    method: 'DELETE',
    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
  })
  .then(res => {
    if (!res.ok) throw new Error(`Server returned ${res.status}`);
    return res.json();
  })
  .then(json => {
    if (json.deleted) {
      this.library = this.library.filter(m => m.id !== id);
      window.location.reload();
    } else {
      alert('Could not delete item.');
    }
  })
  .catch(err => {
    console.error('Delete error:', err);
    alert('Error deleting item: ' + err.message);
  });
},

    addFiles(e) {
      this.files = [];
      for (let f of e.target.files) {
        if (!f.type.startsWith('image/')) continue;
        let reader = new FileReader();
        reader.onload = evt => this.files.push({ file: f, preview: evt.target.result, progress: 0 });
        reader.readAsDataURL(f);
      }
      e.target.value = null;
    },
    remove(idx) {
      this.files.splice(idx,1);
    },
    addCategory() {
      if (!this.newCategoryName.trim()) return;
      fetch('{{ route("admin.media.categories.store") }}', {
        method:'POST',
        headers:{ 'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}' },
        body: JSON.stringify({ name:this.newCategoryName, parent:0 })
      })
      .then(r=>r.ok?r.json():Promise.reject(r.statusText))
      .then(cat=>{
        this.categories.push(cat);
        this.selectedCategory=cat.id;
        this.showAddCategory=false;
        this.newCategoryName='';
      })
      .catch(err=>alert('Error: '+err));
    },
    uploadAll() {
      let total = this.files.length;
      this.files.forEach((f,i)=>{
        let form=new FormData();
        form.append('files[]', f.file);
        form.append('category_id',this.selectedCategory||'');
        let xhr=new XMLHttpRequest();
        xhr.open('POST','{{ route("admin.media.store") }}');
        xhr.setRequestHeader('X-CSRF-TOKEN','{{ csrf_token() }}');
        xhr.upload.onprogress=e=>f.progress=Math.round(e.loaded/e.total*100);
        xhr.onload=()=>{
          if(xhr.status===201) JSON.parse(xhr.responseText).uploaded.forEach(u=>this.library.unshift(u));
          if(i===total-1) this.closeUpload();
        };
        xhr.send(form);
      });
    }
  }
}
</script>
@endpush
