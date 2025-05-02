// resources/js/mediaBrowser.js
export default function registerMediaBrowser() {
    document.addEventListener("alpine:init", () => {
        Alpine.data("mediaBrowser", () => ({
            // === state ===
            isOpen: false,
            tab: "library",
            isLoading: false,
            loadError: "",
            showSuccess: false,
            uploadError: "",
            images: [],
            uploads: [],
            selectedIds: [],
            filterType: "all",
            filterDate: "all",
            searchTerm: "",
            currentPage: 1,
            lastPage: 1,
            callback: null,

            // === Alpine lifecycle hook ===
            init() {
                document.addEventListener("media-open", (e) => {
                    this.callback = e.detail.onSelect;
                    this.loadLibrary();
                    this.isOpen = true;
                });
            },

            // === load library via JSON ===
            async loadLibrary(page = 1) {
                this.isLoading = true;
                this.loadError = "";
                try {
                    const params = new URLSearchParams({
                        page,
                        per_page: 20,
                        ...(this.filterType !== "all" && {
                            type: this.filterType,
                        }),
                        ...(this.filterDate !== "all" && {
                            date: this.filterDate,
                        }),
                        ...(this.searchTerm && { search: this.searchTerm }),
                    });
                    const res = await fetch(`/admin/media?${params}`, {
                        headers: {
                            Accept: "application/json",
                            "X-CSRF-TOKEN": document.querySelector(
                                'meta[name="csrf-token"]'
                            ).content,
                        },
                    });
                    if (!res.ok)
                        throw new Error(
                            (await res.json()).error || "Failed to load media"
                        );
                    const { data, meta } = await res.json();
                    this.images = data;
                    this.currentPage = meta.current_page;
                    this.lastPage = meta.last_page;
                } catch (err) {
                    this.loadError = err.message;
                } finally {
                    this.isLoading = false;
                }
            },

            // === helpers ===
            get dateOptions() {
                const months = [
                    ...new Set(
                        this.images
                            .map((i) => i.created_at?.slice(0, 7))
                            .filter(Boolean)
                    ),
                ];
                return months.sort().map((m) => ({
                    value: m,
                    label: new Date(m + "-01").toLocaleString("default", {
                        month: "long",
                        year: "numeric",
                    }),
                }));
            },

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

            // === uploading ===
            handleDrop(e) {
                this.uploadFiles(e.dataTransfer.files);
            },
            pickFiles(e) {
                this.uploadFiles(e.target.files);
                e.target.value = "";
            },

            uploadFiles(files) {
                Array.from(files).forEach((file) => {
                    if (!file.type.match(/^(image|video)\//)) {
                        this.uploadError = "Only images/videos";
                        return;
                    }
                    if (file.size > 5 * 1024 * 1024) {
                        this.uploadError = "Max 5MB";
                        return;
                    }
                    const entry = {
                        file,
                        name: file.name,
                        progress: 0,
                        status: "queued",
                        previewUrl: URL.createObjectURL(file),
                        lengthComputable: false,
                        errorMessages: [],
                    };
                    this.uploads.push(entry);
                    this.uploadFile(entry);
                });
            },

            uploadFile(entry) {
                entry.status = "uploading";
                const xhr = new XMLHttpRequest();
                xhr.open("POST", "/admin/media/upload", true);
                xhr.setRequestHeader(
                    "X-CSRF-TOKEN",
                    document.querySelector('meta[name="csrf-token"]').content
                );
                xhr.upload.onprogress = (e) => {
                    if (e.lengthComputable) {
                        entry.lengthComputable = true;
                        entry.progress = Math.round((e.loaded / e.total) * 100);
                    }
                };
                xhr.onload = () => {
                    if (xhr.status === 201) {
                        const uploaded = JSON.parse(xhr.responseText).uploaded;
                        uploaded.forEach((item) => {
                            this.images.unshift(item);
                            this.selectedIds.push(item.id);
                        });
                        entry.status = "done";
                        entry.progress = 100;
                    } else {
                        let err;
                        try {
                            err = JSON.parse(xhr.responseText);
                        } catch {
                            err = { error: "Unknown error" };
                        }
                        entry.errorMessages = err.error
                            ? [err.error]
                            : ["Upload failed"];
                        entry.status = "error";
                    }
                    this.checkAllDone();
                };
                xhr.onerror = () => {
                    entry.status = "error";
                    entry.errorMessages = ["Network error"];
                    this.checkAllDone();
                };
                const form = new FormData();
                form.append("files[]", entry.file);
                xhr.send(form);
            },

            removeFile(idx) {
                this.uploads.splice(idx, 1);
            },

            checkAllDone() {
                if (this.uploads.every((u) => u.status !== "uploading")) {
                    this.showSuccess = true;
                    setTimeout(() => {
                        this.showSuccess = false;
                        this.tab = "library";
                        this.uploads = [];
                        this.loadLibrary(this.currentPage);
                    }, 2000);
                }
            },

            close() {
                this.isOpen = false;
                this.tab = "library";
                this.isLoading = false;
                this.loadError = "";
                this.showSuccess = false;
                this.uploadError = "";
                this.images = [];
                this.uploads = [];
                this.selectedIds = [];
                this.filterType = "all";
                this.filterDate = "all";
                this.searchTerm = "";
                this.currentPage = 1;
                this.lastPage = 1;
                this.callback = null;
            },
        }));
    });
}
