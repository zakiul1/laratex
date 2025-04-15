/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/views/**/*.blade.php',  // More specific
        './resources/js/**/*.js',
        './resources/js/**/*.vue',
    ],
    theme: {
        extend: {},
    },
    plugins: [
        require('@tailwindcss/forms'), // recommended for form styling
    ],
}
