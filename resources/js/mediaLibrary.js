/* // resources/js/mediaLibrary.js
import Alpine from "alpinejs";

document.addEventListener("alpine:init", () => {
    Alpine.data("mediaLibrary", () => ({
        // State
        media: window.initialMedia || [],
        categories: window.initialCategories || [],
        selected: [],
        view: "grid",
        perPage: 12,
        category: null,
        search: "",
        currentPage: window.initialMeta.current_page || 1,
        lastPage: window.initialMeta.last_page || 1,
        loadError: "",
        isLoading: false,
        modalOpen: false,
        modalImage: {},

        // Upload-modal state
        uploadModalOpen: false,
        uploadErrors: [],
        uploadCategory: null,
        selectedFiles: [],
        newCategoryName: "",
        creatingCategory: false,
        categoryError: "",
        uploading: false,
        uploadProgress: [],

        init() {
            console.log("MediaLibrary initialized");
            this.loadMedia(this.currentPage);
        },

        openUpload() {
            this.uploadErrors = [];
            this.uploadCategory = null;
            this.selectedFiles = [];
            this.newCategoryName = "";
            this.categoryError = "";
            this.uploadProgress = [];
            this.uploadModalOpen = true;
        },

        closeUpload() {
            this.uploadModalOpen = false;
            this.selectedFiles = [];
            this.uploadProgress = [];
        },

        handleFileChange(event) {
            const files = Array.from(event.target.files);
            this.selectedFiles = files.map((file) => ({
                file,
                url: URL.createObjectURL(file),
                selected: true,
            }));
            this.uploadProgress = files.map(() => 0);
        },

        toggleFileSelection(index) {
            this.selectedFiles[index].selected =
                !this.selectedFiles[index].selected;
        },

        removeFile(index) {
            URL.revokeObjectURL(this.selectedFiles[index].url);
            this.selectedFiles.splice(index, 1);
            this.uploadProgress.splice(index, 1);
        },

        async createCategory() {
            if (!this.newCategoryName.trim()) {
                this.categoryError = "Category name is required.";
                return;
            }
            this.creatingCategory = true;
            this.categoryError = "";

            try {
                const res = await fetch(window.mediaRoutes.categoriesStore, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]'
                        ).content,
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({
                        name: this.newCategoryName,
                        parent: this.uploadCategory || 0,
                    }),
                });

                if (!res.ok) {
                    const json = await res.json();
                    this.categoryError =
                        json.error || "Failed to create category.";
                } else {
                    const newCat = await res.json();
                    this.categories.push(newCat);
                    this.uploadCategory = newCat.id;
                    this.newCategoryName = "";
                }
            } catch {
                this.categoryError = "Network error while creating category.";
            } finally {
                this.creatingCategory = false;
            }
        },

        async upload() {
            this.uploadErrors = [];
            this.uploading = true;

            const filesToUpload = this.selectedFiles
                .filter((f) => f.selected)
                .map((f) => f.file);
            if (!filesToUpload.length) {
                this.uploadErrors.push("Please select at least one file.");
                this.uploading = false;
                return;
            }
            if (!this.uploadCategory) {
                this.uploadErrors.push("Please select or create a category.");
                this.uploading = false;
                return;
            }

            const form = new FormData();
            filesToUpload.forEach((f) => form.append("files[]", f));
            form.append("category_id", this.uploadCategory);

            try {
                const xhr = new XMLHttpRequest();
                xhr.open("POST", window.mediaRoutes.store, true);
                xhr.setRequestHeader(
                    "X-CSRF-TOKEN",
                    document.querySelector('meta[name="csrf-token"]').content
                );

                xhr.upload.onprogress = (event) => {
                    if (event.lengthComputable) {
                        const totalProgress =
                            (event.loaded / event.total) * 100;
                        this.uploadProgress = this.uploadProgress.map(() =>
                            Math.min(totalProgress, 100)
                        );
                    }
                };

                xhr.onload = () => {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        this.loadMedia(this.currentPage);
                        this.closeUpload();
                    } else if (xhr.status === 422) {
                        const json = JSON.parse(xhr.responseText);
                        Object.values(json.errors || {}).forEach((arr) =>
                            arr.forEach((msg) => this.uploadErrors.push(msg))
                        );
                    } else {
                        this.uploadErrors.push(
                            "Upload failed. Please try again."
                        );
                    }
                    this.uploading = false;
                };

                xhr.onerror = () => {
                    this.uploadErrors.push("Network error during upload.");
                    this.uploading = false;
                };

                xhr.send(form);
            } catch {
                this.uploadErrors.push("Network error during upload.");
                this.uploading = false;
            }
        },

        async loadMedia(page = 1) {
            this.isLoading = true;
            this.loadError = "";

            try {
                const params = new URLSearchParams({
                    page,
                    per_page: this.perPage,
                    category: this.category || "",
                    search: this.search || "",
                });
                const res = await fetch(
                    `${window.mediaRoutes.index}?${params}`,
                    {
                        headers: { Accept: "application/json" },
                    }
                );
                const json = await res.json();

                this.media = json.data;
                this.currentPage = json.meta.current_page;
                this.lastPage = json.meta.last_page;
                this.categories = json.categories;
            } catch {
                this.loadError = "Failed to load media.";
            } finally {
                this.isLoading = false;
            }
        },

        showModal(item) {
            this.modalImage = item;
            this.modalOpen = true;
        },

        async deleteMedia(id) {
            if (!confirm("Delete this media?")) return;

            await fetch(window.mediaRoutes.destroy.replace("__ID__", id), {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]'
                    ).content,
                },
            });

            this.loadMedia(this.currentPage);
        },

        async bulkDelete() {
            if (!confirm(`Delete ${this.selected.length} items?`)) return;

            await fetch(window.mediaRoutes.bulkDelete, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]'
                    ).content,
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ ids: this.selected }),
            });

            this.selected = [];
            this.loadMedia(this.currentPage);
        },

        getCategoryNames(item) {
            const cats = item.categories || [];
            if (!Array.isArray(cats)) return "—";
            return cats
                .map((id) => {
                    const c = this.categories.find((cat) => cat.id === id);
                    return c ? c.name : "—";
                })
                .join(", ");
        },
    }));
});
 */
