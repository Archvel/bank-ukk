import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import colors from 'tailwindcss/colors';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    light: colors.emerald[50],
                    DEFAULT: colors.emerald[600],
                    dark: colors.emerald[700],
                },
                status: {
                    success: colors.green[500],
                    warning: colors.amber[500],
                    danger: colors.red[600],
                },
                admin: {
                    sidebar: colors.slate[900],
                }
            }
        },
    },

    plugins: [forms],
};