import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                mono: ['"JetBrains Mono"', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                brand: {
                    DEFAULT: '#D81F2A',
                    dark: '#AF1922',
                    tint: '#FBE9EA',
                    line: '#F4C9CC',
                },
                ink: {
                    DEFAULT: '#0E1116',
                    2: '#1B1F27',
                },
                accent: '#234E9E',
                canvas: '#EBECEF',
                // Semantic neutral ramp — replaces the five different
                // near-blacks and ad hoc greys used across the old templates.
                gray: {
                    50: '#F6F7F9',
                    100: '#EEF0F3',
                    200: '#E4E6EB',
                    300: '#CBD0D8',
                    400: '#9CA3AF',
                    500: '#6B7280',
                    700: '#374151',
                    900: '#161A20',
                    950: '#0E1116',
                },
            },
            borderRadius: {
                sm: '6px',
                DEFAULT: '10px',
                md: '10px',
                lg: '16px',
            },
        },
    },

    plugins: [forms],
};
