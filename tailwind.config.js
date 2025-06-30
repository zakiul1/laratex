import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

export default {
    // Disable all dark-mode variants
    darkMode: false,

    safelist: [
        "bg-yellow-600",
        "hover:bg-yellow-700",
        // add any other dynamic classes you build at runtime
    ],

    content: [
        // Laravel’s default pagination views (still needed if you use paginate())
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",

        // Blade templates you control
        "./resources/views/**/*.blade.php",

        // Any compiled Blade (only needed if you reference Blade in storage during dev)
        "./storage/framework/views/*.php",

        // Alpine/Vue/React components where you might use Tailwind classes
        "./resources/js/**/*.vue",
        "./resources/js/**/*.jsx",
        "./resources/js/**/*.tsx",
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
