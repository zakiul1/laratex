// resources/js/dynamicGridCartStore.js
import Alpine from "alpinejs";

document.addEventListener("alpine:init", () => {
    Alpine.store("dynamicCart", {
        items: [],
        showCart: false,
        showForm: false,
        name: "",
        whatsapp: "",
        email: "",
        message: "",
        errors: [],

        init() {
            // 1) Load existing cart from localStorage
            try {
                const saved = localStorage.getItem("dynamicCartItems");
                if (saved) {
                    this.items = JSON.parse(saved);
                }
            } catch (e) {
                console.error("Failed to parse saved cart", e);
            }
            // 2) Bind all .get-price-btn clicks _after_ DOM is ready
            this.bindButtons();
        },

        save() {
            localStorage.setItem(
                "dynamicCartItems",
                JSON.stringify(this.items)
            );
        },

        bindButtons() {
            // Remove any previous listeners and re‐bind
            document.querySelectorAll(".get-price-btn").forEach((btn) => {
                btn.replaceWith(btn.cloneNode(true));
            });
            document.querySelectorAll(".get-price-btn").forEach((btn) => {
                btn.addEventListener("click", () => {
                    const id = btn.dataset.id;
                    const title = btn.dataset.title;
                    const img = btn.dataset.image;
                    const url = btn.dataset.url;

                    if (!this.items.some((x) => x.id === id)) {
                        this.items.push({ id, title, img, url });
                        this.save();
                        window.ntfy(`"${title}" added to cart`);
                    }
                });
            });
        },

        remove(id) {
            this.items = this.items.filter((x) => x.id !== id);
            this.save();
        },

        goToForm() {
            this.errors = [];
            this.showCart = false;
            this.showForm = true;
        },

        async submit() {
            this.errors = [];
            const payload = {
                name: this.name,
                whatsapp: this.whatsapp,
                email: this.email,
                message: this.message,
                products: this.items,
            };

            try {
                let res = await fetch("/dynamicgrid/request-price", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]'
                        ).content,
                    },
                    credentials: "same-origin",
                    body: JSON.stringify(payload),
                });

                if (res.status === 422) {
                    let json = await res.json();
                    this.errors = Object.values(json.errors).flat();
                    return;
                }

                let json = await res.json();
                window.ntfy(json.message || "Request sent!");
                this.showForm = false;
                this.items = [];
                this.name = "";
                this.whatsapp = "";
                this.email = "";
                this.message = "";
                localStorage.removeItem("dynamicCartItems");
            } catch (e) {
                console.error(e);
                window.ntfy("Error sending request", "error");
            }
        },
    });

    // ← No direct call to Alpine.store("dynamicCart").init() here!
});
