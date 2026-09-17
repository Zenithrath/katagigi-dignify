import defaultTheme from 'tailwindcss/defaultTheme';
import colors from 'tailwindcss/colors';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            screens: {
                print: { raw: 'print' },
                screen: { raw: 'screen' },
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            // Palet warisan app lama (dipakai halaman-halaman port)
            colors: {
                brand: colors.blue,
                danger: colors.red,
                warning: colors.yellow,
                success: colors.green,
                item: colors.gray,
                ground: colors.slate,
            },
        },
    },

    plugins: [forms],
};
