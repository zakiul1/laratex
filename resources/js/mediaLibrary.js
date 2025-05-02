// resources/js/mediaLibrary.js

import Alpine from "alpinejs";

// seeded in your Blade via <script> … window.initialMedia, window.initialMeta, window.initialCategories, window.mediaRoutes …
window.initialMedia = window.initialMedia || [];
window.initialMeta = window.initialMeta || { current_page: 1, last_page: 1 };
window.initialCategories = window.initialCategories || [];
window.mediaRoutes = window.mediaRoutes || {};

// route fallbacks
const indexUrl = window.mediaRoutes.index || "/admin/media";
const storeUrl = window.mediaRoutes.store || "/admin/media/upload";
const destroyBaseUrl = window.mediaRoutes.destroy || "/admin/media/"; // note trailing slash
const bulkDeleteUrl =
    window.mediaRoutes.bulkDelete || "/admin/media/bulk-delete";
const categoriesUrl =
    window.mediaRoutes.categoriesStore || "/admin/media/categories";

Alpine.data("mediaLibrary", () => ({
    media: window.initialMedia,
    categories: window.initialCategories,
    view: "grid",
    selected: [],
    search: "",
    category: "",
    perPage: 12,
    currentPage: window.initialMeta.current_page,
    lastPage: window.initialMeta.last_page,
    modalOpen: false,
    modalImage: {},
    uploadModalOpen: false,
    files: [],
    selectedCategory: "",
    showAddCategory: false,
    newCategoryName: "",
    isLoading: false,
    isUploading: false,
    uploadError: "",
    loadError: "",

    init() {
        const params = new URLSearchParams(window.location.search);
        this.search = params.get("search") || "";
        this.category = params.get("category") || "";
        this.perPage = params.get("per_page") || this.perPage;
        this.$watch("search", () => this.loadMedia());
        this.$watch("category", () => this.loadMedia());
        this.$watch("perPage", () => this.loadMedia());
    },

    getCategoryNames(item) {
        if (!item.categories?.length) return "Uncategorized";
        return this.categories
            .filter((c) => item.categories.includes(c.id))
            .map((c) => c.name || "Unnamed")
            .join(", ");
    },
    showModal(item) {
        this.modalImage = item;
        this.modalOpen = true;
    },
    async loadMedia(page = 1) {
        this.isLoading = true;
        this.loadError = "";
        try {
            const params = new URLSearchParams({
                page,
                per_page: this.perPage,
                ...(this.search && { search: this.search }),
                ...(this.category && { category: this.category }),
            });
            const res = await fetch(`${indexUrl}?${params}`, {
                headers: {
                    Accept: "application/json",
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]'
                    ).content,
                    "X-Requested-With": "XMLHttpRequest",
                },
            });
            if (!res.ok) {
                const err = await res.json();
                throw new Error(err.error || "Failed to load media");
            }
            const { data, meta, categories } = await res.json();
            this.media = data;
            this.currentPage = meta.current_page;
            this.lastPage = meta.last_page;
            this.categories = categories;
            history.replaceState(null, "", `?${params}`);
        } catch (err) {
            this.loadError = err.message;
        } finally {
            this.isLoading = false;
        }
    },

    async deleteMedia(id) {
        if (!confirm("Are you sure you want to delete this media?")) return;
        try {
            const res = await fetch(`${destroyBaseUrl}${id}`, {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]'
                    ).content,
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
            });
            if (!res.ok) throw new Error("Failed to delete media");
            await this.loadMedia(this.currentPage);
        } catch (err) {
            this.loadError = err.message;
        }
    },

    async deleteMedia(id) {
        if (!confirm("Delete this item?")) return;

        try {
            // build the URL with the actual ID
            const url = `/admin/media/${id}`;

            const res = await fetch(url, {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]'
                    ).content,
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
            });

            if (!res.ok) throw new Error("Failed to delete media");

            // reload the current page of media
            await this.loadMedia(this.currentPage);
        } catch (err) {
            this.loadError = err.message;
        }
    },

    openUpload() {
        this.uploadModalOpen = true;
        this.files = [];
        this.uploadError = "";
    },
    closeUpload() {
        this.uploadModalOpen = false;
        this.files = [];
        this.isUploading = false;
        this.uploadError = "";
    },

    addFiles(e) {
        this.uploadError = "";
        const list = e.dataTransfer?.files || e.target.files;
        Array.from(list).forEach((file) => {
            if (!file.type.startsWith("image/")) {
                this.uploadError = "Only images allowed";
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                this.uploadError = "Max 5MB per file";
                return;
            }
            const reader = new FileReader();
            reader.onload = (ev) =>
                this.files.push({
                    file,
                    preview: ev.target.result,
                    progress: 0,
                });
            reader.readAsDataURL(file);
        });
        if (e.target) e.target.value = "";
    },

    removeFile(i) {
        this.files.splice(i, 1);
    },

    async addCategory() {
        if (!this.newCategoryName.trim()) {
            this.uploadError = "Category name is required";
            return;
        }
        try {
            const res = await fetch(categoriesUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]'
                    ).content,
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: JSON.stringify({ name: this.newCategoryName, parent: 0 }),
            });
            const json = await res.json();
            if (!res.ok || json.error)
                throw new Error(json.error || "Failed to create category");
            this.categories.push({ id: json.id, name: json.name });
            this.selectedCategory = String(json.id);
            this.newCategoryName = "";
            this.showAddCategory = false;
        } catch (err) {
            this.uploadError = err.message;
        }
    },

    async uploadFiles() {
        if (!this.files.length) return;

        this.isUploading = true;
        this.uploadError = "";

        // we’ll upload *one file at a time* so each has its own progress bar
        for (let i = 0; i < this.files.length; i++) {
            const entry = this.files[i];
            entry.progress = 0;
            entry.status = "uploading";
            entry.errorMessages = [];

            await new Promise((resolve) => {
                const form = new FormData();
                form.append("files[]", entry.file);
                if (this.selectedCategory) {
                    form.append("category_id", this.selectedCategory);
                }

                const xhr = new XMLHttpRequest();
                xhr.open("POST", storeUrl, true);
                xhr.setRequestHeader(
                    "X-CSRF-TOKEN",
                    document.querySelector('meta[name="csrf-token"]').content
                );
                xhr.setRequestHeader("Accept", "application/json");

                // progress event
                xhr.upload.onprogress = (e) => {
                    if (e.lengthComputable) {
                        entry.progress = Math.round((e.loaded / e.total) * 100);
                    }
                };

                xhr.onload = () => {
                    if (xhr.status === 201) {
                        // success: parse JSON, add to grid
                        const json = JSON.parse(xhr.responseText);
                        if (json.uploaded && json.uploaded[0]) {
                            this.media.unshift(json.uploaded[0]);
                            entry.status = "done";
                            entry.progress = 100;
                        }
                    } else {
                        // non-201 => error page or JSON error
                        let errMsg = "Upload failed";
                        try {
                            const err = JSON.parse(xhr.responseText);
                            errMsg = err.error || errMsg;
                        } catch (_) {
                            errMsg = "Server error uploading file";
                        }
                        entry.status = "error";
                        entry.errorMessages = [errMsg];
                        this.uploadError = errMsg;
                    }
                    resolve();
                };

                xhr.onerror = () => {
                    entry.status = "error";
                    entry.errorMessages = ["Network error"];
                    this.uploadError = "Network error";
                    resolve();
                };

                xhr.send(form);
            });
        }

        this.isUploading = false;
        // clear the queue
        this.files = [];
    },

    /**
     * **Re-add** this so @click="bulkDelete()" works
     */
    async bulkDelete() {
        if (!this.selected.length) return;

        if (!confirm(`Delete ${this.selected.length} items?`)) {
            return;
        }

        this.isLoading = true;
        this.loadError = "";

        try {
            const res = await fetch("/admin/media/bulk-delete", {
                method: "DELETE",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]'
                    ).content,
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: JSON.stringify({ ids: this.selected }),
            });

            if (!res.ok) {
                const err = await res.json();
                throw new Error(err.error || "Bulk delete failed");
            }

            // clear selection & refresh
            this.selected = [];
            await this.loadMedia(this.currentPage);
        } catch (err) {
            this.loadError = err.message;
        } finally {
            this.isLoading = false;
        }
    },
}));
