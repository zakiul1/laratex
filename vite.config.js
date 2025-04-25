import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
    ],
    server: {
        host: "127.0.0.1", // bind to IPv4 localhost
        port: 5174, // your chosen port
        strictPort: true, // fail if port is in use
        cors: {
            origin: "http://127.0.0.1:8000", // allow your Laravel app
            methods: ["GET", "POST", "PUT", "PATCH", "DELETE", "OPTIONS"],
            allowedHeaders: ["*"],
            credentials: true,
        },
        hmr: {
            host: "127.0.0.1", // force HMR socket to use IPv4 host
            protocol: "ws",
            port: 5174,
        },
    },
});
