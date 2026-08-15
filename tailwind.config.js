import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/views/**/*.blade.php',
        './app/**/*.php',
    ],
    darkMode: 'class',
    corePlugins: {
        preflight: false,
    },
    theme: {
        extend: {},
    },
    plugins: [
        forms({ strategy: 'class' }),
    ],
};
