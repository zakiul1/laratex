import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

export default {
    // Disable all dark-mode variants
    darkMode: false,

    // Anything here is always generated, even if not found in your content files
    safelist: [
        "bg-yellow-600",
        "hover:bg-yellow-700",
        "h-[75vh]",
        "md:h-[85vh]",
        "lg:h-[90vh]",
    ],

    content: [
        // Laravel’s default pagination views
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",

        // Your app’s Blade templates
        "./resources/views/**/*.blade.php",

        // Any compiled Blade (during dev)
        "./storage/framework/views/*.php",

        // Your JS components
        "./resources/js/**/*.vue",
        "./resources/js/**/*.jsx",
        "./resources/js/**/*.tsx",

        // **SliderPlugin front‐end views** 👇
        "./plugins/SliderPlugin/resources/views/**/*.blade.php",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
