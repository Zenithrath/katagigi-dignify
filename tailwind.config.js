import defaultTheme from 'tailwindcss/defaultTheme';
import colors from 'tailwindcss/colors';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
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
            // Palet selaras logo (#264B6F biru tua): primer = sky,
            // emerald/teal lawas dipetakan ke sky agar seluruh aksen seragam.
            colors: {
                brand: colors.sky,
                emerald: colors.sky,
                teal: colors.sky,
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
