// resources/js/mediaBrowser.js

export default function registerMediaBrowser() {
    document.addEventListener("alpine:init", () => {
        Alpine.data("mediaBrowser", () => ({
            // === UI state ===
            isOpen: false,
            tab: "library",
            isLoading: false,
            loadError: "",
            images: [],
            selectedIds: [],
            currentPage: 1,
            lastPage: 1,

            // === filters & sort ===
            categories: [], // will be filled by loadLibrary
            filterCategory: "", // bound to the Category dropdown
            sortOrder: "newest", // bound to the Sort dropdown

            // callback for inserting images into your editor
            callback: null,

            // === Alpine lifecycle hook ===
            init() {
                document.addEventListener("media-open", (e) => {
                    this.callback = e.detail.onSelect;
                    this.loadLibrary();
                    this.isOpen = true;
                });
            },

            // === fetch media + categories ===
            async loadLibrary(page = 1) {
                this.isLoading = true;
                this.loadError = "";
                try {
                    const params = new URLSearchParams({
                        page,
                        per_page: 20,
                        category: this.filterCategory || "",
                        sort: this.sortOrder,
                    });
                    const res = await fetch(`/admin/media?${params}`, {
                        headers: { Accept: "application/json" },
                    });
                    if (!res.ok) {
                        const err = await res.json();
                        throw new Error(err.error || "Failed to load media");
                    }
                    const json = await res.json();
                    // populate your lists
                    this.images = json.data;
                    this.currentPage = json.meta.current_page;
                    this.lastPage = json.meta.last_page;
                    this.categories = json.categories; // ← **NEW**: fill the dropdown
                } catch (err) {
                    this.loadError = err.message;
                } finally {
                    this.isLoading = false;
                }
            },

            // === selection logic ===
            toggleSelect(img) {
                this.selectedIds = this.selectedIds.includes(img.id)
                    ? this.selectedIds.filter((i) => i !== img.id)
                    : [...this.selectedIds, img.id];
            },
            insertSelected() {
                this.images
                    .filter((i) => this.selectedIds.includes(i.id))
                    .forEach((i) => this.callback(i));
                this.close();
            },

            // === close & reset everything ===
            close() {
                this.isOpen = false;
                this.tab = "library";
                this.filterCategory = "";
                this.sortOrder = "newest";
                this.images = [];
                this.selectedIds = [];
                this.currentPage = 1;
                this.lastPage = 1;
                this.callback = null;
            },
        }));
    });
}
