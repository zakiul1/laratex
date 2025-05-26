// resources/js/app.js
import "./bootstrap";
import Alpine from "alpinejs";
import collapse from "@alpinejs/collapse";
import focus from "@alpinejs/focus";
import Sortable from "sortablejs";
import Notification from "@aponahmed/notify";
import "./dynamicGridCartStore.js";

// ** Side-effect imports only **
import registerMediaBrowser from "./mediaBrowser";
import "./mediaLibrary";

window.Alpine = Alpine;
window.Sortable = Sortable;
window.ntfy = (message, type = "success", timeout = 3000) => {
    new Notification({ type, message, timeout });
};
registerMediaBrowser();

Alpine.plugin(collapse);
Alpine.plugin(focus);
Alpine.start();
