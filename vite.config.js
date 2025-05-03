// vite.config.js
import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import path from "path";

export default defineConfig({
    server: {
        fs: {
            allow: [
                path.resolve(__dirname), // your project root
                path.resolve(__dirname, "storage/app/public"), // storage folder
            ],
        },
    },
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
    ],
});
